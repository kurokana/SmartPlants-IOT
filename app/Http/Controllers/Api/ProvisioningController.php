<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProvisioningController extends Controller
{
    public function claim(Request $req)
    {
        $data = $req->validate([
            'token' => 'required|string',
            'device_id' => 'required|string', // Raw chip ID from ESP8266
            'name' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        // Validate token
        $pt = \App\Models\ProvisioningToken::where('token', $data['token'])->first();
        if (!$pt) {
            \Log::warning('Provisioning failed: Invalid token', ['token' => $data['token']]);
            return response()->json(['message' => 'Invalid provisioning token'], 404);
        }
        
        if ($pt->claimed) {
            \Log::warning('Provisioning failed: Token already used', [
                'token' => $pt->token,
                'claimed_at' => $pt->claimed_at,
                'claimed_device_id' => $pt->claimed_device_id
            ]);
            return response()->json(['message' => 'Token already claimed'], 409);
        }
        
        if ($pt->expires_at->isPast()) {
            \Log::warning('Provisioning failed: Token expired', [
                'token' => $pt->token,
                'expired_at' => $pt->expires_at
            ]);
            return response()->json(['message' => 'Token expired'], 410);
        }

        // Generate unique device ID per user: user_{user_id}_chip_{chip_id}
        // This ensures same ESP8266 can be used by different users without conflicts
        $chipId = $data['device_id']; // Raw chip ID from ESP8266 (e.g., "62563")
        $uniqueDeviceId = "user_{$pt->user_id}_chip_{$chipId}";
        
        \Log::info('Processing provisioning request', [
            'raw_chip_id' => $chipId,
            'unique_device_id' => $uniqueDeviceId,
            'user_id' => $pt->user_id,
        ]);

        // Check if device already exists with this unique ID
        $device = \App\Models\Device::find($uniqueDeviceId);
        
        if (!$device) {
            // NEW DEVICE: Create with unique ID per user
            $device = \App\Models\Device::create([
                'id' => $uniqueDeviceId,
                'name' => $data['name'] ?? $pt->name_hint ?? 'ESP8266 SmartPlant',
                'location' => $data['location'] ?? $pt->location_hint ?? 'Home',
                'api_key' => Str::random(40),
                'status' => 'offline',
                'user_id' => $pt->user_id,
            ]);

            // Create default sensors
            $defaultSensors = [
                ['type' => 'soil', 'unit' => '%', 'label' => 'Soil Moisture'],
                ['type' => 'temp', 'unit' => 'C', 'label' => 'Temperature'],
                ['type' => 'hum', 'unit' => '%', 'label' => 'Humidity'],
                ['type' => 'color_r', 'unit' => 'au', 'label' => 'Leaf Red'],
                ['type' => 'color_g', 'unit' => 'au', 'label' => 'Leaf Green'],
                ['type' => 'color_b', 'unit' => 'au', 'label' => 'Leaf Blue'],
            ];
            
            foreach ($defaultSensors as $sensorDef) {
                \App\Models\Sensor::firstOrCreate(
                    array_merge($sensorDef, ['device_id' => $device->id]),
                    $sensorDef
                );
            }

            \Log::info('New device provisioned with unique ID', [
                'chip_id' => $chipId,
                'device_id' => $device->id,
                'user_id' => $pt->user_id,
                'user_email' => $pt->user->email ?? 'N/A',
                'token' => $pt->token,
            ]);
            
        } else {
            // EXISTING DEVICE: This user already provisioned this chip before
            // Re-provision: Generate new API key for security
            
            $oldApiKey = $device->api_key;
            $device->update([
                'api_key' => Str::random(40),
                'name' => $data['name'] ?? $device->name,
                'location' => $data['location'] ?? $device->location,
                'status' => 'offline',
            ]);
            
            \Log::info('Device re-provisioned with new API key', [
                'chip_id' => $chipId,
                'device_id' => $device->id,
                'user_id' => $pt->user_id,
                'old_api_key' => substr($oldApiKey, 0, 8) . '...',
                'new_api_key' => substr($device->api_key, 0, 8) . '...',
                'token' => $pt->token,
            ]);
        }

        // Mark token as claimed
        $pt->update([
            'claimed' => true,
            'claimed_device_id' => $device->id,
            'claimed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Device provisioned successfully',
            'device_id' => $device->id,
            'api_key' => $device->api_key,
            'owner' => $device->user->email ?? 'N/A',
        ]);
    }
}
