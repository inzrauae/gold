# Deploying on cPanel

Time needed: about 30–45 minutes. You need a cPanel account with PHP 8.1 or newer, MySQL or
MariaDB, cron jobs, and (ideally) Terminal or SSH access. No Composer, Node, queue worker or
Redis is needed.

Throughout this guide replace `USER` with your cPanel username and `www.example.lk` with your domain.

## Quick deploy with Terminal (www.goldpricetoday.lk)

With PHP 8.1+ and `pdo_sqlite` enabled for the domain (step 1) and SSL active (step 7), run this in
cPanel → **Terminal**. It clones the repo to `~/gold-src`, puts the app in `~/goldprice-app`, copies
the web files into `~/public_html` (backing up what was there first), creates `.env` with a SQLite
database, fetches the first price and installs the cron job. Run the same command again to update.

```bash
cd ~ && (git -C gold-src pull --ff-only 2>/dev/null || git clone -b main https://github.com/inzrauae/gold.git gold-src) && ADMIN_EMAIL=you@example.lk bash gold-src/deploy.sh
```

Then create your admin login with the command it prints, and fill in the `CONTACT_*` and API key
settings in `~/goldprice-app/.env`. The rest of this guide is the manual route (and MySQL setup).

---

## 1. Choose PHP version and extensions

1. cPanel → **MultiPHP Manager** → select your domain → **PHP 8.2** (8.1+ works) → Apply.
2. cPanel → **Select PHP Version** / **MultiPHP INI Editor** (name depends on host) and make sure these
   extensions are enabled: `pdo_mysql`, `curl`, `mbstring`, `simplexml`, `json`, `openssl`
   (`pdo_sqlite` is only needed to run the tests on the server).
3. Recommended INI values: `memory_limit = 128M`, `max_execution_time = 60`, `display_errors = Off`.

## 2. Create the database

cPanel → **MySQL Database Wizard**:

1. Database name, e.g. `goldprice` → becomes `USER_goldprice`.
2. User, e.g. `gold` → becomes `USER_gold`, with a long random password.
3. Privileges: **ALL PRIVILEGES** on that database.

## 3. Upload the files

The zip contains two folders. They must end up side by side in your home directory:

```
/home/USER/goldprice-app/     <- application (NOT web-accessible)
/home/USER/public_html/       <- web root
```

1. cPanel → **File Manager** → open `/home/USER` (your home, *not* public_html).
2. **Upload** the zip, then **Extract** it there.
3. Move the *contents* of the extracted `public_html` folder into your real `public_html`
   (`index.php`, `.htaccess`, `assets/`). If `public_html` has an old `index.html`, delete it.
   Enable **Settings → Show Hidden Files** in File Manager so `.htaccess` is visible.
4. Move the `goldprice-app` folder to `/home/USER/goldprice-app`.

**Addon domain or subdomain** with a different document root (e.g. `/home/USER/gold.example.lk`)?
Put `goldprice-app` next to that folder, or edit `APP_DIR` at the top of `index.php`
to point to it (for example `'/home/USER/goldprice-app'`).

Permissions: folders `755`, files `644`. `goldprice-app/storage` and its subfolders must be
writable by PHP (`755` is enough on normal cPanel servers, where PHP runs as your user).

## 4. Configure `.env`

1. In `goldprice-app`, copy `.env.example` to `.env`.
2. Edit `.env` and set at least:

```ini
APP_URL=https://www.example.lk          # exact public address, no trailing slash
SITE_NAME="Gold Price Sri Lanka"
FORCE_HTTPS=true
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=USER_goldprice
DB_USERNAME=USER_gold
DB_PASSWORD=the-password-from-step-2
GOLD_PRICE_SECONDARY_API_KEY=...        # MetalpriceAPI (step 5)
EXCHANGE_RATE_API_KEY=...               # Open Exchange Rates (step 5)
ADMIN_EMAIL=you@example.lk              # receives verification alerts
MAIL_FROM=noreply@example.lk            # an address on your domain
CRON_TOKEN=                             # only for the HTTP cron fallback (step 8)
```

