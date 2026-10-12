# Web push setup

The in-app notification feed works without browser permission. Browser push requires Composer dependencies, HTTPS (localhost is allowed for development), a persistent VAPID key pair, and an opt-in on each browser/device.

When the portal is open, new notifications play a short alert tone; use the Sound control in the notification panel to test or mute it. When the page is closed, the browser displays a system notification and requests vibration on devices that support it. The notification sound itself is controlled by the browser, operating system, and device notification settings.

Install the production dependencies from the project root:

```text
composer install --no-dev --no-scripts
```

Generate one VAPID key pair and keep it stable. The following PHP command writes the keys to the ignored local config file without printing the private key:

```powershell
$env:OPENSSL_CONF = 'C:\xampp\php\extras\ssl\openssl.cnf'
@'
<?php
require 'vendor/autoload.php';
$keys = \Minishlink\WebPush\VAPID::createVapidKeys();
$config = array(
    'public_key' => $keys['publicKey'],
    'private_key' => $keys['privateKey'],
    'subject' => 'mailto:admin@example.com',
);
file_put_contents('application/config/web_push_keys.php', "<?php\nreturn " . var_export($config, TRUE) . ";\n");
'@ | & 'C:\xampp\php\php.exe'
```

Replace `admin@example.com` with a monitored contact address. Alternatively, set `DAGUITAN_VAPID_PUBLIC_KEY`, `DAGUITAN_VAPID_PRIVATE_KEY`, and `DAGUITAN_VAPID_SUBJECT` in the server environment. Do not commit or regenerate the private key: existing browser subscriptions are tied to the public key.

Apply `database/migrate_notifications.sql` when deploying the database; the application also creates the push-subscription table automatically. Residents and admins must enable push separately on each browser from the Notifications panel. If browser permission was denied earlier, allow notifications in the browser's site settings, then reload and opt in.
