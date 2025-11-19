/*
 * SmartPlants ESP8266 - Dummy Data (HTTPS Fixed)
 */

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <EEPROM.h>
#include <WiFiClientSecure.h>

// ===== PIN KONFIGURASI =====
// (Pin sensor diabaikan untuk dummy)

// ===== WiFi & Server =====
const char* ssid = "pedal";
const char* password = "12345689";
const char* serverUrl = "https://kurokana.alwaysdata.net";
const char* provisionToken = "Teeh8cRkZtBo9rpj4S7NmAkHgD4p122dbLR6";

// ===== EEPROM Credentials =====
struct Credentials {
  char magic[4];
  char deviceId[32];
  char apiKey[48];
  bool isValid;
};
Credentials creds;

// ===== Helper EEPROM =====
void saveCredentials() {
  EEPROM.begin(512);
  creds.magic[0] = 'S'; creds.magic[1] = 'P'; creds.magic[2] = 'L'; creds.magic[3] = 'T';
  creds.isValid = true;
  EEPROM.put(0, creds);
  EEPROM.commit();
  EEPROM.end();
  Serial.println("✅ Credentials saved to EEPROM");
}

void loadCredentials() {
  EEPROM.begin(512);
  EEPROM.get(0, creds);
  EEPROM.end();

  if (creds.magic[0]=='S' && creds.magic[1]=='P' && creds.magic[2]=='L' && creds.magic[3]=='T' && creds.isValid) {
    Serial.println("✅ Credentials loaded from EEPROM");
  } else {
    creds.isValid = false;
    Serial.println("⚠️ No valid credentials found");
  }
}

// ===== Provisioning (HTTPS) =====
bool doProvisioning() {
  WiFiClientSecure client;
  client.setInsecure();  // Skip SSL certificate verification
  
  HTTPClient http;
  String url = String(serverUrl) + "/api/provision/claim";

  Serial.println("🔐 Connecting to HTTPS server for provisioning...");
  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");

  DynamicJsonDocument doc(256);
  doc["token"] = provisionToken;
  doc["device_id"] = String(ESP.getChipId());
  doc["name"] = "ESP8266 SmartPlant (Dummy)";
  doc["location"] = "Home Test";

  String body;
  serializeJson(doc, body);
  Serial.println("📤 Provisioning payload: " + body);
  
  int code = http.POST(body);
  Serial.printf("📥 HTTP Response Code: %d\n", code);

  if (code == 200) {
    String response = http.getString();
    Serial.println("📥 Response: " + response);
    
    DynamicJsonDocument resDoc(512);
    DeserializationError error = deserializeJson(resDoc, response);
    
    if (error) {
      Serial.println("❌ JSON parse error: " + String(error.c_str()));
      http.end();
      return false;
    }

    String id = resDoc["device_id"].as<String>();
    String key = resDoc["api_key"].as<String>();

    id.toCharArray(creds.deviceId, 32);
    key.toCharArray(creds.apiKey, 48);
    saveCredentials();
    http.end();
    
    Serial.println("✅ Provisioning successful!");
    Serial.println("Device ID: " + String(creds.deviceId));
    Serial.println("API Key: " + String(creds.apiKey));
    return true;
  } else {
    Serial.printf("❌ Provisioning failed (code %d)\n", code);
    String response = http.getString();
    Serial.println("Response: " + response);
    http.end();
    return false;
  }
}

// ===== Generate Dummy Data =====
float randomFloat(float min, float max) {
  return min + ((float)random(1000) / 1000.0) * (max - min);
}

// ===== Kirim Data (HTTPS) =====
bool sendSensorData(float soil, float temp, float hum, float r, float g, float b) {
  WiFiClientSecure client;
  client.setInsecure();  // Skip SSL certificate verification
  
  HTTPClient http;
  String url = String(serverUrl) + "/api/ingest";

  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Device-Id", String(creds.deviceId));
  http.addHeader("X-Api-Key", String(creds.apiKey));

  DynamicJsonDocument doc(512);
  JsonArray readings = doc.createNestedArray("readings");

  JsonObject j1 = readings.createNestedObject(); j1["type"] = "soil"; j1["value"] = soil;
  JsonObject j2 = readings.createNestedObject(); j2["type"] = "temp"; j2["value"] = temp;
  JsonObject j3 = readings.createNestedObject(); j3["type"] = "hum";  j3["value"] = hum;
  JsonObject j4 = readings.createNestedObject(); j4["type"] = "color_r"; j4["value"] = r;
  JsonObject j5 = readings.createNestedObject(); j5["type"] = "color_g"; j5["value"] = g;
  JsonObject j6 = readings.createNestedObject(); j6["type"] = "color_b"; j6["value"] = b;

  String body;
  serializeJson(doc, body);
  Serial.println("📤 Sending: " + body);

  int code = http.POST(body);
  
  if (code == 200) {
    String response = http.getString();
    Serial.println("✅ Data sent successfully!");
    Serial.println("Response: " + response);
    http.end();
    return true;
  } else {
    Serial.printf("❌ HTTP failed (code %d)\n", code);
    String response = http.getString();
    Serial.println("Response: " + response);
    
    // If 401 Unauthorized, force re-provisioning
    if (code == 401) {
      Serial.println("\n⚠️ 401 UNAUTHORIZED! Credentials invalid.");
      Serial.println("This usually means:");
      Serial.println("  1. Provision token expired/invalid");
      Serial.println("  2. Device was deleted from server");
      Serial.println("  3. API key mismatch");
      Serial.println("\n🔄 Auto re-provisioning in 3 seconds...");
      http.end();
      delay(3000);
      
      creds.isValid = false;
      if (doProvisioning()) {
        Serial.println("✅ Re-provisioning successful! Retrying send...");
        return sendSensorData(soil, temp, hum, r, g, b);
      } else {
        Serial.println("❌ Re-provisioning failed. Restarting device...");
        delay(5000);
        ESP.restart();
      }
    }
    
    http.end();
    return false;
  }
}

