/*
 * ESP8266 - Clear EEPROM
 * Upload this to clear stored credentials
 */

#include <EEPROM.h>

void setup() {
  Serial.begin(115200);
  delay(100);
  
  Serial.println("\n╔════════════════════════════════════╗");
  Serial.println("║   🧹 CLEARING EEPROM...           ║");
  Serial.println("╚════════════════════════════════════╝");
  
  EEPROM.begin(512);
  
  // Clear all 512 bytes
  for (int i = 0; i < 512; i++) {
    EEPROM.write(i, 0);
  }
  
  EEPROM.commit();
  EEPROM.end();
  
  Serial.println("\n✅ EEPROM cleared successfully!");
  Serial.println("📝 All stored credentials erased.");
  Serial.println("\n🔄 Now upload your main firmware with new provisioning token.");
  Serial.println("\n⚠️ Device will restart in 5 seconds...");
  
  delay(5000);
  ESP.restart();
}

void loop() {
  // Nothing to do
}
