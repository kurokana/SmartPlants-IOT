# User-Scoped Device ID System

## 🎯 Problem Solved

**SEBELUM:**
- ESP8266 chip ID sama (contoh: `62563`) digunakan oleh banyak user
- Jika user berbeda provision ESP8266 yang sama → device ID bentrok
- Device ownership ter-override antar user
- Tidak bisa 2 user pakai ESP8266 yang sama (bahkan di waktu berbeda)

**SESUDAH:**
- ✅ Device ID otomatis unik per user: `user_{user_id}_chip_{chip_id}`
- ✅ ESP8266 yang sama bisa dipakai user berbeda tanpa konflik
- ✅ Tidak ada lagi ownership conflict
- ✅ Sistem otomatis, tidak perlu input manual

---

## 🔧 How It Works

### Device ID Format

**Old Format (Raw Chip ID):**
```
62563
```

**New Format (User-Scoped):**
```
user_1_chip_62563  ← User ID 1, Chip ID 62563
user_2_chip_62563  ← User ID 2, SAME chip ID
```

### Provisioning Flow

1. **ESP8266 sends raw chip ID:**
   ```json
   {
     "token": "abc123...",
     "device_id": "62563",  ← Raw chip ID from ESP.getChipId()
     "name": "ESP8266 SmartPlant"
   }
   ```

2. **Server generates unique device ID:**
   ```php
   $chipId = "62563";  // From ESP8266
   $userId = 1;        // From provisioning token
   
   $uniqueDeviceId = "user_{$userId}_chip_{$chipId}";
   // Result: "user_1_chip_62563"
   ```

3. **Server returns unique device ID:**
   ```json
   {
     "message": "Device provisioned successfully",
     "device_id": "user_1_chip_62563",  ← Unique ID
     "api_key": "aBcDeF123456..."
   }
   ```

4. **ESP8266 saves credentials:**
   ```cpp
   creds.deviceId = "user_1_chip_62563";  // Saved to EEPROM
   creds.apiKey = "aBcDeF123456...";
   ```

5. **ESP8266 uses unique ID for all requests:**
   ```cpp
   http.addHeader("X-Device-Id", creds.deviceId);
   // Sends: "user_1_chip_62563"
   ```

---

## 📊 Examples

### Scenario 1: Different Users, Same ESP8266

**User 1 (test@example.com) provisions ESP8266 chip #62563:**
```
Token: user_id=1
Chip:  62563
→ Device ID: user_1_chip_62563
```

**User 2 (pedal@gmail.com) provisions SAME ESP8266 chip #62563:**
```
Token: user_id=2
Chip:  62563
→ Device ID: user_2_chip_62563  ← DIFFERENT device in database!
```

**Result:**
- ✅ Database has 2 separate devices
- ✅ User 1 dashboard: Shows `user_1_chip_62563`
- ✅ User 2 dashboard: Shows `user_2_chip_62563`
- ✅ No conflict!

### Scenario 2: Same User, Multiple ESP8266

**User 1 provisions ESP8266 #62563:**
```
Device ID: user_1_chip_62563
```

**User 1 provisions ESP8266 #99999:**
```
Device ID: user_1_chip_99999
```

**Result:**
- ✅ User 1 dashboard shows 2 devices
- ✅ Each device has unique ID
- ✅ Both owned by same user

---

## 🔄 Migration from Old System

### Automatic Migration Script

```bash
php scripts/migrate-device-ids.php
```

**What it does:**
1. Lists all devices with old format IDs
2. Generates new user-scoped IDs
3. Updates devices table
4. Updates sensors, commands, readings
5. Updates provisioning tokens
6. Preserves API keys temporarily

**Example migration:**
```
OLD: 62563 (user_id=1)
NEW: user_1_chip_62563

Related records updated:
• 6 sensors
• 2 commands
• 150 sensor readings
• 1 provisioning token
```

### Manual Migration (if needed)

