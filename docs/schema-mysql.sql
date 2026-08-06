-- K2-Net — Database Schema (MySQL 8.0)
-- Generated: 6 Agustus 2026
-- Note: Menggunakan Schema Builder Laravel conventions.
--       Semua enum di level aplikasi (PHP), bukan native MySQL ENUM.
--       Primary key: UUID v7 string (K2-{PREFIX}-{uuid7})

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE users (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'pelanggan') NOT NULL DEFAULT 'pelanggan',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT users_email_unique UNIQUE (email),
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: packages
-- ============================================================
CREATE TABLE packages (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    speed VARCHAR(50) NULL,
    price DECIMAL(12, 0) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_packages_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: customers
-- ============================================================
CREATE TABLE customers (
    id VARCHAR(50) PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    whatsapp_number VARCHAR(20) NOT NULL,
    whatsapp_number_full VARCHAR(25) NULL,
    email VARCHAR(255) NULL,
    address TEXT NOT NULL,
    package_id VARCHAR(50) NOT NULL,
    status ENUM('aktif', 'isolir', 'nonaktif') NOT NULL DEFAULT 'aktif',
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT customers_code_unique UNIQUE (code),
    CONSTRAINT customers_user_id_unique UNIQUE (user_id),
    INDEX idx_customers_status (status),
    INDEX idx_customers_package_id (package_id),
    CONSTRAINT fk_customers_user_id FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_customers_package_id FOREIGN KEY (package_id)
        REFERENCES packages(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: invoices
-- ============================================================
CREATE TABLE invoices (
    id VARCHAR(50) PRIMARY KEY,
    invoice_number VARCHAR(100) NOT NULL,
    customer_id VARCHAR(50) NOT NULL,
    billing_period DATE NOT NULL,
    amount DECIMAL(12, 0) NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('belum_bayar', 'menunggu_verifikasi', 'lunas', 'ditolak') NOT NULL DEFAULT 'belum_bayar',
    rejection_reason TEXT NULL,
    issued_at TIMESTAMP NOT NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT invoices_invoice_number_unique UNIQUE (invoice_number),
    INDEX idx_invoices_customer_id (customer_id),
    INDEX idx_invoices_status (status),
    INDEX idx_invoices_billing_period (billing_period),
    INDEX idx_invoices_due_date (due_date),
    CONSTRAINT invoices_customer_billing_unique UNIQUE (customer_id, billing_period),
    CONSTRAINT fk_invoices_customer_id FOREIGN KEY (customer_id)
        REFERENCES customers(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: payment_proofs
-- ============================================================
CREATE TABLE payment_proofs (
    id VARCHAR(50) PRIMARY KEY,
    invoice_id VARCHAR(50) NOT NULL,
    customer_id VARCHAR(50) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NULL,
    file_type VARCHAR(20) NOT NULL,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT payment_proofs_invoice_id_unique UNIQUE (invoice_id),
    INDEX idx_payment_proofs_customer_id (customer_id),
    CONSTRAINT fk_payment_proofs_invoice_id FOREIGN KEY (invoice_id)
        REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_payment_proofs_customer_id FOREIGN KEY (customer_id)
        REFERENCES customers(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: notification_logs
-- ============================================================
CREATE TABLE notification_logs (
    id VARCHAR(50) PRIMARY KEY,
    invoice_id VARCHAR(50) NOT NULL,
    customer_id VARCHAR(50) NOT NULL,
    notification_type ENUM('reminder_h3', 'reminder_h0', 'reminder_h3_late', 'confirmation', 'rejection') NOT NULL,
    channel ENUM('whatsapp', 'email') NOT NULL,
    status ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    failed_at TIMESTAMP NULL,
    error_message TEXT NULL,
    meta JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_notification_logs_invoice_id (invoice_id),
    INDEX idx_notification_logs_customer_id (customer_id),
    INDEX idx_notification_logs_notification_type (notification_type),
    INDEX idx_notification_logs_status (status),
    INDEX idx_notification_logs_sent_at (sent_at),
    CONSTRAINT notification_logs_daily_unique UNIQUE (invoice_id, notification_type, sent_at),
    CONSTRAINT fk_notification_logs_invoice_id FOREIGN KEY (invoice_id)
        REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_notification_logs_customer_id FOREIGN KEY (customer_id)
        REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: audit_logs
-- ============================================================
CREATE TABLE audit_logs (
    id VARCHAR(50) PRIMARY KEY,
    actor_id VARCHAR(50) NULL,
    actor_type VARCHAR(50) NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id VARCHAR(50) NOT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_logs_actor_id (actor_id),
    INDEX idx_audit_logs_entity_type (entity_type),
    INDEX idx_audit_logs_entity_id (entity_id),
    INDEX idx_audit_logs_action (action),
    INDEX idx_audit_logs_created_at (created_at),
    CONSTRAINT fk_audit_logs_actor_id FOREIGN KEY (actor_id)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: system_configurations
-- ============================================================
CREATE TABLE system_configurations (
    id VARCHAR(50) PRIMARY KEY,
    key VARCHAR(100) NOT NULL,
    value TEXT NULL,
    type ENUM('string', 'number', 'boolean', 'json') NOT NULL DEFAULT 'string',
    description TEXT NULL,
    group_name VARCHAR(100) NULL,
    is_editable BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT system_configurations_key_unique UNIQUE (key),
    INDEX idx_system_configurations_group_name (group_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: jwt_blacklist
-- ============================================================
CREATE TABLE jwt_blacklist (
    id VARCHAR(50) PRIMARY KEY,
    token_id VARCHAR(255) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    revoked_by VARCHAR(50) NULL,
    expires_at TIMESTAMP NOT NULL,
    blacklisted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT jwt_blacklist_token_id_unique UNIQUE (token_id),
    CONSTRAINT jwt_blacklist_token_hash_unique UNIQUE (token_hash),
    INDEX idx_jwt_blacklist_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA: Default Admin User
-- Password: admin123 (akan di-hash saat seeding)
-- ============================================================
-- INSERT INTO users (id, name, email, password_hash, role, created_at, updated_at)
-- VALUES (
--     CONCAT('K2-USR-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)),
--     'Kang Dedi',
--     'admin@k2net.local',
--     '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4qEWTHkQQlZ8KHOi', -- bcrypt('admin123')
--     'admin',
--     NOW(),
--     NOW()
-- );

-- ============================================================
-- SEED DATA: Default Packages
-- ============================================================
-- INSERT INTO packages (id, name, speed, price, description, is_active, created_at, updated_at) VALUES
-- (CONCAT('K2-PKG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'Paket Basic', '5 Mbps', 100000, 'Paket internet basic untuk rumah tangga', TRUE, NOW(), NOW()),
-- (CONCAT('K2-PKG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'Paket Standard', '10 Mbps', 150000, 'Paket internet standard untuk rumah tangga', TRUE, NOW(), NOW()),
-- (CONCAT('K2-PKG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'Paket Premium', '20 Mbps', 250000, 'Paket internet premium untuk rumah tangga', TRUE, NOW(), NOW()),
-- (CONCAT('K2-PKG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'Paket Business', '50 Mbps', 500000, 'Paket internet untuk usaha kecil-menengah', TRUE, NOW(), NOW());

-- ============================================================
-- SEED DATA: System Configurations
-- ============================================================
-- INSERT INTO system_configurations (id, key, value, type, description, group_name, is_editable, created_at, updated_at) VALUES
-- (CONCAT('K2-CFG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'invoice_generate_day', '25', 'number', 'Tanggal generate invoice bulanan', 'billing', TRUE, NOW(), NOW()),
-- (CONCAT('K2-CFG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'invoice_due_day', '5', 'number', 'Tanggal jatuh tempo (bulan berikutnya)', 'billing', TRUE, NOW(), NOW()),
-- (CONCAT('K2-CFG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'notification_reminder_days', '[-3,0,3]', 'json', 'Hari-hari pengingat notifikasi', 'notification', TRUE, NOW(), NOW()),
-- (CONCAT('K2-CFG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'upload_max_size_kb', '5120', 'number', 'Maks ukuran upload bukti transfer (KB)', 'general', TRUE, NOW(), NOW()),
-- (CONCAT('K2-CFG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'upload_allowed_types', '["pdf","jpg","jpeg","png"]', 'json', 'Tipe file yang diizinkan untuk upload', 'general', TRUE, NOW(), NOW()),
-- (CONCAT('K2-CFG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'whatsapp_api_url', '', 'string', 'URL API WhatsApp gateway', 'notification', TRUE, NOW(), NOW()),
-- (CONCAT('K2-CFG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'email_from_address', '', 'string', 'Alamat email pengirim notifikasi', 'notification', TRUE, NOW(), NOW()),
-- (CONCAT('K2-CFG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'email_from_name', 'K2-Net', 'string', 'Nama pengirim email', 'notification', TRUE, NOW(), NOW()),
-- (CONCAT('K2-CFG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'company_name', 'K2-Net', 'string', 'Nama perusahaan/brand', 'general', FALSE, NOW(), NOW()),
-- (CONCAT('K2-CFG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'company_address', '', 'string', 'Alamat perusahaan', 'general', TRUE, NOW(), NOW()),
-- (CONCAT('K2-CFG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'company_phone', '', 'string', 'Nomor telepon perusahaan', 'general', TRUE, NOW(), NOW()),
-- (CONCAT('K2-CFG-', SUBSTRING(REPLACE(UUID(), '-', ''), 1, 18)), 'bank_account_info', '', 'json', 'Info rekening bank untuk transfer', 'billing', TRUE, NOW(), NOW());
