/*
  DAGUITAN BRIDGE
  IoT Flood Real-Time Monitoring and Early Warning System

  ESP32 Field Monitoring Firmware

  Responsibilities:
  1. Read JSN-SR04T ultrasonic sensor
  2. Calculate distance
  3. Convert distance to water level
  4. Validate consecutive readings
  5. Connect to Wi-Fi
  6. Send validated readings to the monitoring platform
  7. Temporarily queue readings if the server is unavailable

  Backend responsibilities:
  - Store readings
  - Green/Yellow/Red classification
  - Rate of water-level rise
  - Water-level trend
  - ETA to threshold
  - Weather information
  - FCM notifications
  - SMS notifications
  - Warning speaker control
*/


// ======================================================
// LIBRARIES
// ======================================================

#include <WiFi.h>
#include <HTTPClient.h>


// ======================================================
// PIN CONFIGURATION
// ======================================================

const int TRIG_PIN = 5;   // ESP32 D5 / GPIO 5
const int ECHO_PIN = 18;  // ESP32 D18 / GPIO 18

/*
  IMPORTANT:
  JSN-SR04T ECHO must NOT be connected directly
  to the ESP32 GPIO if its output is 5V.

  Use the voltage divider:

  JSN ECHO
      |
     1kΩ
      |
      +-------- GPIO 18
      |
     2kΩ
      |
     GND
*/


// ======================================================
// WI-FI CONFIGURATION
// ======================================================

// Replace these with your actual Wi-Fi information.
// DO NOT share your Wi-Fi password with anyone.

const char* WIFI_SSID     = ".";
const char* WIFI_PASSWORD = "ExtendCutie";


// ======================================================
// SERVER CONFIGURATION
// ======================================================

// PC running XAMPP / CodeIgniter
const char* PC_IP = "192.168.0.101";

// Must match application/config/monitor.php
const char* API_KEY = "daguitan-esp32-key";


// ======================================================
// SERVER ENDPOINT
// ======================================================

String ingestUrl() {

  String url = "http://";
  url += PC_IP;
  url += "/daguitan/ingest.php";

  return url;
}


// ======================================================
// SENSOR CONFIGURATION
// ======================================================

// TEMPORARY DEVELOPMENT VALUE.
//
// This is NOT the final Daguitan Bridge reference height.
//
// It will be replaced after measuring/calibrating
// the prototype.
const float REFERENCE_HEIGHT_CM = 200.0;


// ======================================================
// READING VALIDATION
// ======================================================

// Maximum change considered a normal change.
//
// Larger changes require consecutive confirmation.
const float MAX_CHANGE_CM = 10.0;


// Number of consecutive similar readings required
// to confirm a sudden change.
const int REQUIRED_CONFIRMATIONS = 3;


// Maximum difference allowed between suspicious
// readings for them to be considered similar.
const float CONFIRMATION_TOLERANCE_CM = 2.0;


// ======================================================
// TRANSMISSION CONFIGURATION
// ======================================================

// Send a reading every 10 seconds.
const unsigned long POST_EVERY_MS = 10000;


// Retry Wi-Fi connection every 5 seconds.
const unsigned long WIFI_RETRY_MS = 5000;


// ======================================================
// LOCAL QUEUE
// ======================================================

// Maximum number of readings that can be temporarily
// stored in ESP32 memory while the server is unavailable.
const int QUEUE_MAX = 24;


// ======================================================
// QUEUED READING STRUCTURE
// ======================================================

struct QueuedReading {

  float distance;

  float waterLevel;

  char uid[32];
};


// ======================================================
// NETWORK VARIABLES
// ======================================================

WiFiClient wifiClient;


// Temporary local queue
QueuedReading queue[QUEUE_MAX];

int queueCount = 0;


// ======================================================
// VALIDATION VARIABLES
// ======================================================

// Latest accepted/validated water level
float previousValidatedLevel = -1.0;


// Temporary suspicious reading
float pendingLevel = -1.0;


