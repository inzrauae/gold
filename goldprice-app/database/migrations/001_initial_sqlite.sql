CREATE TABLE IF NOT EXISTS admin_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'admin',
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS prices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    spot_usd_per_oz REAL NOT NULL,
    usd_lkr REAL NOT NULL,
    price_24k_gram REAL NOT NULL,
    price_22k_gram REAL NOT NULL,
    price_21k_gram REAL NOT NULL,
    price_18k_gram REAL NOT NULL,
    price_24k_8g REAL NOT NULL,
    price_22k_8g REAL NOT NULL,
    price_21k_8g REAL NOT NULL,
    price_18k_8g REAL NOT NULL,
    price_24k_oz REAL NOT NULL,
    price_22k_oz REAL NOT NULL,
    price_21k_oz REAL NOT NULL,
    price_18k_oz REAL NOT NULL,
    gold_source_primary TEXT,
    gold_source_secondary TEXT,
    fx_source_primary TEXT,
    fx_source_secondary TEXT,
    verification_mode TEXT NOT NULL DEFAULT 'single_source',
    gold_reading_timestamp INTEGER,
    fx_reading_timestamp INTEGER,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_prices_created_at ON prices (created_at);

CREATE TABLE IF NOT EXISTS daily_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    date TEXT NOT NULL UNIQUE,
    spot_usd_per_oz REAL NOT NULL,
    usd_lkr REAL NOT NULL,
    price_24k_gram REAL NOT NULL,
    price_22k_gram REAL NOT NULL,
    price_21k_gram REAL NOT NULL,
    price_18k_gram REAL NOT NULL,
    is_imported INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS update_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    status TEXT NOT NULL,
    reason TEXT,
    details_json TEXT,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_update_log_created_at ON update_log (created_at);

CREATE TABLE IF NOT EXISTS pending_changes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    payload_json TEXT NOT NULL,
    reason TEXT NOT NULL,
    resolved INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS price_alerts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    channel_type TEXT NOT NULL,
    destination TEXT NOT NULL,
    karat INTEGER NOT NULL DEFAULT 22,
    threshold_type TEXT NOT NULL,
    threshold_value REAL NOT NULL,
    active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS news_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    url TEXT NOT NULL,
    source TEXT,
    published_at TEXT,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS scheduler_state (
    id INTEGER PRIMARY KEY CHECK (id = 1),
    last_price_attempt_at TEXT,
    last_price_success_at TEXT,
    last_news_attempt_at TEXT,
    last_cleanup_at TEXT
);

INSERT OR IGNORE INTO scheduler_state (id) VALUES (1);
