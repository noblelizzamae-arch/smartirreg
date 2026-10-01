#define BLYNK_TEMPLATE_ID "TMPL6Kk5vZ08v"
#define BLYNK_TEMPLATE_NAME "final"
#define BLYNK_AUTH_TOKEN "hpcSzkZatVN-rv6rB8iVBJsFci7NQ6t7"

#define BLYNK_PRINT Serial
#include <WiFi.h>
#include <WiFiClient.h>
#include <BlynkSimpleEsp32.h>
#include <DHT.h>

char auth[] = "hpcSzkZatVN-rv6rB8iVBJsFci7NQ6t7";
char ssid[] = "NCL";
char pass[] = "Nonong-1963";

#define DHTPIN 4
#define DHTTYPE DHT22
DHT dht(DHTPIN, DHTTYPE);

const int soilMoisturePin = 34;
const int pumpRelayPin = 25;
const int mistRelayPin = 26;

// Threshold settings
const int soilDryThreshold = 40;
const float tempHighThreshold = 30.0;
const float humidityLowThreshold = 60.0;

BlynkTimer timer;

void sendSensorData() {
  float h = dht.readHumidity();
  float t = dht.readTemperature();
  int rawSoil = analogRead(soilMoisturePin);
  int soilMoisturePct = map(rawSoil, 4095, 1400, 0, 100);
  soilMoisturePct = constrain(soilMoisturePct, 0, 100);

  if (isnan(h) || isnan(t)) {
    Serial.println("Failed to read from DHT sensor!");
    return;
  }

  // Automatic Irrigation Control (Soil Moisture)
  if (soilMoisturePct < soilDryThreshold) {
    digitalWrite(pumpRelayPin, HIGH);
    Blynk.virtualWrite(V2, 1);
  } else {
    digitalWrite(pumpRelayPin, LOW);
    Blynk.virtualWrite(V2, 0);
  }

  // Automatic Misting Control (Temperature & Air Humidity)
  if (t > tempHighThreshold || h < humidityLowThreshold) {
    digitalWrite(mistRelayPin, HIGH);
    Blynk.virtualWrite(V3, 1);
  } else {
    digitalWrite(mistRelayPin, LOW);
    Blynk.virtualWrite(V3, 0);
  }

  // Push live telemetry to Blynk Cloud Virtual Pins
  Blynk.virtualWrite(V0, t);
  Blynk.virtualWrite(V1, h);
  Blynk.virtualWrite(V4, soilMoisturePct);
}

void setup() {
  Serial.begin(115200);
  pinMode(pumpRelayPin, OUTPUT);
  pinMode(mistRelayPin, OUTPUT);

  digitalWrite(pumpRelayPin, HIGH);
  digitalWrite(mistRelayPin, HIGH);

  dht.begin();
  Blynk.begin(auth, ssid, pass);
  timer.setInterval(2000L, sendSensorData);
}

void loop() {
  Blynk.run();
  timer.run();
}
