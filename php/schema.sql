-- schema.sql — MySQL schema for Zoya Ventures (ported verbatim from src/server/db.ts MIGRATIONS + ALTER_MIGRATIONS)
-- Target: MySQL 8.x, utf8mb4_unicode_ci. Run once against the provident DB.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS roles (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) DEFAULT '',
    avatar VARCHAR(500) DEFAULT '',
    role_id INT NOT NULL DEFAULT 2,
    is_active INT NOT NULL DEFAULT 1,
    last_login_at TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sessions (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(128) NOT NULL UNIQUE,
    expires_at BIGINT NOT NULL,
    user_agent TEXT DEFAULT '',
    ip TEXT DEFAULT '',
    created_at TEXT NOT NULL,
    KEY idx_sessions_user (user_id),
    KEY idx_sessions_expiry (expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS saved_properties (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_ref VARCHAR(255) NOT NULL,
    property_slug VARCHAR(255) NOT NULL DEFAULT '',
    title VARCHAR(500) DEFAULT '',
    price INT DEFAULT 0,
    thumb VARCHAR(1000) DEFAULT '',
    created_at TEXT NOT NULL,
    UNIQUE (user_id, property_ref),
    KEY idx_saved_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inquiries (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(100) DEFAULT '',
    kind VARCHAR(50) NOT NULL DEFAULT 'property',
    property_ref VARCHAR(255) DEFAULT '',
    property_slug VARCHAR(255) DEFAULT '',
    message TEXT DEFAULT '',
    status VARCHAR(50) NOT NULL DEFAULT 'new',
    created_at TEXT NOT NULL,
    KEY idx_inquiries_user (user_id),
    KEY idx_inquiries_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS viewings (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_ref VARCHAR(255) DEFAULT '',
    property_slug VARCHAR(255) DEFAULT '',
    preferred_date TEXT DEFAULT '',
    time_slot TEXT DEFAULT '',
    notes TEXT DEFAULT '',
    status VARCHAR(50) NOT NULL DEFAULT 'requested',
    created_at TEXT NOT NULL,
    KEY idx_viewings_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(500) NOT NULL,
    body TEXT DEFAULT '',
    type VARCHAR(50) DEFAULT 'info',
    `read` INT NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL,
    KEY idx_notifications_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS amenities (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS properties (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) NOT NULL UNIQUE,
    title VARCHAR(500) NOT NULL,
    category VARCHAR(100) DEFAULT '',
    property_type VARCHAR(100) DEFAULT 'apartment',
    transaction_type VARCHAR(20) DEFAULT 'buy',
    status VARCHAR(50) DEFAULT 'ready',
    price BIGINT DEFAULT 0,
    price_qualifier VARCHAR(20) DEFAULT 'AED',
    community VARCHAR(255) DEFAULT '',
    developer VARCHAR(255) DEFAULT '',
    location VARCHAR(500) DEFAULT '',
    latitude DOUBLE,
    longitude DOUBLE,
    display_address VARCHAR(1000) DEFAULT '',
    bedroom INT DEFAULT 0,
    bathroom INT DEFAULT 0,
    area_sqft INT DEFAULT 0,
    plot_size INT DEFAULT 0,
    parking INT DEFAULT 0,
    furnished VARCHAR(100) DEFAULT '',
    completion_status VARCHAR(100) DEFAULT '',
    year_built INT,
    introtext TEXT DEFAULT '',
    long_description MEDIUMTEXT DEFAULT '',
    featured INT NOT NULL DEFAULT 0,
    published INT NOT NULL DEFAULT 1,
    created_by INT,
    created_at TEXT NOT NULL,
    updated_at TEXT,
    KEY idx_properties_status (status),
    KEY idx_properties_featured (featured),
    KEY idx_properties_transaction (transaction_type),
    FOREIGN KEY (created_by) REFERENCES users(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS property_media (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    kind VARCHAR(20) NOT NULL DEFAULT 'image',
    url VARCHAR(1000) NOT NULL,
    is_featured INT NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    KEY idx_property_media (property_id),
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS property_amenities (
    property_id INT NOT NULL,
    amenity_id INT NOT NULL,
    PRIMARY KEY (property_id, amenity_id),
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    icon VARCHAR(1000) DEFAULT '',
    banner_image VARCHAR(1000) DEFAULT '',
    description TEXT DEFAULT '',
    rich_content MEDIUMTEXT DEFAULT '',
    gallery TEXT DEFAULT '[]',
    seo_title VARCHAR(500) DEFAULT '',
    seo_description VARCHAR(1000) DEFAULT '',
    published INT NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agents (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    role VARCHAR(255) DEFAULT '',
    phone VARCHAR(100) DEFAULT '',
    email VARCHAR(255) DEFAULT '',
    languages TEXT DEFAULT '[]',
    specialties TEXT DEFAULT '[]',
    img VARCHAR(1000) DEFAULT '',
    bio TEXT DEFAULT '',
    brn_number VARCHAR(100) DEFAULT '',
    published INT NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS developers (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    region VARCHAR(255) DEFAULT '',
    founded INT,
    deliveries INT DEFAULT 0,
    img VARCHAR(1000) DEFAULT '',
    description TEXT DEFAULT '',
    published INT NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS communities (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    region VARCHAR(255) DEFAULT '',
    published INT NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    type VARCHAR(50) DEFAULT '',
    sort INT NOT NULL DEFAULT 0
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS testimonials (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    author VARCHAR(255) NOT NULL,
    role VARCHAR(255) DEFAULT '',
    content TEXT DEFAULT '',
    rating INT NOT NULL DEFAULT 5,
    img VARCHAR(1000) DEFAULT '',
    published INT NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS faqs (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(1000) NOT NULL,
    answer TEXT DEFAULT '',
    category VARCHAR(255) DEFAULT '',
    sort INT NOT NULL DEFAULT 0,
    published INT NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homepage_content (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(191) NOT NULL UNIQUE,
    value MEDIUMTEXT DEFAULT ''
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_info (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(191) NOT NULL UNIQUE,
    value TEXT DEFAULT ''
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS page_content (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(191) NOT NULL UNIQUE,
    value MEDIUMTEXT DEFAULT ''
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jobs (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    location VARCHAR(255) DEFAULT '',
    summary TEXT DEFAULT '',
    job_details MEDIUMTEXT DEFAULT '',
    published INT NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_library (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(1000) NOT NULL,
    kind VARCHAR(20) DEFAULT 'image',
    alt VARCHAR(500) DEFAULT '',
    created_at TEXT NOT NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_addresses (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address_line1 VARCHAR(500) DEFAULT '',
    address_line2 VARCHAR(500) DEFAULT '',
    town_city VARCHAR(255) DEFAULT '',
    postcode VARCHAR(50) DEFAULT '',
    country VARCHAR(100) DEFAULT '',
    is_primary INT NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL,
    updated_at TEXT,
    KEY idx_user_addresses_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subscribe_news INT NOT NULL DEFAULT 1,
    email_notifications INT NOT NULL DEFAULT 1,
    property_alerts INT NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT,
    KEY idx_notification_preferences_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_updates (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    created_at TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS account_deletion_logs (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reason TEXT DEFAULT '',
    created_at TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) NOT NULL UNIQUE,
    title VARCHAR(500) NOT NULL,
    category VARCHAR(100) DEFAULT 'new-project',
    status VARCHAR(100) DEFAULT '',
    price BIGINT DEFAULT 0,
    currency VARCHAR(20) DEFAULT 'AED',
    community VARCHAR(255) DEFAULT '',
    developer VARCHAR(255) DEFAULT '',
    building_type TEXT DEFAULT '[]',
    department VARCHAR(255) DEFAULT '',
    bedrooms_min INT DEFAULT 0,
    bedrooms_max INT DEFAULT 0,
    display_address VARCHAR(1000) DEFAULT '',
    about MEDIUMTEXT DEFAULT '',
    images TEXT DEFAULT '[]',
    amenities TEXT DEFAULT '[]',
    banner_image VARCHAR(1000) DEFAULT '',
    completion_year INT,
    published INT NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT,
    KEY idx_projects_status (status)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_details (
    slug VARCHAR(255) NOT NULL PRIMARY KEY,
    data MEDIUMTEXT NOT NULL,
    updated_at TEXT NOT NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ALTER_MIGRATIONS (conditional adds)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'first_name');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE users ADD COLUMN first_name VARCHAR(255) DEFAULT ''''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'surname');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE users ADD COLUMN surname VARCHAR(255) DEFAULT ''''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'properties' AND COLUMN_NAME = 'agent_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE properties ADD COLUMN agent_id INT, ADD CONSTRAINT fk_properties_agent FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;