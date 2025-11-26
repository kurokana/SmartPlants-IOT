# Testing Guide - Device Ownership Security

## 📋 Testing Scenarios

### Test 1: New Device Provisioning ✅

**Setup:**
```bash
# Create token for user test@example.com (ID=1)
php artisan tinker
>>> $token = App\Models\ProvisioningToken::create([
...   'token' => Str::random(40),
...   'user_id' => 1,
...   'name_hint' => 'Test Device 1',
...   'expires_at' => now()->addDays(7)
... ]);
>>> echo $token->token;
```

**Steps:**
1. Copy token yang di-generate
2. Update `provisionToken` di firmware ESP8266
3. Upload firmware ke ESP8266 yang **FRESH** (EEPROM clear)
4. Monitor serial output untuk:
   - WiFi connection success
   - Provisioning request
   - Device ID received
   - API key received

**Expected Result:**
- ✅ ESP8266 serial: "✅ Credentials saved to EEPROM"
- ✅ Server logs: "New device provisioned" dengan user_id=1
- ✅ Dashboard user test@example.com menampilkan device baru
- ✅ Device status: ONLINE setelah mulai kirim data

**Verification:**
```bash
php scripts/check-devices.php
```
Expected output: Device muncul di user "test@example.com"

---

### Test 2: Re-provision Same Device (Same User) ✅

**Setup:**
```bash
# Create NEW token for SAME user (ID=1)
php artisan tinker
>>> $token2 = App\Models\ProvisioningToken::create([
...   'token' => Str::random(40),
...   'user_id' => 1,
...   'name_hint' => 'Test Device 1 Renewed',
...   'expires_at' => now()->addDays(7)
... ]);
>>> echo $token2->token;
```

**Steps:**
1. Update `provisionToken` dengan token BARU
2. Upload firmware ke ESP8266 yang SAMA (yang sudah provisioned sebelumnya)
3. ESP8266 akan clear EEPROM otomatis dan provision ulang
4. Monitor serial output

**Expected Result:**
- ✅ Device tetap muncul di dashboard user test@example.com
- ✅ API key BERUBAH (security feature)
- ✅ Device name/location bisa diupdate
- ✅ Server logs: "Device re-provisioned by same user"

**Verification:**
```bash
php scripts/check-devices.php
```
Device tetap ada, API key berbeda

---

### Test 3: Cross-User Claim Prevention ❌ (Should FAIL)

**Setup:**
```bash
# Create token for DIFFERENT user pedal@gmail.com (ID=2)
php artisan tinker
>>> $token3 = App\Models\ProvisioningToken::create([
...   'token' => Str::random(40),
...   'user_id' => 2,
...   'name_hint' => 'Stolen Device',
...   'expires_at' => now()->addDays(7)
... ]);
>>> echo $token3->token;
```

**Steps:**
1. Update `provisionToken` dengan token user BERBEDA
2. Upload firmware ke ESP8266 yang SUDAH DI-CLAIM user 1
3. Monitor serial output

**Expected Result:**
- ❌ ESP8266 serial: "❌ Provisioning failed (code 409)"
- ❌ Response: "Device already registered to another user"
- ❌ Dashboard user pedal@gmail.com: Device TIDAK MUNCUL
- ✅ Dashboard user test@example.com: Device MASIH MUNCUL
- ✅ Server logs: "Provisioning blocked: Device ownership conflict"

**Verification:**
```bash
php scripts/check-devices.php
```
Device tetap owned by user 1, tidak pindah ke user 2

---

### Test 4: Orphaned Device Auto-Claim ✅

**Setup:**
```bash
# Create orphaned device manually
php artisan tinker
>>> $device = App\Models\Device::create([
...   'id' => '999999',
...   'name' => 'Orphaned Device',
...   'location' => 'Unknown',
...   'api_key' => Str::random(40),
...   'status' => 'offline',
...   'user_id' => null  // Orphaned!
... ]);

# Create token for user 1
>>> $token4 = App\Models\ProvisioningToken::create([
...   'token' => Str::random(40),
...   'user_id' => 1,
...   'planned_device_id' => '999999',
...   'expires_at' => now()->addDays(7)
... ]);
>>> echo $token4->token;
```

