<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Arduino / ESP32 ingest settings.
| Keep this key the same as API_KEY in arduino/daguitan_flood_monitor.ino
|
| Preferred ESP32 URL (no spaces):
|   http://YOUR_PC_LAN_IP/daguitan/ingest.php
*/
$config['monitor_api_key'] = 'daguitan-esp32-key';

/*
| Height from the ultrasonic sensor face down to the river bed (cm).
| Water level (m) = (sensor_height_cm - measured_distance_cm) / 100
*/
$config['monitor_sensor_height_cm'] = 400;

/* Seconds without a reading before the station is marked offline. */
$config['monitor_offline_after'] = 90;

/* Water-level thresholds in meters. */
$config['monitor_threshold_yellow_m'] = 1.50;
$config['monitor_threshold_red_m'] = 2.50;

$config['monitor_station'] = 'Daguitan Bridge Monitoring Station';
$config['monitor_location'] = 'Daguitan Bridge, Dulag, Leyte';
