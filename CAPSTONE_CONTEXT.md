# CAPSTONE CONTEXT — ONE DULAG_MDRRMO

## Project Identity

**Title:** IoT-Based Flood Real-Time Monitoring and Early Warning System for Daguitan Bridge, Dulag, Leyte  
**Program:** BSIT  
**Project Type:** Working miniature/prototype; not a full-scale permanent deployment  
**Primary Stakeholder:** MDRRMO Dulag  
**Development Methodology:** Agile SDLC

This file is the authoritative project context for coding work in this repository. Read it before making architectural, database, hardware-integration, warning-logic, or UI changes.

---

## 1. Core Purpose and Scope

The system is a localized IoT river water-level monitoring and early warning system for Daguitan Bridge, Dulag, Leyte.

The system continuously monitors **river water level** using an ultrasonic sensor and provides warning information to MDRRMO personnel and residents.

### Critical Scope Rule

The system's actual physical environmental measurement is **ONLY river water level**.

Weather information is obtained from an API and is **supplementary only**.

Weather data MUST NOT determine the Green/Yellow/Red flood warning classification.

Do not silently expand the system into an AI flood prediction system, rainfall monitoring system, flow-velocity monitoring system, regional flood forecasting system, or satellite flood-mapping system.

---

## 2. Hardware

- ESP32 DevKit V1 (ESP-WROOM-32) — main microcontroller
- JSN-SR04T V3.0 waterproof ultrasonic sensor — river water-level measurement
- Solar panel — primary power source
- Rechargeable battery — energy storage/backup
- TP4056 charging module
- MT3608 boost converter
- Relay module
- Community warning speaker
- Waterproof enclosure
- Supporting wiring, connectors, and mounting materials

### Power

The monitoring station is designed as a **solar-powered system with rechargeable battery backup**.

Do not describe it as dependent on conventional AC/grid power unless the project design is explicitly changed.

---

## 3. Sensor Wiring / Current Prototype

Current ESP32 sensor pins:

- GPIO/D5 → JSN-SR04T TRIG
- GPIO/D18 → JSN-SR04T ECHO through a voltage divider
- VIN/5V → sensor VCC
- GND → sensor GND

Voltage divider:

- JSN-SR04T ECHO → 1kΩ → junction → ESP32 D18
- junction → 2kΩ → GND

Do NOT directly connect the 5V ECHO signal to the ESP32 GPIO.

The JSN-SR04T should have sufficient time between measurements; the sensor manual recommends at least approximately 60 ms between measurements.

---

## 4. Software / Technology Stack

- Visual Studio Code 1.103
- CodeIgniter 3.1.13
- PHP 8.2.12
- MySQL Community Server 8.0
- XAMPP 8.2.12
- Arduino IDE 2.3.6
- Windows 11 24H2
- Git
- Google Chrome 139+
- Progressive Web Application (PWA)
- Firebase Cloud Messaging (FCM)
- OpenWeather One Call API 3.0
- SMS add-on
- Hybrid local + cloud database architecture

Do not invent an SMS provider/gateway unless one is explicitly selected.

---

## 5. Main System Flow

1. JSN-SR04T measures distance to the river surface.
2. The system converts distance into river water level.
3. Consecutive readings are validated to reduce abnormal/spike readings.
4. ESP32 processes the measurement.
5. Monitoring data is transmitted to the monitoring platform.
6. Data is stored in the local MySQL database.
7. Local data synchronizes with the cloud database when connectivity is available/restored.
8. Weather API provides supplementary weather information.
9. The system computes:
   - Rate of Water-Level Rise
   - Water-Level Trend
   - Estimated Time-to-Threshold (ETA)
10. Validated river water level is compared with predefined thresholds.
11. Flood condition is classified as Green, Yellow, or Red.
12. Warnings/information are disseminated through:
   - MDRRMO web dashboard
   - Resident PWA
   - FCM push notifications
   - SMS notifications
   - Community warning speaker when applicable

### Warning Logic — Critical