// Number of consecutive readings supporting
// the suspicious/pending level
int confirmationCount = 0;


// ======================================================
// SETUP
// ======================================================

void setup() {

  Serial.begin(115200);

  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);

  // Make sure trigger starts LOW
  digitalWrite(TRIG_PIN, LOW);

  delay(1000);

  Serial.println();
  Serial.println("========================================");
  Serial.println(" DAGUITAN BRIDGE WATER MONITOR");
  Serial.println(" ESP32 FIELD MONITORING DEVICE");
  Serial.println("========================================");
  Serial.println("System starting...");
  Serial.println();

  connectWifi();
}


// ======================================================
// CONNECT TO WI-FI
// ======================================================

void connectWifi() {

  if (WiFi.status() == WL_CONNECTED) {

    return;
  }


  WiFi.mode(WIFI_STA);

  WiFi.setSleep(false);

  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);


  Serial.print("Connecting to Wi-Fi");


  unsigned long start = millis();


  while (
    WiFi.status() != WL_CONNECTED &&
    millis() - start < 20000
  ) {

    delay(400);

    Serial.print(".");
  }


  Serial.println();


  if (WiFi.status() == WL_CONNECTED) {

    Serial.println("Wi-Fi OK");

    Serial.print("ESP32 IP: ");
    Serial.println(WiFi.localIP());

    Serial.print("Server URL: ");
    Serial.println(ingestUrl());

  }

  else {

    Serial.println("Wi-Fi connection failed.");

    Serial.println(
      "Check SSID/password and network connectivity."
    );
  }
}


// ======================================================
// READ DISTANCE
// ======================================================

float readDistanceCM() {

  // Send trigger pulse

  digitalWrite(TRIG_PIN, LOW);

  delayMicroseconds(2);


  digitalWrite(TRIG_PIN, HIGH);

  delayMicroseconds(10);

  digitalWrite(TRIG_PIN, LOW);


  // Read echo pulse

  unsigned long duration =
    pulseIn(
      ECHO_PIN,
      HIGH,
      30000
    );


  // No echo received

  if (duration == 0) {

    return -1.0;
  }


  // Convert echo duration to distance

  float distance =
    duration * 0.0343 / 2.0;


  return distance;
}


// ======================================================
// CHECK IF TWO LEVELS ARE SIMILAR
// ======================================================

bool isSimilarLevel(
  float level1,
  float level2
) {

  float difference =
    abs(level1 - level2);


  return
    difference <= CONFIRMATION_TOLERANCE_CM;
}


// ======================================================
// PROCESS WATER-LEVEL VALIDATION
// ======================================================

bool processValidation(
  float currentLevel
) {

  // ----------------------------------------------------
  // FIRST READING
  // ----------------------------------------------------

  if (previousValidatedLevel < 0) {

    previousValidatedLevel =
      currentLevel;

    pendingLevel = -1.0;

    confirmationCount = 0;

    return true;
  }


  // ----------------------------------------------------
  // CALCULATE CHANGE FROM LAST ACCEPTED READING
  // ----------------------------------------------------

  float difference =
    abs(
      currentLevel -
      previousValidatedLevel
    );


  // ----------------------------------------------------
  // NORMAL CHANGE
  // ----------------------------------------------------

  if (difference <= MAX_CHANGE_CM) {

    previousValidatedLevel =
      currentLevel;

    pendingLevel = -1.0;

    confirmationCount = 0;

    return true;
  }


  // ----------------------------------------------------
  // SUDDEN CHANGE
  // ----------------------------------------------------

  if (pendingLevel < 0) {

    pendingLevel =
      currentLevel;

    confirmationCount = 1;

    return false;
  }


  // ----------------------------------------------------
  // CHECK SIMILARITY
  // ----------------------------------------------------

  if (
    isSimilarLevel(
      currentLevel,
      pendingLevel
    )
  ) {

    confirmationCount++;
  }

  else {

    pendingLevel =
      currentLevel;

    confirmationCount = 1;

    return false;
  }


  // ----------------------------------------------------
  // CONFIRM SUDDEN CHANGE
  // ----------------------------------------------------

  if (
    confirmationCount >=
    REQUIRED_CONFIRMATIONS
  ) {

    previousValidatedLevel =
      currentLevel;

    pendingLevel = -1.0;

    confirmationCount = 0;

    return true;
  }


  // Still waiting for confirmation

  return false;
}