3. Change the file permissions of `.env` to **600**.

`APP_URL` matters: canonical URLs, sitemap, RSS, the widget and the API all use it, and other
hostnames (e.g. `example.lk` without `www`) are redirected to it.

## 5. Get the API keys

| Slot | Default provider | Sign up | Plan to choose |
|---|---|---|---|
| Gold, primary | Gold-API.com | nothing – no key needed | Free |
| Gold, secondary | MetalpriceAPI | https://metalpriceapi.com | **Essential** (about US$5/month, 30-min delay; the default settings fit its 1,000 requests) or **Basic** (about US$12/month, 10-min delay, more headroom). The free plan is delayed by a day and every update would be rejected as stale. |
| USD/LKR, primary | Open Exchange Rates | https://openexchangerates.org/signup/free | Free (hourly, 1,000 requests/month) |
| USD/LKR, secondary | ExchangeRate-API open access | nothing – no key needed | Free (daily; attribution is added to the footer automatically) |

Paste the MetalpriceAPI key into `GOLD_PRICE_SECONDARY_API_KEY` and the Open Exchange Rates App ID
into `EXCHANGE_RATE_API_KEY`. The `*_MIN_INTERVAL` values in `.env.example` are already set so these
plans stay within quota (see API_RESEARCH.md; on Basic you may set `GOLD_SECONDARY_MIN_INTERVAL=0`). Keys are only ever sent in request headers or to
the provider, are never shown on the site and are redacted from logs.

Alternatives supported without code changes: GoldAPI.io (`goldapi_io`) for gold, keyed
ExchangeRate-API (`exchangerate_api`) and MetalpriceAPI (`metalpriceapi`) for USD/LKR.

## 6. Create the tables and the admin account

Open cPanel → **Terminal** (or SSH) and run:

```bash
cd ~/goldprice-app
php -v                                   # must say 8.1 or newer; otherwise use the full path, e.g.
                                         # /opt/cpanel/ea-php82/root/usr/bin/php
php cli/migrate.php                      # "Applied: 001_initial"
php cli/create-admin.php --email=you@example.lk          # asks for a password (min 12 chars)
php cli/check-sources.php                # all four sources should say OK
php cli/update-prices.php                # "PUBLISHED: ..." = your first verified price
```

A read-only colleague account: `php cli/create-admin.php --email=colleague@example.lk --role=viewer`.

**No Terminal/SSH?** Add each command once as a cron job set to run every minute, wait a minute, then
delete it. For the admin account use:
`cd /home/USER/goldprice-app && ADMIN_PASSWORD='a-long-password' /usr/local/bin/php cli/create-admin.php --email=you@example.lk`
and delete that cron entry immediately afterwards (it contains the password), then change the
password later by running the same command again with a new one.

## 7. SSL

cPanel → **SSL/TLS Status** → run **AutoSSL** for the domain (and `www.`). When the padlock works,
keep `FORCE_HTTPS=true`. The `.htaccess` also redirects HTTP to HTTPS and sends HSTS from the app.
If you use Cloudflare, set SSL mode to *Full (strict)* and `TRUST_PROXY_HEADERS=true`.

## 8. Cron job (the only background process)

cPanel → **Cron Jobs** → Common setting *Once Per Five Minutes* (`*/5 * * * *`) → command:

```
/usr/local/bin/php /home/USER/goldprice-app/cli/scheduler.php >/dev/null 2>&1
```

(If `/usr/local/bin/php` is an old version on your host, use the path of the PHP 8 binary, e.g.
`/opt/cpanel/ea-php82/root/usr/bin/php`.)

The scheduler decides by itself what is due: price update every `PRICE_UPDATE_INTERVAL` minutes
while markets are open (every `MARKET_CLOSED_INTERVAL` at weekends), a faster retry after a failed
update, news every `NEWS_UPDATE_INTERVAL` minutes and a daily clean-up. A lock prevents overlapping runs.

