# SEO & GEO Strategy - Gold Price Today Sri Lanka

Status: keyword ranking done, on-page + technical implementation done (2026-09-30), audited
and extended same day after the first pass was checked against real measurements (ยง6).
Owner: buddikasuresh. Source keyword data: user-supplied volume/CPC export, 1-month window.

## 1. Keyword ranking (by monthly volume)

36 keywords, ~337,600 combined monthly searches. Ranked and mapped to the page that now
targets each one.

| # | Keyword | Volume | Intent | Target page (this build) |
|---|---|---:|---|---|
| 1 | gold price in sri lanka | 135,000 | Transactional | `/` |
| 2 | gold price | 49,500 | Transactional | `/` |
| 2 | gold price today | 49,500 | Transactional | `/` |
| 4 | today gold price in sri lanka | 33,100 | Transactional | `/` |
| 5 | gold price in sri lanka today | 27,100 | Transactional | `/` |
| 6 | today gold price in sri lanka 22k | 18,100 | Transactional | `/gold-price-22k-sri-lanka` |
| 7 | today 22k gold price in sri lanka | 12,100 | Transactional | `/gold-price-22k-sri-lanka` |
| 8 | today gold price in sri lanka 24k | 5,400 | Transactional | `/gold-price-24k-sri-lanka` |
| 9 | gold price chart | 4,400 | Navigational | `/` (#chart) + `/gold-price-history` |
| 9 | gold price live | 4,400 | Transactional | `/` |
| 11 | live gold price | 3,600 | Transactional | `/` |
| 12 | 22k gold price in sri lanka | 2,900 | Transactional | `/gold-price-22k-sri-lanka` |
| 12 | sri lanka gold prices increase | 2,900 | Transactional | `/` + `/gold-price-today-sri-lanka` (trend line) |
| 14 | 22 carat gold price in sri lanka | 2,400 | Transactional | `/gold-price-22k-sri-lanka` |
| 14 | karat 22 gold price in sri lanka | 2,400 | Transactional | `/gold-price-22k-sri-lanka` |
| 16 | gold pound price in sri lanka | 1,900 | Transactional | `/gold-price-per-8-grams-sri-lanka` |
| 16 | gold price sri lanka | 1,900 | Commercial | `/` |
| 16 | gold price today colombo | 1,900 | Transactional | `/` + `/gold-price-today-sri-lanka` |
| 19 | 1 gram gold price in sri lanka today | 1,600 | Transactional | `/gold-price-per-gram-sri-lanka` |
| 19 | gold one pound price in sri lanka | 1,600 | Transactional | `/gold-price-per-8-grams-sri-lanka` |
| 19 | gold pound price in sri lanka today | 1,600 | Transactional | `/gold-price-per-8-grams-sri-lanka` |
| 19 | sri lanka gold price live | 1,600 | Transactional | `/` |
| 19 | today gold price in sri lanka 22k 8g | 1,600 | Transactional | `/gold-price-22k-sri-lanka` + `/gold-price-per-8-grams-sri-lanka` |
| 24 | 1 gram gold price in sri lanka | 1,300 | Transactional | `/gold-price-per-gram-sri-lanka` |
| 24 | 1 pound gold price in sri lanka | 1,300 | Transactional | `/gold-price-per-8-grams-sri-lanka` |
| 24 | gold biscuit price in sri lanka | 1,300 | Transactional | FAQ (`/`) - see ยง4 |
| 24 | gold earrings price in sri lanka | 1,300 | Transactional | Not targeted - see ยง4 |
| 24 | gold price increase in sri lanka | 1,300 | Informational | `/` + `/gold-price-today-sri-lanka` (trend line) |
| 29 | 18k gold price in sri lanka | 1,000 | Transactional | `/gold-price-18k-sri-lanka` |
| 29 | 24 carat gold price in sri lanka | 1,000 | Transactional | `/gold-price-24k-sri-lanka` |
| 29 | 24k gold price in sri lanka | 1,000 | Transactional | `/gold-price-24k-sri-lanka` |
| 29 | gold pawn price in sri lanka | 1,000 | Transactional | `/gold-price-per-8-grams-sri-lanka` |
| 29 | gold price in colombo | 1,000 | Transactional | `/` |
| 29 | gold price in sri lanka chart | 1,000 | Transactional | `/` (#chart) |
| 29 | gold price sri lanka today | 1,000 | Transactional | `/` |
| 29 | to day gold price | 1,000 | Transactional | `/` (typo variant, covered naturally) |

**Concentration**: the top 5 rows alone are ~294,200/mo - 87% of total addressable volume.
The homepage carries nearly the whole opportunity; everything else is secondary. This is
why the build put the most SEO/GEO weight (H1, direct-answer paragraph, trend sentence,
FAQ schema, Organization/WebSite schema) on `/`, then the 22K page (2nd biggest cluster:
~34,700/mo across 5 keyword variants), then the pawn/pound page, then 24K/18K.

## 2. What was implemented (2026-09-30)

**Dynamic, date-stamped meta on every page** - title/description are generated per request
from `Seo::todayHuman()` (e.g. "30 September 2026") and the latest verified price, so
search snippets always show today's date and "updated daily" without manual edits.
Example (home): `Gold Price Today in Sri Lanka - 30 September 2026 | Live 24K, 22K, 21K, 18K Rates`.

**Structured data (JSON-LD)** via `Seo.php`: `Organization`, `WebSite`, `WebPage` (with
`dateModified` = latest verified timestamp), `FAQPage` (8 Q&As, up from 5), `BreadcrumbList`
on every inner page, and `ItemList`/`UnitPriceSpecification` on karat pages. Deliberately
skipped `Product`/`Offer` schema - a commodity conversion isn't a purchasable SKU, and
Google treats mismatched Product markup as a manual-action risk.

**OpenGraph + Twitter Card tags** (previously absent) plus a dynamically generated
1200x630 `/og-image.png` (GD, no bundled font/Node/Composer dependency - matches the
project's shared-hosting constraint) showing today's date and all four live prices, so
shared links and AI chat citations get a real branded preview instead of nothing.

**`robots.txt`**: unchanged permissive default, plus explicit `Allow: /` blocks naming
GPTBot, ChatGPT-User, OAI-SearchBot, ClaudeBot, Claude-User, anthropic-ai, PerplexityBot,
Perplexity-User, Google-Extended and Applebot-Extended, so answer-engine crawlers are
unambiguously welcome.

**`llms.txt`** (new, `/llms.txt`): plain-language site index for LLM crawlers - what the
site is, update cadence, unit coverage, key URLs, and a citation guideline (quote the
karat + unit + LKR amount + as-of timestamp, link back instead of caching a number).

**`sitemap.xml`**: added `<lastmod>` (from the latest verified price for price pages),
`<changefreq>` and `<priority>`, previously missing entirely.

**On-page content**:
- Homepage got a real `<h1>` (it was missing whenever a price existed - only the empty
  state had one), a one-paragraph direct answer up top written to be liftable verbatim by
  an AI Overview/answer engine, and a trend sentence.
- New `Content::trendSentence()` compares the latest price to the most recent prior
  `daily_history` close and produces one factual sentence, e.g. *"Gold price in Sri Lanka
  today has increased by 0.80% (LKR 323.66) for 22K gold per gram..."* - this directly
  targets the "gold price increase/sri lanka gold prices increase" cluster (4,200/mo) and
  is exactly the single-sentence, self-contained format AI Overviews prefer to quote.
  Backed by `PriceRepository::previousDayHistory()`.
- "Colombo" is now explicitly named on the homepage and today's-breakdown page (targets
  "gold price today colombo" + "gold price in colombo", 2,900/mo combined) alongside an
  FAQ entry explaining prices are national, not city-specific - honest instead of spinning
  up thin geo-doorway pages.
- The 8-gram page now explicitly carries "pawn", "pound" (paun) and "sovereign" as
  synonyms in title, meta description, H1 and body copy, covering the "gold pound price"
  cluster (~6,400/mo across 4 keyword variants) without creating a duplicate URL.
- Karat pages carry both "K" and "carat" naming ("22K (22 Carat)") since both phrasings
  have independent search volume.
- Internal linking: price table and karat pages now cross-link to the per-gram, per-8g and
  history pages with keyword-bearing anchor text.

**Files touched**: `goldprice-app/src/Services/Seo.php`, `Content.php`,
`PriceRepository.php`; `src/Controllers/PageController.php`, `HistoryController.php`,
`FeedController.php`; `src/Core/Response.php`; `public_html/index.php`;
`views/layout.php`, `home.php`, `purity.php`, `weight.php`, `today-breakdown.php`,
`history-index.php`/`history-year.php`/`history-month.php` (meta only);
`public_html/assets/css/style.css` (new `.lede`/`.breadcrumb` rules).

Verified locally: every route returns 200, JSON-LD parses as valid JSON, sitemap parses as
valid XML (26 URLs), the OG image renders correctly (1200x630 PNG, on-brand), no PHP
warnings in the server or app logs.

## 3. Deliberately not built (and why)

- **Separate landing pages for "Colombo", "gold biscuit", "gold earrings"**: these would be
  near-duplicate content (the price doesn't vary by city or by biscuit/bar weight - it's
  the same formula scaled), which risks Google's doorway-page / duplicate-content
  treatment. Instead: strong on-page mentions + FAQ entries on the pages that already rank
  for the core cluster. "Gold earrings" in particular is a retail-jewellery query this site
  explicitly disclaims ("not official retail prices") - chasing it would work against the
  site's own trust/E-E-A-T positioning.
- **`Product`/`Offer` schema for the price itself**: considered and rejected - see ยง2.
- **Multi-language (Sinhala/Tamil) pages**: no source content to translate accurately yet;
  flagged as a future opportunity (ยง5), not attempted here.

## 4. GEO (generative-engine optimisation) notes

GEO piggybacks almost entirely on good SEO fundamentals plus three things classic SEO
under-weights, all addressed above:
1. **A single, self-contained, quotable answer sentence near the top of the page**
   (the new "lede" paragraphs) - this is what gets lifted verbatim into AI Overviews,
   Perplexity answers and ChatGPT search citations, with a link back.
2. **Explicit freshness signals** - visible "as of" timestamp + "updated daily" copy +
   `article:modified_time` + JSON-LD `dateModified`, all driven by the real latest-price
   timestamp, not a hardcoded date. Answer engines weight recency heavily for
   volatile-price queries.
3. **Crawler access + a plain-language index** - `robots.txt` allow-list + `llms.txt`.
   Neither guarantees inclusion, but a blocked or invisible site guarantees exclusion.

## 5. Backlog / next steps (not done in this session)

- Submit `sitemap.xml` to Google Search Console and Bing Webmaster Tools, and monitor
  Core Web Vitals + query performance there - this repo has no access to those consoles.
- Validate the JSON-LD with Google's Rich Results Test, and the OG tags with Facebook's
  Sharing Debugger and Twitter's Card Validator, once the site is on a public domain
  (schema is syntactically valid locally; those tools' own checks are the real bar).
- Consider a small set of backlink targets (Sri Lankan finance/news sites, jewellery trade
  bodies) - off-page authority is the other half of ranking for the head terms and is
  outside what code changes can do.
- If traffic data later shows real demand for Sinhala/Tamil, add `/si/` or `/ta/` variants
  with proper `hreflang` reciprocal tags (self-referencing hreflang is already in place).
- Swap the GD-generated OG image for a designed static template if/when a licensed TTF
  font is added to the repo - current version intentionally uses GD's built-in bitmap font
  to stay dependency-free on shared hosting.

## 6. Follow-up audit (same day) - what the first pass got wrong

Asked to verify the first pass was actually complete, three real defects turned up:

1. **Title/description length overruns.** Measured against the live pages, not assumed:
   home was 81 chars / 247 chars (limits are ~60 / ~155) - the description was nearly 2x
   over budget and would have been hard-truncated mid-sentence in the SERP. Karat pages
   were 76/180. Fixed by adding `Seo::todayShort()` (compact date, e.g. "30 Sep") and
   trimming every description to its highest-value numbers only (e.g. home now leads with
   22K + 24K, not all four karats - the other two are still on the page itself). Re-measured
   after the fix: every page is now under both limits (see the table this section replaces
   in spirit - re-run the same curl+PHP length check in ยง2 if verifying again).
2. **A real mobile layout bug**, found by actually loading the homepage in a 390px-wide
   headless browser instead of only checking desktop screenshots: the price-history
   `<canvas>` rendered 32px too wide and caused page-level horizontal scroll. Root cause in
   `public_html/assets/js/app.js`: the resize handler read `parent.clientWidth` on a
   `box-sizing: border-box` container, which includes the container's own left+right
   padding, so the canvas was sized to the padding-inclusive box instead of the padding-
   exclusive content area. Fixed by subtracting the parent's computed horizontal padding
   before sizing the canvas. This matters for SEO, not just looks - mobile usability and
   layout stability (CLS) are part of Google's ranking signals and mobile-first indexing.
3. **Missing schema types that had an obvious, free slot**: `HowTo` on the step-by-step
   breakdown page (`Content::methodSteps()` is literally a numbered procedure - exactly
   what `HowTo` is for) and `SpeakableSpecification` pointing at the `.lede` direct-answer
   paragraph on every price page, for voice assistants / AI read-aloud.

Also added, having under-weighted them the first time:
- **Four trust/E-E-A-T pages**: `/about`, `/contact`, `/privacy`, `/terms` - linked from
  every page's footer, in the sitemap, with breadcrumb schema. Written honestly: the About
  page argues trust through *process transparency* (published methodology, dual-source
  verification, open API) rather than a fabricated author bio, since no real name/company
  was supplied and inventing one would be worse than the gap it's meant to fill. Privacy
  policy was written only after actually grepping the codebase for analytics/ad/tracking
  scripts (`app.js`, everywhere) to confirm there are none - it says that, accurately,
  instead of generic boilerplate.
- **A "why does gold price change" FAQ entry** naming real Sri Lanka-specific demand
  factors (import duty, Avurudu/wedding-season demand, CBSL-influenced exchange rate)
  instead of only describing the calculation mechanically. Kept deliberately non-numeric -
  no fabricated duty rates or statistics - since getting a specific figure wrong on a
  finance-adjacent page is worse than a general, accurate statement.

**Still not done - flagged, not fixed:** Sinhala and Tamil content. This is the largest
remaining gap for "targeting Sri Lanka" specifically - most of the country searches in
Sinhala or Tamil, not English, and this build is English-only. Not attempted without
checking first: machine-translating financial figures/terminology carries real accuracy
risk, and it's an ongoing content commitment (three languages to keep in sync going
forward), not a one-time code change - genuinely the user's call on scope and approach,
not a default to silently take.

## 7. Sri Lanka localisation + technical pass (2026-10-01)

- **Sinhala and Tamil pages** at `/si` and `/ta` (`Services/Lang.php`, `views/home-local.php`): translated
  title, meta, H1, direct-answer paragraph, price cards/table and a 4-question FAQ with FAQPage schema,
  live prices from the same verified source. Reciprocal `hreflang` (en-LK, si-LK, ta-LK, x-default) in
  the page head and in `sitemap.xml` (xhtml:link). Header language switcher, footer links, Noto Sans
  Sinhala/Tamil fonts loaded only on those pages. **Have a native speaker review the copy** before promoting it.
- **Technical**: HEAD requests now work (previously 404, which some crawlers/uptime tools use);
  security headers; `Cache-Control: public, max-age=120, stale-while-revalidate=600` on public pages
  (admin/cron `no-store`); cache-busted CSS; `/favicon.ico`, `/manifest.webmanifest`, apple-touch-icon;
  `geo.region=LK`, `og:locale:alternate`; Organization `areaServed: Sri Lanka`; sitemap no longer lists
  history months with no data; `llms.txt` lists the Sinhala/Tamil pages.
- **Still needs doing outside the code** (cannot be done from this repo): deploy on the real domain with
  HTTPS, set `APP_URL` in `.env`, submit the sitemap to Google Search Console and Bing, verify Rich Results,
  and build Sri Lankan backlinks. Rankings cannot be guaranteed - they depend on domain authority and competition.
