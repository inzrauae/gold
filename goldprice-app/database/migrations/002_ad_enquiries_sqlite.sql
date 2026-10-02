CREATE TABLE IF NOT EXISTS ad_enquiries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT,
    company TEXT,
    enquiry_type TEXT NOT NULL,
    budget TEXT,
    message TEXT NOT NULL,
    ip_hash TEXT,
    status TEXT NOT NULL DEFAULT 'new',
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_ad_enquiries_created ON ad_enquiries (created_at);
