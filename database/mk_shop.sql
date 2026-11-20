CREATE DATABASE IF NOT EXISTS db_mk_shop
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_mk_shop;

-- 1. ตารางประเภทสินค้า (categories)
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

-- 2. ตารางสินค้า (products) เชื่อมโยงกับ categories
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(150) NOT NULL,
    category_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(150) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(category_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- ข้อมูลประเภทสินค้าเริ่มต้น
INSERT INTO categories (category_name) VALUES
('เนื้อสัตว์'),
('ผัก'),
('เครื่องดื่ม');

-- ข้อมูลสินค้าเริ่มต้น
INSERT INTO products (product_name, category_id, price, stock, image) VALUES
('ชุดรวมหมูชาบู', 1, 299.00, 20, 'shabu_pork.jpg'),
('สันคอหมูสไลด์', 1, 220.00, 25, 'pork_neck.jpg'),
('ชุดรวมมิตรชาบู', 1, 299.00, 18, 'shabu_mix.jpg'),
('ชุดผักรวมสด', 2, 85.00, 30, 'veg_set.jpg'),
('ผักบุ้งและผักกาดขาว', 2, 45.00, 35, 'veg_green.jpg'),
('แตงโมปั่น', 3, 65.00, 24, 'watermelon.jpg'),
('ชาไทย', 3, 45.00, 24, 'thai_tea.jpg'),
('ชาเขียว', 3, 50.00, 24, 'green_tea.jpg'),
('ลาเต้มัทฉะ', 3, 55.00, 30, 'matcha.jpg'),
('น้ำเปล่า', 3, 10.00, 30, 'water.jpg');
