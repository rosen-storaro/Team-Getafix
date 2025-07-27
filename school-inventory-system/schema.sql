-- School Inventory Management System Database Schema
-- Version: 1.0
-- Date: 26 July 2025

-- =============================================
-- AUTH_DB Schema
-- =============================================
USE auth_db;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(32) UNIQUE NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    email VARCHAR(100) UNIQUE,
    password_hash CHAR(60),
    google_id VARCHAR(60) NULL,
    role_id INT DEFAULT 1,
    status ENUM('Pending','Active','Disabled') DEFAULT 'Pending',
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    must_change_password BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Insert default roles
INSERT INTO roles (name, description) VALUES 
('User', 'Standard user with basic access'),
('Admin', 'Administrator with management privileges'),
('Super-admin', 'Super administrator with full system access');

-- Insert default super admin (password: pass123!@#)
INSERT INTO users (username, email, password_hash, role_id, status, first_name, last_name, must_change_password) VALUES 
('superadmin', 'admin@school.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 'Active', 'Super', 'Admin', TRUE);

-- =============================================
-- INVENTORY_DB Schema
-- =============================================
USE inventory_db;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    category_id INT,
    serial_number VARCHAR(80),
    quantity INT DEFAULT 1,
    low_stock_threshold INT DEFAULT 3,
    status ENUM('Available','Checked Out','Reserved','Under Repair','Lost/Stolen','Retired') DEFAULT 'Available',
    condition_notes VARCHAR(255),
    sensitive_level ENUM('No','Sensitive','High Value') DEFAULT 'No',
    location_id INT,
    photo_path VARCHAR(255),
    description TEXT,
    purchase_date DATE,
    purchase_price DECIMAL(10,2),
    warranty_expiry DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (location_id) REFERENCES locations(id)
);

CREATE TABLE borrow_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT,
    user_id INT,
    quantity INT DEFAULT 1,
    date_from DATETIME,
    date_to DATETIME,
    purpose VARCHAR(255),
    status ENUM('Pending','Approved','Declined','Returned','Overdue') DEFAULT 'Pending',
    approved_by INT NULL,
    approved_at DATETIME NULL,
    declined_reason VARCHAR(255),
    returned_at DATETIME NULL,
    return_condition VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_dates (date_from, date_to)
);

CREATE TABLE item_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT,
    user_id INT,
    action ENUM('Created','Updated','Borrowed','Returned','Repaired','Retired') NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id),
    INDEX idx_item_id (item_id),
    INDEX idx_action (action)
);

-- Insert default categories
INSERT INTO categories (name, description) VALUES 
('Electronics', 'Electronic devices and equipment'),
('Computers', 'Computers, laptops, and accessories'),
('Audio/Visual', 'Projectors, speakers, and AV equipment'),
('Office Supplies', 'Stationery and office materials'),
('Tools', 'Maintenance and repair tools'),
('Furniture', 'Desks, chairs, and classroom furniture');

-- Insert default locations
INSERT INTO locations (name, description) VALUES 
('Main Office', 'Administrative office'),
('Computer Lab', 'Computer laboratory'),
('Library', 'School library'),
('Classroom A', 'Classroom A'),
('Classroom B', 'Classroom B'),
('Storage Room', 'Equipment storage room');

-- =============================================
-- REPORTS_DB Schema
-- =============================================
USE reports_db;

CREATE TABLE analytics_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metric_type ENUM('most_borrowed','requests_per_month','low_stock','user_activity') NOT NULL,
    metric_data JSON,
    period_start DATE,
    period_end DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_metric_type (metric_type),
    INDEX idx_period (period_start, period_end)
);

CREATE TABLE export_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    export_type ENUM('CSV','PDF') NOT NULL,
    file_path VARCHAR(255),
    filters JSON,
    record_count INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_export_type (export_type)
);

CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description VARCHAR(255),
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES 
('site_name', 'School Inventory Management System', 'Name of the application'),
('site_logo', '', 'Path to site logo'),
('default_low_stock_threshold', '3', 'Default low stock threshold for new items'),
('max_borrow_days', '30', 'Maximum number of days an item can be borrowed'),
('require_approval_sensitive', '1', 'Require super-admin approval for sensitive items'),
('enable_qr_codes', '1', 'Enable QR code generation'),
('enable_google_docs_viewer', '1', 'Enable Google Docs viewer for documents'),
('maintenance_mode', '0', 'Enable maintenance mode');

