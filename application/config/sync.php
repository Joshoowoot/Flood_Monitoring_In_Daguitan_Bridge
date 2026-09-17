<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Hybrid storage: local MySQL is always the source of truth.
| When the internet is reachable, pending rows are copied to the cloud database.
|
| The default cloud target is a second MySQL schema on this XAMPP server
| (MDRRMO_DULAG_CLOUD). Point hostname at a remote MySQL host in production.
*/
$config['sync_enabled'] = TRUE;
/* Set TRUE only to simulate no internet without unplugging the cable. */
$config['sync_force_offline'] = FALSE;
$config['sync_probe_host'] = '8.8.8.8';
$config['sync_probe_port'] = 53;
$config['sync_probe_timeout'] = 1.5;
$config['sync_batch_size'] = 40;
$config['sync_probe_ttl'] = 20;
