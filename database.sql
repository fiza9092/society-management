-- Society Management System Database
CREATE DATABASE IF NOT EXISTS society_management;
USE society_management;

-- Admins table
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    reset_token VARCHAR(255),
    reset_expires DATETIME,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Residents table
CREATE TABLE residents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    apartment_number VARCHAR(20) UNIQUE NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(15),
    profile_pic VARCHAR(255),
    reset_token VARCHAR(255),
    reset_expires DATETIME,
    last_login DATETIME,
    status ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Notices table
CREATE TABLE notices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('general', 'maintenance', 'event', 'emergency') DEFAULT 'general',
    posted_by INT,
    posted_by_type ENUM('admin', 'resident') DEFAULT 'admin',
    attachments VARCHAR(255),
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    expires_at DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Complaints table
CREATE TABLE complaints (
    id INT PRIMARY KEY AUTO_INCREMENT,
    resident_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('maintenance', 'noise', 'cleanliness', 'security', 'other') DEFAULT 'other',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'resolved', 'rejected') DEFAULT 'pending',
    admin_comments TEXT,
    attachments VARCHAR(255),
    resolved_by INT,
    resolved_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Bills table
CREATE TABLE bills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    resident_id INT NOT NULL,
    bill_number VARCHAR(50) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) GENERATED ALWAYS AS (amount + tax) STORED,
    bill_type ENUM('maintenance', 'water', 'electricity', 'other') DEFAULT 'maintenance',
    month INT NOT NULL,
    year INT NOT NULL,
    description TEXT,
    due_date DATE NOT NULL,
    status ENUM('pending', 'paid', 'overdue', 'cancelled') DEFAULT 'pending',
    late_fee DECIMAL(10,2) DEFAULT 0.00,
    payment_date DATETIME,
    payment_method ENUM('cash', 'card', 'bank_transfer', 'online') NULL,
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    INDEX idx_resident_bills (resident_id, status),
    INDEX idx_due_date (due_date)
);

-- Payments table
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bill_id INT NOT NULL,
    resident_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'bank_transfer', 'online') NOT NULL,
    transaction_id VARCHAR(100),
    payment_status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    receipt_number VARCHAR(50) UNIQUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE
);

-- Activity logs table
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    user_type ENUM('admin', 'resident'),
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_activity (user_id, user_type)
);

-- Insert default admin
INSERT INTO admins (username, email, password, full_name, role) VALUES 
('admin', 'admin@society.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Admin', 'super_admin');

-- Insert sample residents
INSERT INTO residents (username, email, password, full_name, apartment_number, phone) VALUES
('john.doe', 'john@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'A-101', '9876543210'),
('jane.smith', 'jane@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 'A-102', '9876543211'),
('bob.wilson', 'bob@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Wilson', 'B-201', '9876543212');

-- Insert sample notices
INSERT INTO notices (title, content, category, priority, posted_by) VALUES
('Water Shutdown Notice', 'Water supply will be shut down for maintenance on Sunday, 10 AM to 2 PM.', 'maintenance', 'high', 1),
('Annual Society Meeting', 'Annual general meeting will be held on 15th March at community hall.', 'event', 'medium', 1),
('Festival Celebration', 'Join us for Diwali celebration this Saturday at 7 PM.', 'event', 'medium', 1);

-- Insert sample bills
INSERT INTO bills (resident_id, bill_number, amount, month, year, due_date, bill_type) VALUES
(1, 'BILL-2026-001', 2500.00, 2, 2026, '2026-03-10', 'maintenance'),
(2, 'BILL-2026-002', 2500.00, 2, 2026, '2026-03-10', 'maintenance'),
(3, 'BILL-2026-003', 3200.00, 2, 2026, '2026-03-15', 'maintenance');

-- Insert sample complaints
INSERT INTO complaints (resident_id, title, description, category, priority) VALUES
(1, 'Broken Lift', 'The lift in Block A is not working since morning.', 'maintenance', 'high'),
(2, 'Noise Complaint', 'Loud music from neighbor after 11 PM.', 'noise', 'medium');