```php
php artisan tinker

// Find old device
>>> $device = App\Models\Device::find('62563');

// Generate new ID
>>> $oldId = $device->id;
>>> $newId = "user_{$device->user_id}_chip_{$oldId}";

// Create new device
>>> $newDevice = App\Models\Device::create([
...   'id' => $newId,
...   'name' => $device->name,
...   'location' => $device->location,
...   'api_key' => $device->api_key,
...   'user_id' => $device->user_id,
...   'status' => $device->status,
...   'last_seen' => $device->last_seen,
... ]);

// Update sensors
>>> DB::table('sensors')->where('device_id', $oldId)->update(['device_id' => $newId]);

// Update commands
>>> DB::table('commands')->where('device_id', $oldId)->update(['device_id' => $newId]);

// Update tokens
>>> DB::table('provisioning_tokens')->where('claimed_device_id', $oldId)->update(['claimed_device_id' => $newId]);

// Delete old device
>>> $device->delete();
```

---

## 🚀 Deployment Steps

### Step 1: Deploy Backend Changes

```bash
# Commit changes
git add .
git commit -m "feat: Implement user-scoped device ID system"
git push origin main
```

Auto-deployment will update production server.

### Step 2: Migrate Existing Devices

```bash
# SSH to production server
ssh user@kurokana.alwaysdata.net

# Run migration
php scripts/migrate-device-ids.php
```

### Step 3: Update ESP8266 Firmware

**Changes in firmware:**
```cpp
// EEPROM structure updated
struct Credentials {
  char deviceId[64];  // Increased from 32 to support longer IDs
  char apiKey[48];
};
```

**Upload new firmware to all ESP8266 devices.**

### Step 4: Re-provision Devices

After updating firmware:
1. Device will auto-provision on startup
2. Server generates new user-scoped device ID
3. Device saves new ID to EEPROM
4. Device uses new ID for all future requests

---

## 🔍 Verification

### Check Device IDs in Database

```bash
php artisan tinker
>>> App\Models\Device::all(['id', 'user_id', 'name']);
```

**Expected output:**
```
[
  {
    "id": "user_1_chip_62563",
    "user_id": 1,
    "name": "ESP8266 SmartPlant"
  },
  {
    "id": "user_2_chip_62563",
    "user_id": 2,
    "name": "ESP8266 SmartPlant"
  }
]
```

### Check Provisioning Logs

```bash
tail -f storage/logs/laravel.log | grep "Processing provisioning"
```

**Expected log:**
```
[INFO] Processing provisioning request
- raw_chip_id: 62563
- unique_device_id: user_1_chip_62563
- user_id: 1
```

### Verify Dashboard

1. Login as User 1
2. Check devices: Should see `user_1_chip_62563`
3. Login as User 2
4. Check devices: Should see `user_2_chip_62563`

---

## 🧪 Testing

### Test 1: Same Chip, Different Users

```bash
# Create token for user 1
php artisan tinker
>>> $token1 = App\Models\ProvisioningToken::create([
...   'token' => Str::random(40),
...   'user_id' => 1,
...   'expires_at' => now()->addDays(7)
... ]);

# Provision ESP8266 #62563 with user 1 token
# Device ID created: user_1_chip_62563

# Create token for user 2
>>> $token2 = App\Models\ProvisioningToken::create([
...   'token' => Str::random(40),
...   'user_id' => 2,
...   'expires_at' => now()->addDays(7)
... ]);

# Provision SAME ESP8266 #62563 with user 2 token
# Device ID created: user_2_chip_62563
```

**Expected:**
- ✅ 2 separate devices in database
- ✅ No conflict errors
- ✅ Both users see their respective devices

### Test 2: Re-provision Same User

```bash
# User 1 provisions ESP8266 again with NEW token
>>> $token3 = App\Models\ProvisioningToken::create([
...   'token' => Str::random(40),
...   'user_id' => 1,
...   'expires_at' => now()->addDays(7)
... ]);

# Provision with new token
# Device ID: user_1_chip_62563 (same as before)
```

