# Gold Price Today Sri Lanka

A production-ready website that publishes **verified, indicative** gold prices for Sri Lanka
(24K, 22K, 21K and 18K; per gram, per 8 g "pawn" and per troy ounce), with a history chart,
calculator, daily history archive, RSS feed, free JSON API, embeddable widget and a secure
admin dashboard.

Built for ordinary **cPanel shared hosting**: plain PHP 8.1+ and MySQL/MariaDB, no Composer,
no Node, no queue worker, one cron job.

- Deployment on cPanel: **[DEPLOYMENT.md](DEPLOYMENT.md)**
- Data provider research and API quotas: **[API_RESEARCH.md](API_RESEARCH.md)**

## How prices are produced

```
every 5 min (cron) -> scheduler decides if an update is due (default every 15 min)
  -> fetch gold spot (primary + independent secondary)
  -> fetch USD/LKR (primary + independent secondary)
  -> validate each reading: format, currency, positive, sanity bounds, freshness
  -> cross-check sources (default max 1% difference) - never averaged
  -> compare with last verified price (default max 5% gold / 3% FX move)
  -> calculate: spot x USD/LKR / 31.1034768 x karat/24, round once at the end
  -> store (no duplicate if unchanged) -> daily roll-up -> RSS -> clear caches
If any check fails: nothing is published, the last verified price stays live with its time,
status becomes "Data verification required", the reason is logged, the admin is e-mailed
(throttled), and the scheduler retries after RETRY_AFTER_MINUTES.
```

No price is ever typed in by hand, estimated, interpolated or scraped from jewellers.
Before the first verified update the site says so instead of showing a number.

## Folder layout

```
goldprice-app/            <- OUTSIDE the web root (e.g. /home/USER/goldprice-app)
  .env.example            all configuration (copy to .env)
  bootstrap.php
  cli/                    scheduler, update-prices, migrate, create-admin, backfill, check-sources, fetch-news, clear-cache
  database/migrations/    schema for MySQL/MariaDB and SQLite
  src/
    Core/                 tiny framework: router, request/response, DB (PDO), cache, rate limiter, session, CSRF, auth, logger
    Pricing/              GoldPriceCalculator (single source of truth for all maths)
    Providers/            Gold-API.com, MetalpriceAPI, GoldAPI.io, Open Exchange Rates, ExchangeRate-API (open + keyed)
    Services/             PriceUpdater (verification), PriceRepository, Scheduler, RssFeed, NewsService, Seo, Content, Backfill, AlertService (future alerts)
    Controllers/          pages, history, API, feeds/widget/cron, admin
  views/                  PHP templates (every value escaped)
  storage/                cache, logs, feeds, locks, rate-limit files (must be writable)
  tests/                  dependency-free test runner + JS parity test
public_html/              <- the web root
  index.php               front controller
  .htaccess               HTTPS, routing, blocking dotfiles, compression, caching
  assets/                 css, js (vanilla), images
```

## Public URLs

| URL | What |
|---|---|
| `/` | Today's price, history chart, calculator, table, method, news, widget, FAQ |
| `/gold-price-today-sri-lanka` | Step-by-step breakdown with inputs and today's updates |
| `/gold-price-24k-sri-lanka` `/gold-price-22k-sri-lanka` `/gold-price-21k-sri-lanka` `/gold-price-18k-sri-lanka` | Per-purity pages |
| `/gold-price-per-gram-sri-lanka` `/gold-price-per-8-grams-sri-lanka` | Weight tables |
| `/gold-price-history` `/gold-price-history/2026` `/gold-price-history/2026/09` | Daily archive |
| `/data-sources` | Sources, methodology, verification, limitations |
| `/gold-price-widget` `/gold-price-widget/how-to-use` | Widget builder and guide/terms |
| `/widgets/gold-price.js` | Embeddable widget script |
| `/gold-price-api`, `/api/v1/gold-price`, `/api/v1/gold-price/history?range=30d` | Free JSON API + docs |
| `/rss/gold-price.xml`, `/feed.xml` | RSS 2.0 |
| `/sitemap.xml`, `/robots.txt`, `/healthz` | SEO and monitoring |
| `/admin` | Admin dashboard (login required) |

## Local development

```bash
cp goldprice-app/.env.example goldprice-app/.env
# in .env: APP_URL=http://127.0.0.1:8000, FORCE_HTTPS=false, DB_CONNECTION=sqlite,
#          DB_SQLITE_PATH=/absolute/path/goldprice-app/storage/database.sqlite
php goldprice-app/cli/migrate.php
php goldprice-app/cli/create-admin.php --email=you@example.lk
php goldprice-app/cli/update-prices.php        # needs real API keys in .env
php -S 127.0.0.1:8000 -t public_html public_html/router.php
```

## Tests

```bash
php goldprice-app/tests/run.php          # 80 tests: calculator, providers, verification, history, API, RSS, widget, cache, security, admin
node goldprice-app/tests/js/calc.test.js # browser calculator == PHP calculator (125 cases)
```

Tests use in-memory SQLite, a fake HTTP client and a frozen clock; they never call the real APIs
and never touch your production database.

## Price alerts (future)

`price_alerts` table, `AlertService` and the `AlertChannel` interface are in place for email,
WhatsApp, Telegram and browser-push alerts. They are disabled (`ALERTS_ENABLED=false`) and have no
public sign-up form yet; add a channel class and a subscription page when you are ready.
