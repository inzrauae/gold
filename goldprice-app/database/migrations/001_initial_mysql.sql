CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'admin',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS prices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    spot_usd_per_oz DECIMAL(12,4) NOT NULL,
    usd_lkr DECIMAL(12,4) NOT NULL,
    price_24k_gram DECIMAL(12,2) NOT NULL,
    price_22k_gram DECIMAL(12,2) NOT NULL,
    price_21k_gram DECIMAL(12,2) NOT NULL,
    price_18k_gram DECIMAL(12,2) NOT NULL,
    price_24k_8g DECIMAL(12,2) NOT NULL,
    price_22k_8g DECIMAL(12,2) NOT NULL,
    price_21k_8g DECIMAL(12,2) NOT NULL,
    price_18k_8g DECIMAL(12,2) NOT NULL,
    price_24k_oz DECIMAL(12,2) NOT NULL,
    price_22k_oz DECIMAL(12,2) NOT NULL,
    price_21k_oz DECIMAL(12,2) NOT NULL,
    price_18k_oz DECIMAL(12,2) NOT NULL,
    gold_source_primary VARCHAR(50),
    gold_source_secondary VARCHAR(50),
    fx_source_primary VARCHAR(50),
    fx_source_secondary VARCHAR(50),
    verification_mode VARCHAR(20) NOT NULL DEFAULT 'single_source',
    gold_reading_timestamp INT UNSIGNED,
    fx_reading_timestamp INT UNSIGNED,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_prices_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS daily_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    spot_usd_per_oz DECIMAL(12,4) NOT NULL,
    usd_lkr DECIMAL(12,4) NOT NULL,
    price_24k_gram DECIMAL(12,2) NOT NULL,
    price_22k_gram DECIMAL(12,2) NOT NULL,
    price_21k_gram DECIMAL(12,2) NOT NULL,
    price_18k_gram DECIMAL(12,2) NOT NULL,
    is_imported TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS update_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    status VARCHAR(20) NOT NULL,
    reason VARCHAR(100),
    details_json TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_update_log_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pending_changes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payload_json TEXT NOT NULL,
    reason VARCHAR(100) NOT NULL,
    resolved TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS price_alerts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    channel_type VARCHAR(20) NOT NULL,
    destination VARCHAR(190) NOT NULL,
    karat TINYINT UNSIGNED NOT NULL DEFAULT 22,
    threshold_type VARCHAR(20) NOT NULL,
    threshold_value DECIMAL(12,2) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS news_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    source VARCHAR(100),
    published_at DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS scheduler_state (
    id TINYINT UNSIGNED PRIMARY KEY,
    last_price_attempt_at DATETIME NULL,
    last_price_success_at DATETIME NULL,
    last_news_attempt_at DATETIME NULL,
    last_cleanup_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO scheduler_state (id) VALUES (1);
