/*
  Daguitan Flood Monitor — ESP32 + HC-SR04
  Posts river distance to the XAMPP / CodeIgniter website.

  Wiring (ESP32):
    HC-SR04 VCC  -> 5V (or 3.3V if your module supports it)
    HC-SR04 GND  -> GND
    HC-SR04 TRIG -> GPIO 5
    HC-SR04 ECHO -> GPIO 18  (use a voltage divider if ECHO is 5V)

  Setup before upload:
    1. Set WIFI_SSID and WIFI_PASSWORD (same Wi-Fi as your PC).
    2. Set PC_IP to your computer's LAN IP (ipconfig -> IPv4 Address).
       Do NOT use localhost or 127.0.0.1 — the ESP32 is a different device.
    3. Keep API_KEY equal to monitor_api_key in application/config/monitor.php
    4. Set SENSOR_HEIGHT_CM to the height from sensor face down to river bed.

  Uses the space-free proxy:
    http://PC_IP/daguitan/ingest.php
  so ESP32 does not have to POST into a folder name with a space.
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClient.h>

// --- edit these ---
const char* WIFI_SSID     = "YOUR_WIFI_NAME";
const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD";
const char* PC_IP         = "10.114.181.74";   // your PC LAN IP
const char* API_KEY       = "daguitan-esp32-key";
const float SENSOR_HEIGHT_CM = 400.0;          // sensor face -> river bed
// ------------------

const int TRIG_PIN = 5;
const int ECHO_PIN = 18;

const unsigned long POST_EVERY_MS = 10000;
const unsigned long WIFI_RETRY_MS = 5000;
const int SAMPLE_COUNT = 5;

WiFiClient wifiClient;

String ingestUrl() {
  String url = "http://";
  url += PC_IP;
  url += "/daguitan/ingest.php";
  return url;
}

void connectWifi() {
  if (WiFi.status() == WL_CONNECTED) {
    return;
  }

  WiFi.mode(WIFI_STA);
  WiFi.setSleep(false);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  Serial.print("Connecting to Wi-Fi");

  unsigned long start = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - start < 20000) {
    delay(400);
    Serial.print(".");
  }
  Serial.println();

  if (WiFi.status() == WL_CONNECTED) {
    Serial.print("Wi-Fi OK  IP=");
    Serial.println(WiFi.localIP());
    Serial.print("Ingest URL: ");
    Serial.println(ingestUrl());
  } else {
    Serial.println("Wi-Fi failed — check SSID/password and that phone hotspot/router allows clients to talk to each other.");
  }
}

void setup() {
  Serial.begin(115200);
  delay(500);
  Serial.println();
  Serial.println("Daguitan Flood Monitor boot");

  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  digitalWrite(TRIG_PIN, LOW);

  connectWifi();
}

float readDistanceCm() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(3);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);

  unsigned long duration = pulseIn(ECHO_PIN, HIGH, 30000UL);
  if (duration == 0) {
    return -1;
  }
  return (duration * 0.0343f) / 2.0f;
}

float medianDistanceCm() {
  float samples[SAMPLE_COUNT];
  int valid = 0;

  for (int i = 0; i < SAMPLE_COUNT; i++) {
    float d = readDistanceCm();
    if (d > 2 && d < 400) {
      samples[valid++] = d;
    }
    delay(60);
  }

  if (valid == 0) {
    return -1;
  }

  for (int i = 0; i < valid - 1; i++) {
    for (int j = i + 1; j < valid; j++) {
      if (samples[j] < samples[i]) {
        float tmp = samples[i];
        samples[i] = samples[j];
        samples[j] = tmp;
      }
    }
  }

  return samples[valid / 2];
}

bool postReading(float distanceCm) {
  if (WiFi.status() != WL_CONNECTED) {
    connectWifi();
    if (WiFi.status() != WL_CONNECTED) {
      return false;
    }
  }

  HTTPClient http;
  String url = ingestUrl();

  if (!http.begin(wifiClient, url)) {
    Serial.println("HTTP begin failed");
    return false;
  }

  http.setTimeout(12000);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  http.addHeader("X-Api-Key", API_KEY);
  http.addHeader("Connection", "close");

  String body = "api_key=";
  body += API_KEY;
  body += "&distance_cm=";
  body += String(distanceCm, 1);
  body += "&sensor_height_cm=";
  body += String(SENSOR_HEIGHT_CM, 1);

  int code = http.POST(body);
  String response = http.getString();
  http.end();

  Serial.print("HTTP ");
  Serial.print(code);
  Serial.print(" ");
  Serial.println(response);

  return code >= 200 && code < 300;
}

void loop() {
  float distance = medianDistanceCm();
  if (distance < 0) {
    Serial.println("Sensor read failed — check TRIG/ECHO wiring and 5V power");
  } else {
    float levelM = (SENSOR_HEIGHT_CM - distance) / 100.0f;
    if (levelM < 0) {
      levelM = 0;
    }

    Serial.print("Distance cm: ");
    Serial.print(distance, 1);
    Serial.print("  -> water_level_m: ");
    Serial.println(levelM, 2);

    if (!postReading(distance)) {
      Serial.println("Post failed — will retry next cycle");
      delay(WIFI_RETRY_MS);
    }
  }

  delay(POST_EVERY_MS);
}
