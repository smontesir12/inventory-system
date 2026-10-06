-- Database Setup for Simple Inventory System

CREATE DATABASE IF NOT EXISTS inventory_db;
USE inventory_db;

-- Users Table (Handles login and roles)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  role ENUM('admin','cashier') NOT NULL DEFAULT 'cashier',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  quantity INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Transactions Table (Stores checkout records)
CREATE TABLE IF NOT EXISTS transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) UNIQUE NOT NULL,
  user_id INT,
  cashier_name VARCHAR(100),
  total DECIMAL(10,2) NOT NULL,
  cash DECIMAL(10,2) NOT NULL,
  change_amount DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sale Items Table (Stores individual items inside a transaction)
CREATE TABLE IF NOT EXISTS sale_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  transaction_id INT NOT NULL,
  product_id INT,
  product_name VARCHAR(255),
  price DECIMAL(10,2),
  quantity INT,
  subtotal DECIMAL(10,2),
  FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE
);

-- Sample Products (Optional)
INSERT INTO products (name, price, quantity) VALUES
('Notebook', 2.50, 100),
('Pen', 1.20, 200),
('Backpack', 25.00, 30),
('Water Bottle', 8.00, 50);

-- Note: Default users (admin/admin123 and cashier/cashier123) 
-- are automatically created by the login.php script the first time you open it.