**ONLY validated river water level thresholds determine Green/Yellow/Red classification.**

The following MUST NOT determine warning classification:

- Weather API information
- Rainfall
- Rate of water-level rise
- Water-level trend
- ETA

These are supplementary/analytical information.

---

## 6. Analytical Features

### Rate of Water-Level Rise

The intended specification calculates rate using consecutive water-level readings over a **5-minute interval**.

Example:

- Reading 1 = 120 cm
- Reading 2 = 135 cm
- Time interval = 5 minutes

Rate:

`(135 - 120) / 5 = 3 cm/min`

Current prototype/backend implementation may temporarily use a shorter comparison interval while testing. Do not change it automatically without checking the current implementation and project decision.

### Water-Level Trend

Trend categories:

- Rising
- Stable/Steady
- Falling

Trend is analytical information only and does not determine Green/Yellow/Red classification.

### Estimated Time-to-Threshold (ETA)

ETA estimates how long it may take for the current water level to reach the next predefined flood threshold, assuming the observed rate remains relatively constant.

ETA is **supplementary information only**.

If the water level is not rising, ETA may appropriately display something such as:

`Not rising`

or another explicit non-applicable state.

Do not interpret ETA as a flood prediction guarantee.

---

## 7. Warning Classification

Warning classification uses predefined river water-level thresholds:

- Green
- Yellow
- Red

Threshold values will be based on existing Daguitan Bridge water-level markings and finalized through consultation with MDRRMO.

The prototype may use proportional conversion of actual bridge measurements to prototype scale.

### Current Development Configuration

Current development configuration has used placeholder values:

```php
monitor_sensor_height_cm = 400
monitor_threshold_yellow_m = 1.50
monitor_threshold_red_m = 2.50
```

The current ESP32 prototype testing sketch has also used:

```cpp
REFERENCE_HEIGHT_CM = 200.0
```

These are **development/testing values**, not final field threshold values.

Do not treat placeholder values as final official thresholds.

---

## 8. Database Architecture

The project uses a hybrid local + cloud database architecture.

### Local MySQL Database

Used for:

- continuous local recording
- offline buffering
- river water-level readings
- weather information
- warning records
- historical records
- relevant system/user information

### Cloud Database

Used for:

- centralized records
- remote access
- synchronization
- backup/data availability

When connectivity is interrupted:

- local recording should continue
- records should be synchronized after connectivity is restored

---

## 9. Existing Database Table

The existing `water_readings` table is already used by the system.

Known fields include:

- `record_uid`
- `water_level_m`
- `distance_cm`
- `sensor_height_cm`
- `received_at`
- `sync_status`
- `synced_at`
- `sync_error`
- `created_at`

Do NOT create a second/replacement water-reading table unless explicitly requested.

Existing historical/test records may contain different sensor-height development values. Do not delete or rewrite them merely to make the data look uniform unless explicitly instructed.

---

## 10. Users

### MDRRMO Administrator

Uses the web dashboard to view:

- real-time water level
- warning status
- historical records
- analytical information
- weather information
- sensor/device status
- alerts
- reports
- resident information

### Residents

Use the PWA to receive/view:

- river water level
- flood warning status
- water-level trend
- ETA
- supplementary weather information
- emergency announcements
- push notifications
- SMS notifications where applicable

---

## 11. Warning / Dissemination Channels

The system uses multiple dissemination channels:

1. MDRRMO Web Dashboard
2. Resident PWA
3. Firebase Cloud Messaging (FCM) push notifications
4. SMS notifications for registered recipients
5. Community warning speaker

### SMS Rule

SMS is an **additional communication channel**, not a replacement for FCM.

The intended logical flow is:

`Flood classification → SMS notification service → registered recipient mobile numbers → SMS warning`

Do not specify a particular SMS provider until one is selected.

---

## 12. Existing Project Structure

Main repository:

```text
ONE DULAG_MDRRMO/
├── application/
│   ├── config/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   └── ...
├── arduino/
├── assets/
├── daguitan/
├── database/
├── system/
├── tools/
├── index.php
└── ...
```

