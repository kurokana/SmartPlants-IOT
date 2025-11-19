# 🔐 ESP8266 HTTPS Troubleshooting

## Error Code -5: Connection Refused

### 🔴 Masalah:
```
❌ HTTP failed (code -5)
```

**Penyebab:** ESP8266 tidak bisa connect ke HTTPS server karena SSL/TLS verification gagal.

### ✅ Solusi:

#### 1. Gunakan WiFiClientSecure dengan setInsecure()

**File yang sudah diperbaiki:** `ESP8266_SmartPlants.ino` dan `ESP8266_SmartPlants_HTTPS.ino`

**Code yang benar:**

```cpp
#include <WiFiClientSecure.h>  // Tambahkan library

// Konfigurasi
const char* serverUrl = "https://kurokana.alwaysdata.net";
const bool USE_HTTPS = true;

// Di fungsi provisioning dan sendSensorData
HTTPClient http;

if (USE_HTTPS) {
  WiFiClientSecure client;
  client.setInsecure();  // Skip SSL verification
  http.begin(client, url);
} else {
  WiFiClient client;
  http.begin(client, url);
}
```

**❌ Yang SALAH:**
```cpp
WiFiClient client;  // Tidak support HTTPS!
http.begin(client, "https://...");  // ERROR -5
```

**✅ Yang BENAR:**
```cpp
WiFiClientSecure client;  // Support HTTPS
client.setInsecure();     // Skip certificate check
http.begin(client, "https://...");  // OK!
```

#### 2. Upload Firmware yang Sudah Diperbaiki

**Option A: File Utama (Flexible)**
```
esp8266/ESP8266_SmartPlants.ino
```

Edit di bagian atas:
```cpp
// HTTPS (AlwaysData production)
const char* serverUrl = "https://kurokana.alwaysdata.net";
const bool USE_HTTPS = true;

// Atau HTTP (local development)
// const char* serverUrl = "http://192.168.1.100:8000";
// const bool USE_HTTPS = false;
```

**Option B: File Khusus HTTPS**
```
esp8266/ESP8266_SmartPlants_HTTPS.ino
```

Sudah hardcoded untuk HTTPS, tidak perlu ubah apapun.

### 📋 Checklist Upload

- [ ] Arduino IDE terbuka
- [ ] File `.ino` yang benar dipilih
- [ ] WiFi credentials sudah benar (`ssid` dan `password`)
- [ ] Server URL sudah benar (`https://kurokana.alwaysdata.net`)
- [ ] `USE_HTTPS = true` (untuk ESP8266_SmartPlants.ino)
- [ ] Board: ESP8266 NodeMCU 1.0
- [ ] Upload Speed: 115200
- [ ] Serial Monitor ditutup
- [ ] Upload!

### 🧪 Test Setelah Upload

1. **Buka Serial Monitor** (115200 baud)
2. **Reset ESP8266** (tombol RST atau power cycle)
3. **Lihat output:**

```
========================================
🌱 SmartPlants ESP8266 (HTTPS Version)
========================================
✅ Credentials loaded from EEPROM
Device ID: esp-plant-01
API Key: abcd1234...
📡 Connecting to WiFi: pedal
.....
✅ WiFi connected!
IP: 192.168.1.100
Server: https://kurokana.alwaysdata.net

✅ Device provisioned and ready!
🚀 Starting data transmission...

📊 Sensor Dummy Data:
Soil: 55.55%
Temp: 30.48°C
Hum : 71.36%
RGB : (172, 85, 202)
📤 Sending: {"readings":[...]}
✅ Data sent successfully!  <-- SUKSES!
⏳ Next send in 7143 ms...
```

### ❌ Error Lainnya

#### Error -1 (Connection Timeout)
```
❌ HTTP failed (code -1)
```

**Penyebab:** Server tidak reachable

**Fix:**
- Cek server running: `ssh kurokana@ssh1.alwaysdata.net "cd ~/smartplants && php artisan serve"`
- Cek firewall
- Test ping: `ping kurokana.alwaysdata.net`

#### Error -2 (Send Failed)
```
❌ HTTP failed (code -2)
```

**Penyebab:** Network unstable

**Fix:**
- Cek WiFi signal strength
- Restart router
- Pindah ESP8266 lebih dekat ke router

#### HTTP 401 (Unauthorized)
```
❌ HTTP error: 401
```

**Penyebab:** API key salah atau expired

**Fix:**
- Clear EEPROM dan provisioning ulang:
  ```cpp
  // Uncomment di setup()
  clearCredentials();
  while(1) delay(1000);
  ```
- Upload, biarkan clear EEPROM
- Comment kembali, upload lagi
- Provisioning otomatis akan jalan

#### HTTP 500 (Server Error)
```
❌ HTTP error: 500
```

**Penyebab:** Error di Laravel server

**Fix:**
- SSH ke server: `ssh kurokana@ssh1.alwaysdata.net`
- Clear cache: `cd ~/smartplants && php artisan route:clear && php artisan config:clear`
- Check log: `tail -50 storage/logs/laravel.log`

## 🔧 Development vs Production

### Local Development (HTTP)
```cpp
const char* serverUrl = "http://192.168.137.1:8000";
const bool USE_HTTPS = false;
```

**Keuntungan:**
- ✅ Faster (no SSL overhead)
- ✅ Easier debugging
- ✅ Works with `php artisan serve`

**Kekurangan:**
- ❌ Not secure
- ❌ Only for local network

### Production (HTTPS)
```cpp
const char* serverUrl = "https://kurokana.alwaysdata.net";
const bool USE_HTTPS = true;
```

**Keuntungan:**
- ✅ Secure (encrypted)
- ✅ Works over internet
- ✅ Production-ready

**Kekurangan:**
- ❌ Slightly slower
- ❌ Requires WiFiClientSecure

## 📊 HTTP Error Codes Reference

| Code | Meaning | Common Cause |
|------|---------|--------------|
| -1 | Connection timeout | Server unreachable |
| -2 | Send failed | Network unstable |
| -3 | Lost connection | WiFi dropped |
| -4 | No stream | Invalid response |
| -5 | Connection refused | SSL/HTTPS issue |
| -6 | Connection lost | Server closed |
| 200 | OK | Success! |
| 400 | Bad request | Invalid JSON |
| 401 | Unauthorized | Wrong API key |
| 404 | Not found | Wrong URL |
| 500 | Server error | Laravel error |

## 🆘 Still Not Working?

1. **Copy full Serial Monitor output**
2. **Check server logs:**
   ```bash
   ssh kurokana@ssh1.alwaysdata.net
   tail -100 ~/smartplants/storage/logs/laravel.log
   ```
3. **Test endpoint manually:**
   ```bash
   curl -X POST https://kurokana.alwaysdata.net/api/ingest \
     -H "Content-Type: application/json" \
     -H "X-Device-Id: test-device" \
     -H "X-Api-Key: test-key" \
     -d '{"readings":[{"type":"soil","value":50}]}'
   ```

---

**Quick Fix Summary:**

1. ✅ Use `WiFiClientSecure` for HTTPS
2. ✅ Call `client.setInsecure()` to skip SSL check
3. ✅ Set `USE_HTTPS = true` in code
4. ✅ Upload firmware baru
5. ✅ Monitor Serial untuk verify

**File yang sudah fixed:**
- `esp8266/ESP8266_SmartPlants.ino` (flexible HTTP/HTTPS)
- `esp8266/ESP8266_SmartPlants_HTTPS.ino` (HTTPS only)
