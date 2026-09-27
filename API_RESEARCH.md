# Data provider research

What the site needs: (1) the international spot price of gold in **US$ per troy ounce**, and
(2) the **USD/LKR** exchange rate — each from **two independent sources** so every published price
can be cross-checked. Providers were compared on data quality, update frequency, cost, quota,
reliability signals, terms, and whether the response has a clear timestamp.

Plans and prices below were checked in September 2026. **Providers change their plans — confirm on
their pricing pages before subscribing.**

## Gold spot price (XAU/USD)

| Provider | Endpoint used | Auth | Freshness | Cost / quota | Role here |
|---|---|---|---|---|---|
| **Gold-API.com** | `GET https://api.gold-api.com/price/XAU` → `price`, `currency`, `updatedAt` | none | real time | free; the live price endpoint has no key and no stated rate limit (history/OHLC endpoints need a key) | **Primary** |
| **MetalpriceAPI** | `GET https://api.metalpriceapi.com/v1/latest?base=USD&currencies=XAU,LKR` → `rates.USDXAU`, `rates.LKR`, `timestamp` | `X-API-KEY` header | Free: daily · Essential: 30 min · Basic: 10 min | Free 100/month · Essential about US$5/month, 1,000/month · Basic about US$12/month, 10,000/month (yearly billing) | **Secondary** (independent check); also optional history backfill via `/v1/timeframe` (max 365 days per request) |
| GoldAPI.io | `GET https://www.goldapi.io/api/XAU/USD` → `price`, `timestamp` | `x-access-token` header | real time | small free tier; paid plans for higher volume | Supported alternative (`goldapi_io`) |

Why this pair: they are separate companies with separate data pipelines, so a fault in one is very
unlikely to be repeated identically in the other. Gold-API.com costs nothing and is real time;
MetalpriceAPI is a paid, documented commercial service with explicit plan delays and a history
endpoint.

**Important:** MetalpriceAPI's free plan updates only daily. During market hours such a reading is
older than `MAX_GOLD_READING_AGE_MINUTES` (90) and is rejected as stale, so with
`REQUIRE_SECONDARY_SOURCE=true` nothing would ever publish. Use Essential or Basic.

Not used: scraping jeweller, bank or news websites (fragile, often not permitted, and not
verifiable), and "free" endpoints without a timestamp or clear terms.

## USD/LKR exchange rate

| Provider | Endpoint used | Auth | Freshness | Cost / quota | Role here |
|---|---|---|---|---|---|
| **Open Exchange Rates** | `GET https://openexchangerates.org/api/latest.json?symbols=LKR` → `rates.LKR`, `timestamp` | `Authorization: Token <app id>` | Free plan: hourly | Free 1,000 requests/month (USD base) | **Primary** |
| **ExchangeRate-API (open access)** | `GET https://open.er-api.com/v6/latest/USD` → `rates.LKR`, `time_last_update_unix` | none | once a day | free; attribution required ("Rates By Exchange Rate API" link — the site adds it to the footer automatically whenever this source is used); rate-limited, so poll rarely | **Secondary** |
| ExchangeRate-API (keyed v6) | `https://v6.exchangerate-api.com/v6/<key>/latest/USD` | key in path (redacted from all logs) | plan dependent | free and paid plans | Supported alternative (`exchangerate_api`) |
| MetalpriceAPI | same call as gold (`rates.LKR`) | as above | as above | as above | Supported alternative (`metalpriceapi`) |

**Central Bank of Sri Lanka:** CBSL publishes the official indicative USD/LKR spot rate (a weighted
average of interbank trades) each business day at cbsl.gov.lk → Rates and Indicators → Exchange
Rates. It has no public API, so it is referenced on the `/data-sources` page rather than used
automatically. Market mid rates from the providers above normally sit very close to it.

Why a 1% cross-check works for FX: a daily secondary rate is less precise intraday, but USD/LKR
rarely moves 1% within a day. If it does, publication stops and the admin decides.

## Request budget (default settings)

Scheduler cadence: every `PRICE_UPDATE_INTERVAL` = 15 minutes while markets are open (about 120
hours a week), every `MARKET_CLOSED_INTERVAL` = 180 minutes at the weekend (about 48 hours a week). One
month is about 4.33 weeks, so about 520 open hours and 208 closed hours.

| Slot | Setting | Requests / month | Plan limit |
|---|---|---|---|
| Gold-API.com | every run (15 min open, 180 min closed) | about 2,080 + 69 ≈ **2,150** | no key, no stated limit |
| MetalpriceAPI, **Basic** | `GOLD_SECONDARY_MIN_INTERVAL=0` | about 2,080 + 208/3 ≈ **2,150** | 10,000 ✔ |
| MetalpriceAPI, **Essential** (default `.env.example`) | `GOLD_SECONDARY_MIN_INTERVAL=45` | about 520 × 60/45 + 208/3 = 693 + 69 ≈ **760** (+ retries, admin checks) | 1,000 ✔ |
| Open Exchange Rates, Free | `FX_PRIMARY_MIN_INTERVAL=60` | at most 24 × 30.4 ≈ **730** | 1,000 ✔ |
| open.er-api.com | `FX_SECONDARY_MIN_INTERVAL=360` | about **120** | fair use ✔ |

`*_MIN_INTERVAL` reuses the provider's last successful reading instead of calling it again. A
reused reading still goes through the freshness check using the provider's own timestamp, so on
Essential the oldest gold quote used is about 30 + 45 = 75 minutes old, inside the 90-minute limit.

Staying safely inside quota matters: when a provider returns HTTP 401/402/403/429 the update fails
with `quota_or_auth` and the site keeps the last verified price until the next successful run.

## Recommendation

- **Most headroom (about US$12/month):** Gold-API.com + MetalpriceAPI Basic for gold; Open
  Exchange Rates Free + ExchangeRate-API open access for USD/LKR. Price every 15 minutes.
- **Budget (about US$5/month):** same, with MetalpriceAPI Essential; the defaults in `.env.example`
  are already sized for it.
- **Zero cost (not recommended):** `REQUIRE_SECONDARY_SOURCE=false` and no MetalpriceAPI. The site
  then publishes gold from a single source (still bounds-, freshness- and move-checked) and labels
  those prices `single_source` in the API and on the "today" page.
