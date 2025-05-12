-- Create system_settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    currency VARCHAR(10) DEFAULT 'USD',
    date_format VARCHAR(20) DEFAULT 'Y-m-d',
    timezone VARCHAR(50) DEFAULT 'UTC',
    low_stock_threshold INT DEFAULT 10,
    expiry_alert_days INT DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings if table is empty
INSERT INTO system_settings (currency, date_format, timezone, low_stock_threshold, expiry_alert_days)
SELECT 'USD', 'Y-m-d', 'UTC', 10, 30
WHERE NOT EXISTS (SELECT 1 FROM system_settings); 