Known controllers:

- `Admin.php`
- `Api.php`
- `Auth.php`
- `Portal.php`
- `Welcome.php`

Known models:

- `Auth_model.php`
- `Monitor_model.php`
- `Sync_model.php`

---

## 13. Current API Architecture

### `application/controllers/Api.php`

Current API responsibilities include:

- CORS handling
- `status()` → obtains monitoring status from `Monitor_model`
- `sync()` → handles synchronization through `Sync_model`
- `ingest()` → accepts ESP32 POST/PUT data and passes it to `Monitor_model->ingest()`

Supported ingest input includes:

- `api_key`
- `water_level_m`
- `distance_cm`
- `sensor_height_cm`
- `record_uid`

The backend should remain responsible for server-side storage and monitoring calculations.

---

## 14. ESP32 → PHP Ingest Proxy

Current proxy:

```text
daguitan/ingest.php
```

It forwards ESP32 data to:

```text
http://127.0.0.1/ONE%20DULAG_MDRRMO/index.php/api/ingest
```

The ESP32 currently uses the PC/LAN proxy URL:

```text
http://192.168.0.101/daguitan/ingest.php
```

Current development network:

- PC: `192.168.0.101`
- ESP32: `192.168.0.102`

These addresses are development-network values and may change.

---

## 15. Current API Key / Development Configuration

Current API key:

```text
daguitan-esp32-key
```

Do not expose or change credentials unnecessarily.

---

## 16. Current ESP32 Integration

The preferred prototype sketch is:

```text
arduino/daguitan_sensor_monitor.ino
```

The current combined version includes:

- Wi-Fi connection
- JSN-SR04T reading
- consecutive-reading validation
- local queue/retry behavior
- HTTP POST to the PHP ingest proxy
- validated water-level transmission
- record UID generation

Current development constants include:

```cpp
const char* PC_IP = "192.168.0.101";
const char* API_KEY = "daguitan-esp32-key";
const float REFERENCE_HEIGHT_CM = 200.0;
const unsigned long POST_EVERY_MS = 10000;
const int QUEUE_MAX = 24;
```

The current sensor validation logic includes concepts such as:

- maximum allowed change
- required confirmations
- confirmation tolerance
- previous validated level
- pending level
- confirmation count

The backend should continue to be treated as the authority for warning classification.

---

## 17. Current ESP32 Test Status

The ESP32 prototype has successfully:

- connected to Wi-Fi
- obtained IP `192.168.0.102`
- read the JSN-SR04T
- produced readings around 65–66 cm during testing
- converted those readings using the 200 cm development reference
- produced water levels around 1.34 m
- validated readings
- received HTTP 200 responses
- stored records locally
- synchronized records to the cloud

The database has contained recent records around:

- water level: approximately 1.339–1.343 m
- distance: approximately 65.70–66.11 cm
- sensor height: 200 cm
- sync status: synced

These are test observations, not final field measurements.

---

## 18. Current Backend Monitoring Logic

`Monitor_model.php` currently handles:

- latest reading retrieval
- history retrieval
- trend calculation
- warning classification
- online/offline state
- weather information
- ingest/storage
- cloud synchronization

Current warning logic is conceptually:

```text
if water_level >= red threshold:
    Red / Critical
else if water_level >= yellow threshold:
    Yellow / Monitor
else:
    Green / Safe
```

This classification must remain based on water level only.

### Current Trend Calculation During Prototype Testing

The current implementation has used a comparison based on at least approximately 60 seconds when possible.

Conceptually:

```text
rate = change in water level (cm) / elapsed time (minutes)
```

Current trend boundaries have been:

- rate > 0.03 cm/min → Rising
- rate < -0.03 cm/min → Falling
- otherwise → Steady

The project specification ultimately expects rate-of-rise analysis over a 5-minute interval. If changing the implementation toward the final specification, check the existing code and test behavior before making the change.

---

## 19. ETA Feature Status

ETA has now been added to the website/dashboard and is currently being tested.