**Steps:**
1. Modify firmware temporarily: change `ESP.getChipId()` to `999999`
2. Update `provisionToken` dengan token baru
3. Upload firmware
4. Monitor serial output

**Expected Result:**
- ✅ Provisioning success
- ✅ Device ownership assigned to user 1
- ✅ New API key generated
- ✅ Server logs: "Orphaned device claimed"

**Verification:**
```bash
php scripts/deploy-ownership-update.php
```
No orphaned devices remaining

---

### Test 5: Middleware API Request Validation ✅

**Test 5a: Valid Device Request**
```bash
# Send data with CORRECT credentials
curl -X POST https://kurokana.alwaysdata.net/api/ingest \
  -H "Content-Type: application/json" \
  -H "X-Device-Id: 62563" \
  -H "X-Api-Key: [CORRECT_API_KEY]" \
  -d '{
    "readings": [
      {"type": "soil", "value": 45.2},
      {"type": "temp", "value": 28.5}
    ]
  }'
```

**Expected:** HTTP 200, "Data received successfully"

**Test 5b: Invalid API Key**
```bash
# Send data with WRONG API key
curl -X POST https://kurokana.alwaysdata.net/api/ingest \
  -H "Content-Type: application/json" \
  -H "X-Device-Id: 62563" \
  -H "X-Api-Key: WRONG_KEY" \
  -d '{
    "readings": [
      {"type": "soil", "value": 45.2}
    ]
  }'
```

**Expected:** HTTP 403, "Invalid API key", hint about re-provisioning

**Test 5c: Missing Credentials**
```bash
curl -X POST https://kurokana.alwaysdata.net/api/ingest \
  -H "Content-Type: application/json" \
  -d '{
    "readings": [
      {"type": "soil", "value": 45.2}
    ]
  }'
```

**Expected:** HTTP 401, "Missing device credentials"

**Test 5d: Unknown Device**
```bash
curl -X POST https://kurokana.alwaysdata.net/api/ingest \
  -H "Content-Type: application/json" \
  -H "X-Device-Id: 000000" \
  -H "X-Api-Key: any_key" \
  -d '{
    "readings": [
      {"type": "soil", "value": 45.2}
    ]
  }'
```

**Expected:** HTTP 404, "Device not found"

---

### Test 6: Device Transfer (Admin Feature) ✅

**Setup:**
```bash
php artisan tinker
>>> $device = App\Models\Device::find('62563');
>>> $device->user_id;  // Check current owner
```

**Test 6a: Manual Transfer**
```bash
>>> $device->reassignTo(2, 'User requested transfer');
>>> $device->user_id;  // Should be 2 now
```

**Expected:**
- ✅ Device ownership changed to user 2
- ✅ New API key generated
- ✅ Server logs: "Device ownership transferred"
- ✅ Dashboard user 2: Device muncul
- ✅ Dashboard user 1: Device hilang

**Test 6b: Release Ownership**
```bash
>>> $device->releaseOwnership('Testing orphan state');
>>> $device->user_id;  // Should be null
```

**Expected:**
- ✅ Device user_id = null (orphaned)
- ✅ Status = offline
- ✅ Server logs: "Device ownership released"
- ✅ API requests: HTTP 403 "Orphaned device"

---

### Test 7: Command System with Ownership ✅

**Setup:**
```bash
# Create water command for device owned by user 1
php artisan tinker
>>> $device = App\Models\Device::find('62563');
>>> $device->user_id;  // Verify owner

>>> $cmd = App\Models\Command::create([
...   'device_id' => $device->id,
...   'command' => 'water_on',
...   'params' => ['duration_sec' => 10],
...   'status' => 'pending'
... ]);
```

**Steps:**
1. ESP8266 polls `/api/commands/next` dengan valid credentials
2. Receives command
3. Executes water pump
4. Sends ACK to `/api/commands/{id}/ack`

**Expected Result:**
- ✅ Command received by correct device only
- ✅ Middleware validates device ownership
- ✅ ACK only accepted from device owner
- ✅ Server logs: "Command acknowledged"

**Invalid Test:**
```bash
# Try to poll commands with different device credentials
curl https://kurokana.alwaysdata.net/api/commands/next \
  -H "X-Device-Id: 000000" \
  -H "X-Api-Key: invalid"
```

