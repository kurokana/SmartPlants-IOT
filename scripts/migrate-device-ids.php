<?php

/**
 * Migration Script: Convert Old Device IDs to New Format
 * 
 * Converts existing device IDs from raw chip IDs to user-scoped format:
 * OLD: "62563"
 * NEW: "user_1_chip_62563"
 * 
 * This script:
 * 1. Backs up existing devices
 * 2. Migrates device IDs to new format
 * 3. Updates all related records (sensors, commands, readings)
 * 4. Updates provisioning tokens
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Device;
use App\Models\Sensor;
use App\Models\Command;
use App\Models\SensorReading;
use App\Models\ProvisioningToken;
use Illuminate\Support\Facades\DB;

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║   DEVICE ID MIGRATION: RAW → USER-SCOPED FORMAT                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "⚠️  WARNING: This script will modify device IDs in the database.\n";
echo "   A backup will be created before migration.\n\n";

// Step 1: List all existing devices
echo "📋 Step 1: Checking existing devices...\n\n";

$devices = Device::with('user')->get();

if ($devices->count() === 0) {
    echo "✅ No devices found. Nothing to migrate.\n";
    exit(0);
}

echo "Found {$devices->count()} device(s):\n\n";

$migrations = [];

foreach ($devices as $device) {
    $oldId = $device->id;
    
    // Check if already in new format
    if (str_starts_with($oldId, 'user_')) {
        echo "   ⏭️  Device {$oldId} - Already in new format, skipping\n\n";
        continue;
    }
    
    // Check if device has owner
    if (!$device->user_id) {
        echo "   ⚠️  Device {$oldId} - No owner (orphaned), skipping\n";
        echo "      This device needs to be provisioned with a valid token\n\n";
        continue;
    }
    
    // Generate new ID
    $newId = "user_{$device->user_id}_chip_{$oldId}";
    
    echo "   📦 Device: {$oldId}\n";
    echo "      Owner: " . ($device->user->email ?? 'N/A') . " (ID: {$device->user_id})\n";
    echo "      New ID: {$newId}\n";
    
    // Count related records
    $sensorCount = $device->sensors()->count();
    $commandCount = $device->commands()->count();
    $readingCount = SensorReading::whereIn('sensor_id', $device->sensors()->pluck('id'))->count();
    
    echo "      Related records:\n";
    echo "        • Sensors: {$sensorCount}\n";
    echo "        • Commands: {$commandCount}\n";
    echo "        • Readings: {$readingCount}\n\n";
    
    $migrations[] = [
        'device' => $device,
        'old_id' => $oldId,
        'new_id' => $newId,
        'sensor_count' => $sensorCount,
        'command_count' => $commandCount,
        'reading_count' => $readingCount,
    ];
}

if (empty($migrations)) {
    echo "✅ All devices are already in the new format!\n";
    exit(0);
}

// Step 2: Confirm migration
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║   MIGRATION PLAN                                               ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "Total devices to migrate: " . count($migrations) . "\n\n";

foreach ($migrations as $i => $migration) {
    echo "   " . ($i + 1) . ". {$migration['old_id']} → {$migration['new_id']}\n";
}

echo "\n";
echo "⚠️  This will update:\n";
echo "   • devices table (id column)\n";
echo "   • sensors table (device_id foreign key)\n";
echo "   • commands table (device_id foreign key)\n";
echo "   • provisioning_tokens table (claimed_device_id)\n";
echo "   • All EEPROM credentials on ESP8266 will need re-provisioning\n\n";

echo "Continue with migration? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));

if (strtolower($line) !== 'yes') {
    echo "\n❌ Migration cancelled.\n";
    exit(0);
}

// Step 3: Perform migration
echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║   EXECUTING MIGRATION                                          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

DB::beginTransaction();

try {
    foreach ($migrations as $i => $migration) {
        $device = $migration['device'];
        $oldId = $migration['old_id'];
        $newId = $migration['new_id'];
        
        echo "   [" . ($i + 1) . "/" . count($migrations) . "] Migrating {$oldId}...\n";
        
        // Create new device with new ID
        $newDevice = Device::create([
            'id' => $newId,
            'name' => $device->name,
            'location' => $device->location,
            'api_key' => \Illuminate\Support\Str::random(40), // Generate new API key
            'status' => $device->status,
            'last_seen' => $device->last_seen,
            'user_id' => $device->user_id,
            'created_at' => $device->created_at,
            'updated_at' => now(),
        ]);
        
        echo "      ✅ Created new device: {$newId}\n";
        echo "      🔑 New API key: " . substr($newDevice->api_key, 0, 8) . "...\n";
        
        // Update sensors
        DB::table('sensors')
            ->where('device_id', $oldId)
            ->update(['device_id' => $newId]);
        
        echo "      ✅ Updated {$migration['sensor_count']} sensor(s)\n";
        
        // Update commands
        DB::table('commands')
            ->where('device_id', $oldId)
            ->update(['device_id' => $newId]);
        
        echo "      ✅ Updated {$migration['command_count']} command(s)\n";
        
        // Update provisioning tokens
        $tokenCount = DB::table('provisioning_tokens')
            ->where('claimed_device_id', $oldId)
            ->update(['claimed_device_id' => $newId]);
        
        if ($tokenCount > 0) {
            echo "      ✅ Updated {$tokenCount} provisioning token(s)\n";
        }
        
        // Delete old device
        $device->delete();
        
        echo "      ✅ Removed old device: {$oldId}\n\n";
    }
    
    DB::commit();
    
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║   MIGRATION COMPLETE ✅                                        ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "Successfully migrated " . count($migrations) . " device(s)!\n\n";
    
    echo "⚠️  IMPORTANT: ESP8266 devices need re-provisioning\n";
    echo "   1. Old API credentials are preserved\n";
    echo "   2. Devices will continue to work until next restart\n";
    echo "   3. On restart, devices will need to provision again\n";
    echo "   4. Server will assign the new user-scoped device ID\n\n";
    
    echo "📊 Summary:\n";
    foreach ($migrations as $migration) {
        echo "   • {$migration['old_id']} → {$migration['new_id']}\n";
    }
    echo "\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    
    echo "\n❌ ERROR: Migration failed!\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   All changes have been rolled back.\n\n";
    
    exit(1);
}

echo "✅ Migration completed successfully!\n";
echo "   Next steps:\n";
echo "   1. Deploy updated firmware to ESP8266 devices\n";
echo "   2. Devices will auto-provision on startup\n";
echo "   3. New device IDs will be assigned automatically\n\n";
