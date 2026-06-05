# Configuration

This folder holds the application's credentials and is intentionally **not**
included in the source bundle for security reasons. Before running the site,
create the two files described below.

## 1. `config/config.php`

```php
<?php
// Stripe (https://dashboard.stripe.com/apikeys)
define('STRIPE_PUBLISHABLE_KEY', 'pk_live_xxxxxxxxxxxx');
define('STRIPE_SECRET_KEY',      'sk_live_xxxxxxxxxxxx');

// Google Maps / Routes API (https://console.cloud.google.com)
define('GOOGLE_MAPS_API_KEY', 'xxxxxxxxxxxx');

// Primary email (SMTP)
define('SMTP_HOST',       'smtp.yourprovider.com');
define('SMTP_PORT',       465);
define('SMTP_USERNAME',   'info@yourdomain.com');
define('SMTP_PASSWORD',   'your-smtp-password');
define('SMTP_ENCRYPTION', 'ssl'); // ssl for 465, tls for 587

// Company info
define('COMPANY_NAME',    'Direct Car Service');
define('COMPANY_EMAIL',   'info@yourdomain.com');
define('DISPATCH_EMAIL',  'info@yourdomain.com');
define('COMPANY_PHONE_1', '000-000-0000');
define('COMPANY_PHONE_2', '000-000-0000');

// Admin panel password (used by /admin)
define('ADMIN_PASSWORD', 'choose-a-strong-password');

// App settings
define('DEBUG_MODE', false);          // keep false in production
define('SITE_URL',   'https://yourdomain.com');
define('BOOKING_LOG_PATH', __DIR__ . '/../bookings/');

function getSMTPConfig() {
    return [
        'host'       => SMTP_HOST,
        'port'       => SMTP_PORT,
        'username'   => SMTP_USERNAME,
        'password'   => SMTP_PASSWORD,
        'encryption' => SMTP_ENCRYPTION,
    ];
}
```

## 2. `config/email-config.php`

Holds the same SMTP credentials used by PHPMailer for sending booking and
contact-form emails. Set `$smtp_host`, `$smtp_username`, and `$smtp_password`
to match the values above.

---

**Security notes**

- Never commit these files to version control — both are already listed in `.gitignore`.
- Use a dedicated mailbox password (or app password), not a personal account password.
- Keep `DEBUG_MODE` set to `false` in production.