**Expected:**
- ✅ Device already exists, re-provision
- ✅ New API key generated
- ✅ Device still shows in user 1 dashboard

---

## 📋 Database Schema

### Devices Table

```sql
CREATE TABLE devices (
  id VARCHAR(255) PRIMARY KEY,  -- "user_1_chip_62563"
  name VARCHAR(255),
  location VARCHAR(255),
  api_key VARCHAR(40),
  status VARCHAR(20),
  last_seen TIMESTAMP,
  user_id BIGINT,  -- Foreign key to users
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

-- Indexes
CREATE INDEX devices_user_id_index ON devices(user_id);
CREATE INDEX devices_user_status_index ON devices(user_id, status);
```

### Query Examples

**Get all devices for a user:**
```sql
SELECT * FROM devices WHERE user_id = 1;
```

**Check for device ID conflicts (should return 0):**
```sql
SELECT id, COUNT(*) as count 
FROM devices 
GROUP BY id 
HAVING COUNT(*) > 1;
```

---

## 🛡️ Security Benefits

1. **Namespace Isolation**
   - Each user has their own device namespace
   - No cross-user ID collisions

2. **Predictable IDs**
   - Format: `user_{user_id}_chip_{chip_id}`
   - Easy to identify ownership from ID alone

3. **Automatic Conflict Prevention**
   - System generates unique IDs automatically
   - No manual intervention needed

4. **Audit Trail**
   - Device ID shows which user owns it
   - Easy to track ownership history

---

## 💡 Future Enhancements

### 1. Device Aliases
Allow users to rename devices:
```php
// Display name vs system ID
'display_name' => 'Living Room Plant'
'id' => 'user_1_chip_62563'  // System ID (hidden)
```

### 2. Device Transfer Between Users
```php
// Transfer device from user 1 to user 2
$device->transferTo($newUserId);
// Creates: user_2_chip_62563
// Deletes: user_1_chip_62563
```

### 3. Device Groups
Group multiple ESP8266 devices:
```php
$group = DeviceGroup::create([
  'name' => 'Garden Sensors',
  'user_id' => 1,
  'device_ids' => [
    'user_1_chip_62563',
    'user_1_chip_99999',
  ]
]);
```

---

## 🐛 Troubleshooting

### Device Not Appearing After Provision

**Check device ID in database:**
```bash
php artisan tinker
>>> App\Models\Device::where('user_id', 1)->pluck('id');
```

**Check provisioning logs:**
```bash
tail -f storage/logs/laravel.log | grep "device_id"
```

### Old Device ID Still Showing

**Run migration script:**
```bash
php scripts/migrate-device-ids.php
```

**Or manually update:**
```php
>>> $device = App\Models\Device::find('62563');
>>> $device->update(['id' => "user_{$device->user_id}_chip_62563"]);
```

### EEPROM Too Small Error

**Check EEPROM size in firmware:**
```cpp
EEPROM.begin(512);  // Must be at least 512 bytes

struct Credentials {
  char deviceId[64];  // 64 bytes for longer IDs
  char apiKey[48];    // 48 bytes
};
```

---

## ✅ Summary

**Benefits:**
- ✅ Automatic unique device IDs per user
- ✅ No more ownership conflicts
- ✅ Same ESP8266 can be used by multiple users
- ✅ Clean namespace isolation
- ✅ Easy migration from old system

**Format:**
```
user_{user_id}_chip_{chip_id}
```

**Example:**
```
User 1, Chip 62563 → user_1_chip_62563
User 2, Chip 62563 → user_2_chip_62563
```

**Migration:**
```bash
php scripts/migrate-device-ids.php
```

---

**Version:** 3.0  
**Date:** November 26, 2025  
**Status:** Production Ready ✅