**Queue:** none is needed. **Cache:** file cache in `goldprice-app/storage/cache`, cleared
automatically after every update; clear it manually with `php cli/clear-cache.php` after editing
`.env` or templates.

**Host without cron?** Set a `CRON_TOKEN` (24+ random characters) and have an external monitor
(e.g. cron-job.org) call `https://www.example.lk/cron/run` every 5 minutes with the header
`X-Cron-Token: <token>`.

## 9. Check that everything works

| Check | URL |
|---|---|
| Website | https://www.example.lk/ |
| Admin dashboard | https://www.example.lk/admin |
| Public API | https://www.example.lk/api/v1/gold-price |
| API history | https://www.example.lk/api/v1/gold-price/history?range=30d |
| API docs | https://www.example.lk/gold-price-api |
| RSS feed | https://www.example.lk/rss/gold-price.xml (also /feed.xml) |
| Widget script | https://www.example.lk/widgets/gold-price.js |
| Widget builder | https://www.example.lk/gold-price-widget |
| Sitemap | https://www.example.lk/sitemap.xml |
| Health (for uptime monitors) | https://www.example.lk/healthz (HTTP 200 = fresh data, 503 = no data or stale) |

Also confirm these are **blocked** (should give 403/404): `https://www.example.lk/.htaccess`,
and that nothing under `goldprice-app` is reachable from the web.

Then submit the sitemap in Google Search Console and Bing Webmaster Tools.

## 10. Run the tests on the server (optional)

```bash
cd ~/goldprice-app
php tests/run.php                  # needs pdo_sqlite; uses an in-memory DB, never your MySQL data
node tests/js/calc.test.js         # only if Node is available (usually not on shared hosting)
```

## 11. Optional: import real price history

The chart and history fill up from the day collection starts; the site shows
"Historical data collection started on …". If you have a paid MetalpriceAPI plan you can import
real daily closes for earlier dates (one API request per 365 days). Imported days are labelled
"imported" and drawn dashed; live days are never overwritten.

```bash
php cli/backfill.php --from=2021-10-01 --to=2026-09-26
```

## Day-to-day operation

- **Dashboard** (`/admin`): current price, last update, verification status, API status and raw
  responses, source comparison, failed updates, logs and records. Buttons: *Update now*,
  *Retry failed update*, *Check sources*, *View logs*, and *Accept large change* (appears only when
  an update was held for an abnormal move; requires a confirmation tick, still needs both sources to agree).
- **Alert e-mails** arrive at `ADMIN_EMAIL` when verification fails (at most one per reason per
  `NOTIFY_COOLDOWN_MINUTES`). If mails do not arrive, check cPanel → Email Deliverability for your
  domain (SPF/DKIM) and that `MAIL_FROM` is an address on your domain.
- **Logs:** `goldprice-app/storage/logs/` (one JSON-lines file per day, kept `LOG_RETENTION_DAYS`).
- **Market holidays:** add dates to `GOLD_MARKET_HOLIDAYS` so the site shows "Markets closed".

## Updating the code later

Upload the new files over the old ones (never overwrite `.env` or `storage/`), then run
`php cli/migrate.php` and `php cli/clear-cache.php`.

## Troubleshooting

| Symptom | Likely cause / fix |
|---|---|
| "Application folder not found" | `goldprice-app` is not next to `public_html`; fix the location or `APP_DIR` in `index.php`. |
| White page / 500 | Check `goldprice-app/storage/logs/` and cPanel → Errors. Temporarily set `APP_DEBUG=true` (turn it off again). |
| Every page 404 except home | `.htaccess` missing or `mod_rewrite` off; make sure hidden files were uploaded. |
| Status stays "Data verification required" | Open `/admin` → the failure reason is shown. Common: missing/invalid API key, MetalpriceAPI free plan (stale), quota exceeded, sources disagreeing. |
| Redirect loop behind Cloudflare | `TRUST_PROXY_HEADERS=true` and SSL mode *Full (strict)*. |
| Widget shows only a link on another site | That site's Content-Security-Policy blocks external scripts; the fallback link is intentional. |