// ===== Setup =====
void setup() {
  Serial.begin(115200);
  delay(100);
  Serial.println("\n╔════════════════════════════════════╗");
  Serial.println("║ SmartPlants DUMMY MODE (HTTPS)    ║");
  Serial.println("╚════════════════════════════════════╝");
  
  randomSeed(analogRead(0));

  Serial.println("\n📡 Connecting to WiFi...");
  Serial.println("SSID: " + String(ssid));
  
  WiFi.begin(ssid, password);
  int attempt = 0;
  while (WiFi.status() != WL_CONNECTED && attempt < 30) {
    delay(500); 
    Serial.print(".");
    attempt++;
  }

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\n❌ WiFi Connection Failed!");
    Serial.println("Restarting in 5 seconds...");
    delay(5000);
    ESP.restart();
  }
  
  Serial.println("\n✅ WiFi Connected!");
  Serial.println("IP Address: " + WiFi.localIP().toString());
  Serial.println("Signal Strength: " + String(WiFi.RSSI()) + " dBm");

  loadCredentials();
  if (!creds.isValid) {
    Serial.println("\n⚙️ No credentials found. Starting provisioning...");
    if (!doProvisioning()) {
      Serial.println("❌ Provisioning failed. Restarting in 10 seconds...");
      delay(10000);
      ESP.restart();
    }
  } else {
    Serial.println("\n✅ Using saved credentials:");
    Serial.println("Device ID: " + String(creds.deviceId));
    Serial.println("API Key: " + String(creds.apiKey));
    
    // TEST: Force re-provisioning if getting 401 errors
    Serial.println("\n⚠️ If you're getting 401 errors, type 'R' in Serial Monitor to re-provision");
    Serial.println("Waiting 5 seconds for input...");
    delay(5000);
    
    if (Serial.available() > 0) {
      char cmd = Serial.read();
      if (cmd == 'R' || cmd == 'r') {
        Serial.println("\n🔄 Force re-provisioning...");
        creds.isValid = false;
        if (!doProvisioning()) {
          Serial.println("❌ Re-provisioning failed. Restarting...");
          delay(5000);
          ESP.restart();
        }
      }
    }
  }

  Serial.println("\n✅ Device ready! Starting dummy data transmission...\n");
}

// ===== Loop =====
unsigned long lastSend = 0;
const unsigned long sendInterval = 8000; // 8 detik tetap

void loop() {
  unsigned long now = millis();
  
  if (now - lastSend >= sendInterval) {
    lastSend = now;
    
    // Generate dummy values dengan variasi realistis
    float soil = randomFloat(30, 75);    // Kelembapan tanah 30-75%
    float temp = randomFloat(26, 32);    // Suhu 26-32°C
    float hum  = randomFloat(60, 85);    // Kelembapan udara 60-85%
    float r    = randomFloat(50, 200);   // Red
    float g    = randomFloat(100, 255);  // Green (dominan hijau untuk tanaman)
    float b    = randomFloat(30, 150);   // Blue

    Serial.println("╔════════════════════════════════════╗");
    Serial.println("║     📊 DUMMY SENSOR READING       ║");
    Serial.println("╚════════════════════════════════════╝");
    Serial.printf("🌱 Soil Moisture : %.2f%%\n", soil);
    Serial.printf("🌡️  Temperature  : %.2f°C\n", temp);
    Serial.printf("💧 Humidity     : %.2f%%\n", hum);
    Serial.printf("🎨 RGB Color    : (%.0f, %.0f, %.0f)\n", r, g, b);
    Serial.println("────────────────────────────────────");

    bool success = sendSensorData(soil, temp, hum, r, g, b);
    
    if (!success) {
      Serial.println("⚠️ Failed to send data. Will retry in next cycle...");
    }
    
    Serial.println("\n⏳ Next transmission in 8 seconds...\n");
  }
  
  // Check WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("❌ WiFi disconnected! Reconnecting...");
    WiFi.begin(ssid, password);
    delay(5000);
    if (WiFi.status() != WL_CONNECTED) {
      Serial.println("❌ Reconnection failed. Restarting...");
      ESP.restart();
    }
  }
  
  delay(100); // Small delay to prevent watchdog issues
}
