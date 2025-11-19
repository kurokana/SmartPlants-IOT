<?php
/**
 * Quick script to create a provisioning token
 * Run: php artisan tinker < create-provision-token.php
 * Or: php create-provision-token.php (standalone)
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ProvisioningToken;
use Illuminate\Support\Str;

$token = ProvisioningToken::create([
    'token' => Str::random(36),
    'planned_device_id' => 'esp-plant-' . date('YmdHis'),
    'name_hint' => 'ESP8266 SmartPlant (Auto-generated)',
    'location_hint' => 'Home Lab',
    'expires_at' => now()->addDays(30), // 30 days validity
    'claimed' => false,
]);

echo "╔════════════════════════════════════════════════════════╗\n";
echo "║          ✅ PROVISIONING TOKEN CREATED                ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";
echo "📋 Token Details:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Token:       " . $token->token . "\n";
echo "Device ID:   " . $token->planned_device_id . "\n";
echo "Name:        " . $token->name_hint . "\n";
echo "Location:    " . $token->location_hint . "\n";
echo "Expires:     " . $token->expires_at . "\n";
echo "Status:      " . ($token->claimed ? 'CLAIMED' : 'UNCLAIMED') . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "📝 Update your ESP8266 code:\n";
echo "const char* provisionToken = \"" . $token->token . "\";\n\n";
echo "⏰ This token will expire in 30 days.\n";