During the latest test, the dashboard displayed:

```text
Not rising
```

This means the current ETA implementation is treating the observed rate as not sufficiently positive for ETA calculation.

Do not automatically assume this is a bug. First inspect:

- current Rate of Rise value
- current trend
- current water level
- next threshold
- ETA calculation condition

Then determine whether the result is consistent with the implementation.

### ETA Safety Rule

ETA is an estimate based on the current observed rate. It must not:

- change Green/Yellow/Red classification
- be described as guaranteed flood prediction
- replace MDRRMO judgment
- use weather data as a classification input

---

## 20. Weather API

The project uses:

```text
OpenWeather One Call API 3.0
```

Weather information is supplementary.

Weather values must not be used as a condition for Green/Yellow/Red warning classification.

Do not add rainfall as a physical sensor unless the hardware scope is explicitly changed.

---

## 21. Project Limitations

Do NOT claim that the system:

- predicts floods using AI unless explicitly added
- measures rainfall using a physical sensor
- measures water pressure
- measures water flow velocity
- replaces official PAGASA forecasts
- replaces MDRRMO decision-making
- performs full regional flood forecasting
- provides satellite-based flood mapping
- guarantees flood prediction
- automatically determines disasters from weather data

The system is primarily a localized **real-time river water-level monitoring and early warning system**.

---

## 22. Code Change Rules

Before changing code:

1. Read the relevant existing file.
2. Preserve the current architecture unless a change is explicitly requested.
3. Do not create duplicate database tables or parallel implementations without a clear reason.
4. Do not silently change warning thresholds.
5. Do not make weather, rainfall, rate, trend, or ETA determine warning classification.
6. Keep sensor measurement separate from API weather data.
7. Keep analytical values separate from actual sensor measurements.
8. Maintain local offline recording and synchronization behavior.
9. Preserve the solar-powered design in documentation/UI wording.
10. Do not invent hardware, services, providers, APIs, or system features.
11. When adding a feature, check all affected layers:
    - database
    - model
    - controller/API
    - view/dashboard
    - PWA
    - notifications
    - documentation
    - testing
12. Prefer minimal, targeted changes over unnecessary rewrites.
13. Before modifying a file, explain what will change if the change could affect system behavior.
14. After changes, test the affected behavior before moving to the next feature.

---

## 23. Important Consistency Rule for Academic Documentation

The following must remain consistent across Chapters 1–3 and system implementation:

- Title
- Scope and Delimitations
- Review of Related Literature
- Review of Related Studies
- Conceptual Framework
- Definition of Terms
- Materials
- Hardware Specifications
- Software Specifications
- Agile SDLC phases
- Context Diagram
- DFD
- Database design
- System architecture
- Monitoring process
- Testing
- Deployment

If a feature such as SMS or ETA is added, identify all relevant sections that need updating instead of modifying only one paragraph.

---

## 24. Working Style for This Repository

The project owner prefers a **one-step-at-a-time** development process.

When guiding implementation:

- Give exactly one immediate next task.
- Wait for the result before proceeding.
- Do not provide a long sequence of unrelated steps unless explicitly requested.
- When debugging, ask for the exact output/error before changing multiple things.
- When code is requested, provide complete code for the relevant file rather than incomplete fragments.
- Explain technical concepts simply because the project owner is still learning the implementation details.

---

## 25. Current Development Focus

The current focus is testing the newly added **ETA function on the website/dashboard**.

The dashboard currently reports:

```text
ETA: Not rising
```

The next diagnostic information to inspect is the dashboard's current **Rate of Rise** value.

Do not modify the ETA implementation until the current rate/trend output and ETA calculation condition have been inspected.

---

## 26. Guiding Principle

This repository is a prototype for localized river water-level monitoring and early warning.

When uncertain, preserve the established scope and ask for clarification rather than inventing functionality.

**Water level is the primary measured environmental variable.**

**Water level thresholds determine warning classification.**

**Weather, rate, trend, and ETA are supplementary information.**
