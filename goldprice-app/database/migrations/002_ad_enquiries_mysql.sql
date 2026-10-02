CREATE TABLE IF NOT EXISTS ad_enquiries (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(40) NULL,
    company VARCHAR(160) NULL,
    enquiry_type VARCHAR(40) NOT NULL,
    budget VARCHAR(40) NULL,
    message TEXT NOT NULL,
    ip_hash CHAR(64) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'new',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ad_enquiries_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
