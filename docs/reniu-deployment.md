# Deploying reniu.my

`reniu.my` is served by **this same Laravel app** — there is no second codebase.
The request host decides which site is active, which drives gateway
credentials, labels, Turnstile keys, branding and which routes exist.

| | nansolutions.com.my | reniu.my |
|---|---|---|
| Routes | everything | `/pay` + webhooks only (all else 404s) |
| Reference prefix | `PAY-` | `RNU-` |
| CHIP | FPX + Card | **Card only** ("Credit Card") |
| Fiuu | disabled | **"SPayLater"** |
| DOKU | "Grab PayLater" | "Grab PayLater" |
| Atome | Atome | Atome |
| Admin | full admin | none — managed from NAN Solutions |

Everything is configured in **`config/sites.php`**. Credentials come from
`RENIU_*` env vars.

---

## 1. DNS

> ⚠️ **Check email first.** If `reniu.my` has MX records, changing
> **nameservers** will break email unless you recreate MX/SPF at the new
> provider. Changing only the **A record** is the safe path.

```bash
dig +short MX reniu.my      # if this returns anything, protect it
dig +short NS reniu.my
```

Point the domain at the droplet:

| Type | Host | Value |
|---|---|---|
| A | `@` | `139.59.218.52` |
| A | `www` | `139.59.218.52` |

Wait for propagation before continuing:
```bash
dig +short A reniu.my       # must return 139.59.218.52
```

## 2. Apache vhost

reniu.my points at the **same** Laravel `public/` as nansolutions.com.my.

```bash
sudo nano /etc/apache2/sites-available/reniu.my.conf
```

```apache
<VirtualHost *:80>
    ServerName reniu.my
    ServerAlias www.reniu.my
    DocumentRoot /var/www/Pojie/icms.nansolutions.com.my/public

    <Directory /var/www/Pojie/icms.nansolutions.com.my>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/reniu_error.log
    CustomLog ${APACHE_LOG_DIR}/reniu_access.log combined
</VirtualHost>
```

```bash
sudo a2ensite reniu.my.conf
sudo apache2ctl configtest && sudo systemctl reload apache2
```

Verify over plain HTTP before adding SSL — `http://reniu.my` should redirect to
`/pay`.

## 3. SSL

```bash
sudo certbot --apache -d reniu.my -d www.reniu.my
```

> Do **not** create a `.conf.bak` next to the vhost. A restored `.bak` is what
> silently reverted the nansolutions vhost once. Keep backups in `~`.

## 4. Environment

Add to the PROD `.env` (see `.env.example` for the full block):

```dotenv
DEFAULT_SITE=nansolutions

RENIU_TURNSTILE_SITE_KEY=
RENIU_TURNSTILE_SECRET_KEY=

RENIU_CHIP_API_KEY=
RENIU_CHIP_BRAND_ID=
RENIU_CHIP_BASE_URL=https://gate.chip-in.asia/api/v1
RENIU_CHIP_PUBLIC_KEY_FOR_WEBHOOK=

RENIU_FIUU_MERCHANT_ID=
RENIU_FIUU_VERIFY_KEY=
RENIU_FIUU_SECRET_KEY=
RENIU_FIUU_SANDBOX=false

RENIU_SENANGPAY_CLIENT_ID=
RENIU_SENANGPAY_SECRET_KEY=
RENIU_SENANGPAY_BASE_URL=https://api.doku.com

RENIU_ATOME_PARTNER_ID=
RENIU_ATOME_SECRET_KEY=
RENIU_ATOME_BASE_URL=https://api.apaylater.com/v2
RENIU_ATOME_CALLBACK_SECRET=
```

A gateway only appears at reniu's checkout once its credentials are filled in,
so you can roll them out one at a time.

**Turnstile:** keys are hostname-bound — create a **separate widget** for
`reniu.my`. Reusing the NAN Solutions key will fail the captcha.

## 5. Deploy

```bash
cd /var/www/Pojie/icms.nansolutions.com.my
git pull origin main
composer install --no-dev --optimize-autoloader     # composer.json changed (helpers autoload)
php artisan migrate --path=database/migrations/2026_07_23_090000_add_site_to_payments.php --force
npm run build
php artisan optimize:clear
```

Existing payments default to `site = nansolutions`, so nothing changes for them.

## 6. Gateway dashboards

Every reniu account is separate, so each points its webhook at **reniu.my**:

| Gateway | Where | Value |
|---|---|---|
| CHIP | webhook + allowed redirect domains | `https://reniu.my/webhooks/payments/chip` |
| Fiuu | registered website URL must be `reniu.my` | `https://reniu.my/webhooks/payments/fiuu` |
| DOKU | Payment Settings → Webhook | `https://reniu.my/webhooks/payments/senangpay` |
| Atome | callback URL | `https://reniu.my/webhooks/payments/atome` |

> DOKU's notification URL is **global per account** — this only works because
> reniu has its own DOKU account. Never point one account at two domains.

## 7. Verify

```bash
# route exposure
curl -sI https://reniu.my/            | head -1   # 302 -> /pay
curl -sI https://reniu.my/pay         | head -1   # 200
curl -sI https://reniu.my/login       | head -1   # 404
curl -sI https://reniu.my/privacy-policy | head -1 # 404
```

Then a real payment:
1. `https://reniu.my/pay` → the gateway list shows **reniu's** labels
   (Credit Card, SPayLater, Grab PayLater, Atome).
2. Complete one — the reference must start with **`RNU-`**.
3. Admin → **Payments → Reniu** shows it, and it flips to **paid**.
4. The payer gets the `payment_received` WhatsApp.

## Notes & gotchas

- **Fiuu** was blocked on nansolutions because its account is registered to
  `reniu.my`. Serving reniu.my genuinely satisfies that: the buyer's browser is
  on reniu.my and the return/callback URLs are generated on reniu.my
  (`route()` uses the current request host — there is no `forceRootUrl` in this
  app). Fiuu may still need to **activate** the account; that is separate from
  the domain requirement.
- **Adding a third site** = one entry in `config/sites.php` + a vhost. No code.
- **Branding:** drop a logo at `public/images/reniu-logo.png` and reniu's
  checkout uses it; otherwise it shows the company name as text.
- **Webhooks fail closed.** A callback delivered to one domain cannot settle
  another site's payment — the controller compares the callback's host-derived
  site against `payments.site` and returns 409 on mismatch. Covered by
  `tests/Feature/MultiSiteTest.php`.
- **Reconcile is CLI-safe.** The cron has no request host, so `reconcile()`
  passes the payment's own `site` when resolving the driver.