// ======================================================
// GENERATE RECORD UID
// ======================================================

void makeUid(
  char* dest,
  size_t n
) {

  snprintf(
    dest,
    n,
    "esp-%04X-%lu",
    (unsigned)(
      ESP.getEfuseMac() & 0xFFFF
    ),
    (unsigned long)millis()
  );
}


// ======================================================
// POST READING TO SERVER
// ======================================================

bool postReading(
  float distanceCm,
  float waterLevelCm,
  const char* uid
) {

  // Make sure Wi-Fi is connected

  if (
    WiFi.status() != WL_CONNECTED
  ) {

    connectWifi();


    if (
      WiFi.status() != WL_CONNECTED
    ) {

      return false;
    }
  }


  HTTPClient http;


  String url =
    ingestUrl();


  if (
    !http.begin(
      wifiClient,
      url
    )
  ) {

    Serial.println(
      "HTTP begin failed"
    );

    return false;
  }


  http.setTimeout(8000);


  http.addHeader(
    "Content-Type",
    "application/x-www-form-urlencoded"
  );


  http.addHeader(
    "X-Api-Key",
    API_KEY
  );


  http.addHeader(
    "Connection",
    "close"
  );


  // ----------------------------------------------------
  // Build POST body
  // ----------------------------------------------------

  String body =
    "api_key=";

  body += API_KEY;


  body +=
    "&distance_cm=";

  body +=
    String(
      distanceCm,
      2
    );


  /*
    The backend expects sensor_height_cm
    and calculates water_level_m.

    Therefore we send the same reference height
    used by this ESP32.
  */

  body +=
    "&sensor_height_cm=";

  body +=
    String(
      REFERENCE_HEIGHT_CM,
      2
    );


  /*
    Send the validated water level directly.

    The current backend accepts water_level_m,
    so convert centimeters to meters.
  */

  body +=
    "&water_level_m=";

  body +=
    String(
      waterLevelCm / 100.0,
      3
    );


  if (
    uid != NULL &&
    uid[0] != '\0'
  ) {

    body +=
      "&record_uid=";

    body += uid;
  }


  // ----------------------------------------------------
  // Send POST request
  // ----------------------------------------------------

  int code =
    http.POST(body);


  String response =
    http.getString();


  http.end();


  // ----------------------------------------------------
  // Display server response
  // ----------------------------------------------------

  Serial.print("HTTP ");
  Serial.print(code);
  Serial.print(" ");

  Serial.println(response);


  // HTTP 2xx = success

  return
    code >= 200 &&
    code < 300;
}


// ======================================================
// ADD READING TO LOCAL QUEUE
// ======================================================

void enqueueReading(
  float distanceCm,
  float waterLevelCm
) {

  if (
    queueCount >= QUEUE_MAX
  ) {

    // Drop oldest unread packet

    for (
      int i = 1;
      i < QUEUE_MAX;
      i++
    ) {

      queue[i - 1] =
        queue[i];
    }


    queueCount =
      QUEUE_MAX - 1;


    Serial.println(
      "Local queue full - dropped oldest unread packet"
    );
  }


  makeUid(
    queue[queueCount].uid,
    sizeof(queue[queueCount].uid)
  );


  queue[queueCount].distance =
    distanceCm;


  queue[queueCount].waterLevel =
    waterLevelCm;


  queueCount++;


  Serial.print(
    "Queued locally ("
  );

  Serial.print(queueCount);

  Serial.println(
    " waiting)"
  );
}


// ======================================================
// SEND QUEUED READINGS
// ======================================================