**Expected:** HTTP 404, "Device not found"

---

## 🔍 Monitoring & Verification Commands

### Check System Health
```bash
php scripts/deploy-ownership-update.php
```

### Check Devices Per User
```bash
php scripts/check-devices.php
```

### Check Provisioning Tokens
```bash
php scripts/check-tokens.php
```

### Check Production Logs
```bash
# Via SSH to AlwaysData
tail -f storage/logs/laravel.log | grep -E "Provisioning|Device ownership"
```

### Database Direct Check
```bash
php artisan tinker
>>> DB::table('devices')->select('id', 'name', 'user_id', 'last_seen')->get();
>>> DB::table('provisioning_tokens')->select('token', 'user_id', 'claimed', 'claimed_device_id')->get();
```

---

## ✅ Test Checklist

- [ ] **Test 1**: New device provisioning → Device muncul di user yang benar
- [ ] **Test 2**: Re-provision same user → API key berubah, device tetap muncul
- [ ] **Test 3**: Cross-user claim → HTTP 409, device tidak pindah
- [ ] **Test 4**: Orphaned device claim → Auto-assigned ke token owner
- [ ] **Test 5a**: Valid API request → HTTP 200
- [ ] **Test 5b**: Invalid API key → HTTP 403
- [ ] **Test 5c**: Missing credentials → HTTP 401
- [ ] **Test 5d**: Unknown device → HTTP 404
- [ ] **Test 6a**: Manual transfer → Ownership changed, new API key
- [ ] **Test 6b**: Release ownership → Device orphaned
- [ ] **Test 7**: Command system → Only owner receives commands

---

## 🐛 Troubleshooting

### Device Tidak Muncul Setelah Provisioning

**Check 1: Serial Output**
```
Look for:
✅ WiFi Connected!
✅ Provisioning...
✅ Credentials saved to EEPROM
```

**Check 2: Server Logs**
```bash
tail -f storage/logs/laravel.log | grep "62563"
```

**Check 3: Database**
```bash
php artisan tinker
>>> App\Models\Device::find('62563');
>>> // Check user_id matches expected user
```

### Provisioning Error -5

**Possible causes:**
1. Token expired
2. Token already used
3. Device already claimed by different user
4. Network/HTTPS issue

**Solution:**
```bash
# Check token validity
php artisan tinker
>>> $token = App\Models\ProvisioningToken::where('token', 'xxx')->first();
>>> $token->claimed;  // Should be false
>>> $token->expires_at->isFuture();  // Should be true
```

### Device Ownership Conflict

**Diagnosis:**
```bash
php scripts/check-tokens.php
```

**Solution:**
```bash
# Option 1: Transfer device
php artisan tinker
>>> $device = App\Models\Device::find('62563');
>>> $device->reassignTo(1, 'Manual fix');

# Option 2: Clear EEPROM and re-provision
# Upload ESP8266_Clear_EEPROM.ino first
```

---

## 📊 Expected Test Results Summary

| Test | Scenario | Expected HTTP | Expected Logs | Dashboard Result |
|------|----------|---------------|---------------|------------------|
| 1 | New device | 200 | "New device provisioned" | Device muncul di user 1 |
| 2 | Re-provision same user | 200 | "Device re-provisioned" | Device tetap di user 1, API key baru |
| 3 | Cross-user claim | 409 | "Provisioning blocked" | Device tetap di user 1 |
| 4 | Orphaned claim | 200 | "Orphaned device claimed" | Device assigned ke token owner |
| 5a | Valid request | 200 | "Data received" | Data tersimpan |
| 5b | Invalid API key | 403 | "Invalid API key" | Request ditolak |
| 5c | Missing credentials | 401 | "Missing credentials" | Request ditolak |
| 5d | Unknown device | 404 | "Device not found" | Request ditolak |
| 6a | Manual transfer | - | "Ownership transferred" | Device pindah ke user baru |
| 6b | Release ownership | - | "Ownership released" | Device orphaned |
| 7 | Command with ownership | 200 | "Command sent" | Hanya device owner terima |

---

**Last Updated:** November 26, 2025  
**Version:** 2.0  
**Status:** Production Ready ✅
