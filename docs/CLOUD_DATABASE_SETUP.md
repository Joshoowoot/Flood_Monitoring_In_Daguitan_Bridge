# Google Cloud SQL setup

The application keeps local MySQL as its source of truth. Google Cloud SQL for MySQL receives queued resident-account changes and water readings; it is not the database that serves page requests. The cloud copy is intentionally one-way.

For a Windows/XAMPP deployment, use the [Cloud SQL Auth Proxy](https://cloud.google.com/sql/docs/mysql/connect-auth-proxy). It authenticates the application server to Cloud SQL and encrypts the network connection, while the PHP app connects to a loopback port. Do not expose the Cloud SQL instance to the public internet for this setup.

## 1. Create the Cloud SQL instance

Create a Cloud SQL for MySQL instance in a region near the application server. Enable automated backups and point-in-time recovery. Record the instance connection name in `PROJECT_ID:REGION:INSTANCE_ID`, and enable the Cloud SQL Admin API for the project.

Create a database/schema for the cloud copy. Run [`../database/hybrid_sync.sql`](../database/hybrid_sync.sql) with a database administrator account to create the `users` and `water_readings` tables. If the instance or provider console already created the schema, select it and run the two `CREATE TABLE` statements instead of the `CREATE DATABASE` and `USE` statements.

The application checks that all required tables and columns exist before hosted sync. When `FLOOD_CLOUD_DB_HOST` is explicitly set, it does not create or alter schemas—even if the host is `127.0.0.1` for the Auth Proxy.

## 2. Run the Cloud SQL Auth Proxy on the application server

Install the current Cloud SQL Auth Proxy v2 for Windows on the same machine as XAMPP. Give the proxy's Google identity the **Cloud SQL Client** IAM role. Prefer an attached service identity where available; otherwise, store a service-account key outside the web root with restrictive file permissions and configure `GOOGLE_APPLICATION_CREDENTIALS` for the proxy process.

Run the proxy as a managed, long-running Windows service (or a Task Scheduler task configured to start at boot and restart on failure). Bind it only to loopback:

```text
cloud-sql-proxy.exe --address 127.0.0.1 --port 3307 PROJECT_ID:REGION:INSTANCE_ID
```

Wait until the proxy reports that it is ready. Keep the proxy's identity and credentials on the server; they are not ESP32 credentials and must never be placed in browser code or firmware.

## 3. Create a restricted MySQL application user

Create a dedicated MySQL user in Cloud SQL for the application. Grant it only `SELECT`, `INSERT`, `UPDATE`, and `DELETE` on the cloud copy's `users` and `water_readings` tables. The application needs `DELETE` to propagate queued resident deletions. The sync user does not need `CREATE`, `ALTER`, or instance-admin privileges because the schema is provisioned separately.

The Auth Proxy handles the encrypted connection to Google. The app still uses the database username and password for MySQL authentication; the proxy's IAM identity does not replace those credentials.

## 4. Configure PHP and the scheduled task

Set these environment variables for both the Apache/PHP process and any scheduled PHP CLI task:

| Variable | Value |
| --- | --- |
| `FLOOD_CLOUD_DB_HOST` | `127.0.0.1` |
| `FLOOD_CLOUD_DB_PORT` | `3307` |
| `FLOOD_CLOUD_DB_NAME` | Cloud SQL database/schema name |
| `FLOOD_CLOUD_DB_USERNAME` | Restricted MySQL application username |
| `FLOOD_CLOUD_DB_PASSWORD` | Password for that application user |

Set `GOOGLE_APPLICATION_CREDENTIALS` for the proxy process only if it uses a service-account key; keep the key outside the web root. When using the Auth Proxy, leave `FLOOD_CLOUD_DB_SSL_CA` unset: the proxy secures and verifies its upstream Cloud SQL connection, and the PHP connection is only to loopback. For a direct remote MySQL connection without the proxy, the application requires TLS certificate verification; set `FLOOD_CLOUD_DB_SSL_CA` to the provider CA file and restrict Cloud SQL authorized networks to the application server's fixed public IP. The Auth Proxy is preferred.

Do not put database passwords, service-account keys, or private keys in source files, SQL checked into the repository, or publicly served folders. Configure environment values separately for Apache and the scheduled CLI process; the two processes may not inherit the same environment automatically.

## 5. Verify connectivity and sync

From the project root, run the sync job using the same PHP installation and environment as the application:

```text
php index.php sync_job run
```

The command exits successfully only when the sync run succeeds. It validates the hosted schema before attempting to copy data. Check the CodeIgniter application log if the command reports that Cloud SQL or its schema is unavailable; do not treat the cloud copy as working until the sync command succeeds.

To verify data movement, create or receive a test water reading, run the job, and confirm that its `record_uid` appears in Cloud SQL's `water_readings` table and that the local pending queue decreases. Run the job again and confirm that the cloud row is not duplicated. Test outage/retry behavior against staging: local writes should continue while Cloud SQL is unavailable, and a later successful sync should copy pending rows.

The admin dashboard's **Copy pending to cloud** action also runs a manual sync. “Internet online” only indicates the network probe succeeded; the sync result verifies the database connection, schema, and write permissions.

## 6. Keep the queue draining

Use Windows Task Scheduler to run `php index.php sync_job run` periodically, with the project root as the working directory and the same database and Google identity environment configured for PHP and the proxy. Configure retries on failure. This drains pending or failed rows even when no new sensor packet or user update triggers a sync.

## Scope and recovery

This integration mirrors only `users` and `water_readings` (and applies queued resident deletions). It does not back up announcements, reports, notifications, login history, uploaded files, or application configuration. Cloud SQL automated backups protect the configured Cloud SQL instance; they do not back up the separate local database. Configure and test a full-system backup/recovery plan for both sides.