void flushQueue() {

  while (
    queueCount > 0
  ) {

    if (
      !postReading(
        queue[0].distance,
        queue[0].waterLevel,
        queue[0].uid
      )
    ) {

      Serial.println(
        "Server unreachable - keeping remaining packets"
      );

      return;
    }


    // Remove successfully sent packet

    for (
      int i = 1;
      i < queueCount;
      i++
    ) {

      queue[i - 1] =
        queue[i];
    }


    queueCount--;
  }
}


// ======================================================
// MAIN LOOP
// ======================================================

void loop() {

  // ----------------------------------------------------
  // 1. READ SENSOR
  // ----------------------------------------------------

  float distance =
    readDistanceCM();


  // ----------------------------------------------------
  // 2. HANDLE SENSOR FAILURE
  // ----------------------------------------------------

  if (
    distance < 0
  ) {

    Serial.println();

    Serial.println(
      "No echo received."
    );

    Serial.println(
      "Reading status        : SENSOR ERROR"
    );

    Serial.println(
      "----------------------------------------"
    );


    delay(1000);

    return;
  }


  // ----------------------------------------------------
  // 3. CONVERT DISTANCE TO WATER LEVEL
  // ----------------------------------------------------

  float rawWaterLevel =
    REFERENCE_HEIGHT_CM -
    distance;


  // Prevent negative water level

  if (
    rawWaterLevel < 0
  ) {

    rawWaterLevel = 0;
  }


  // ----------------------------------------------------
  // 4. DISPLAY RAW MEASUREMENT
  // ----------------------------------------------------

  Serial.println();


  Serial.print(
    "Distance              : "
  );

  Serial.print(
    distance,
    2
  );

  Serial.println(
    " cm"
  );


  Serial.print(
    "Raw Water Level       : "
  );

  Serial.print(
    rawWaterLevel,
    2
  );

  Serial.println(
    " cm"
  );


  // ----------------------------------------------------
  // 5. PROCESS VALIDATION
  // ----------------------------------------------------

  bool validReading =
    processValidation(
      rawWaterLevel
    );


  // ----------------------------------------------------
  // 6. VALID READING
  // ----------------------------------------------------

  if (
    validReading
  ) {

    Serial.println(
      "Validation            : VALID"
    );


    Serial.print(
      "Validated Water Level : "
    );

    Serial.print(
      previousValidatedLevel,
      2
    );

    Serial.println(
      " cm"
    );


    Serial.println(
      "Reading Status        : ACCEPTED"
    );


    // --------------------------------------------------
    // Add validated reading to transmission queue
    // --------------------------------------------------

    enqueueReading(
      distance,
      previousValidatedLevel
    );


    // --------------------------------------------------
    // Try sending to server
    // --------------------------------------------------

    flushQueue();
  }


  // ----------------------------------------------------
  // 7. PENDING / ABNORMAL READING
  // ----------------------------------------------------

  else {

    Serial.println(
      "Validation            : PENDING"
    );


    Serial.print(
      "Validated Water Level : "
    );

    Serial.print(
      previousValidatedLevel,
      2
    );

    Serial.println(
      " cm"
    );


    Serial.print(
      "Pending Water Level   : "
    );

    Serial.print(
      pendingLevel,
      2
    );

    Serial.println(
      " cm"
    );


    Serial.print(
      "Confirmation          : "
    );

    Serial.print(
      confirmationCount
    );

    Serial.print(
      " / "
    );

    Serial.println(
      REQUIRED_CONFIRMATIONS
    );


    Serial.println(
      "Reading Status        : TEMPORARILY REJECTED"
    );


    Serial.println(
      "Waiting for consistent readings..."
    );
  }


  // ----------------------------------------------------
  // 8. SEPARATOR
  // ----------------------------------------------------

  Serial.println(
    "----------------------------------------"
  );


  // ----------------------------------------------------
  // 9. WAIT
  // ----------------------------------------------------

  delay(
    POST_EVERY_MS
  );
}