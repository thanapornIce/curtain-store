CREATE TABLE IF NOT EXISTS subscribe_emails (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_subscribe_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS contact_messages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- curtain_store database bootstrap
-- Compatible with MariaDB/MySQL (XAMPP)

CREATE DATABASE IF NOT EXISTS curtain_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE curtain_db;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  fullname VARCHAR(100) NULL,
  name VARCHAR(100) NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(20) NULL,
  address TEXT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bookings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_date DATE NOT NULL,
  time_slot ENUM('morning','afternoon') NOT NULL,
  customer_name VARCHAR(100) NOT NULL,
  customer_tel VARCHAR(20) NOT NULL,
  note TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_booking_date (booking_date),
  UNIQUE KEY uq_booking_slot (booking_date, time_slot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ProductID VARCHAR(10) NOT NULL,
  Name VARCHAR(150) NULL,
  Category CHAR(10) NULL,
  Color VARCHAR(100) NULL,
  Pattern VARCHAR(255) NULL,
  Price INT NULL,
  Stock INT NULL,
  Image VARCHAR(255) NULL,
  PRIMARY KEY (ProductID),
  UNIQUE KEY uq_product_id (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cart (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  product_id VARCHAR(10) NULL,
  product_name VARCHAR(150) NOT NULL,
  product_img VARCHAR(255) NULL,
  price DECIMAL(10,2) NOT NULL,
  qty INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_cart_user (user_id),
  KEY idx_cart_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional order tables (if project uses checkout flow)
CREATE TABLE IF NOT EXISTS orders (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  total_amount DECIMAL(10,2) DEFAULT 0.00,
  status ENUM('cart','pending','paid','cancelled') DEFAULT 'cart',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id INT(11) NOT NULL AUTO_INCREMENT,
  order_id INT(11) NOT NULL,
  product_id VARCHAR(10) DEFAULT NULL,
  price DECIMAL(10,2) NOT NULL,
  quantity INT(11) NOT NULL DEFAULT 1,
  subtotal DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_order_items_order (order_id),
  KEY idx_order_items_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Keep name aligned with fullname for old rows
UPDATE users
SET name = COALESCE(NULLIF(name, ''), fullname)
WHERE (name IS NULL OR name = '') AND fullname IS NOT NULL;

-- Seed users (password hash = 12345678)
INSERT INTO users (fullname, name, email, phone, address, password, role)
VALUES
  ('ผู้ดูแลระบบ', 'ผู้ดูแลระบบ', 'admin@curtain.local', '0900000000', 'กรุงเทพมหานคร', '$2y$10$Fe2Pm3lg15FR81A5.vvj4u2.hJScu5kg7yNzSnJptl57whSNda6BO', 'admin'),
  ('ลูกค้าทดสอบ', 'ลูกค้าทดสอบ', 'customer@curtain.local', '0999999999', 'นนทบุรี', '$2y$10$ixNsIQ6SOLDFx94Of4VHQutLOvw3.54jjGqoExJTUA7Fh7S9wf17q', 'customer')
ON DUPLICATE KEY UPDATE
  fullname = VALUES(fullname),
  name = VALUES(name),
  phone = VALUES(phone),
  address = VALUES(address),
  role = VALUES(role);

-- Seed products
INSERT INTO product (ProductID, Name, Category, Color, Pattern, Price, Stock, Image)
VALUES
  ('CUR-001','ม่านจีบ Dimout สีเทาอ่อน','ผ้าม่าน','เทา','เรียบ',590,50,'จีบ.jpg'),
  ('CUR-002','ม่านพับ ผ้าลินินธรรมชาติ','ผ้าม่าน','ครีม','ลินิน',850,30,'พับ.jpg'),
  ('CUR-003','ม่านโปร่ง กรองแสง UV','ผ้าม่าน','ขาว','โปร่ง',450,100,'ตาไก่.jpg'),
  ('CUR-004','ม่านตาไก่สีเบจ กันแสงแดด','ผ้าม่าน','เบจ','เรียบ',320,45,'ตาไก่.jpg'),
  ('CUR-005','ม่านม้วน Blackout','ม่านม้วน','เทาเข้ม','เรียบ',1200,20,'ม้วน.jpg'),
  ('CUR-006','ม่านลอน สไตล์มินิมอล','ผ้าม่าน','ขาวนวล','ลอน',790,60,'ลอน.jpg'),
  ('CUR-007','ม่านไม้ไผ่ ธรรมชาติ','ม่านพิเศษ','น้ำตาล','ธรรมชาติ',1500,15,'รางเทป.jpg'),
  ('CUR-008','ม่านมู่ลี่อลูมิเนียม','มู่ลี่','เงิน','เส้น',990,25,'รางเทป.jpg'),
  ('CUR-009','ม่านมู่ลี่ไม้ PVC','มู่ลี่','ไม้โอ๊ค','เส้น',1100,40,'รางเทป.jpg'),
  ('CUR-010','ผ้าโปร่งสำเร็จรูป','ผ้าม่าน','ขาว','โปร่ง',290,80,'จีบ.jpg')
ON DUPLICATE KEY UPDATE
  Name = VALUES(Name),
  Category = VALUES(Category),
  Color = VALUES(Color),
  Pattern = VALUES(Pattern),
  Price = VALUES(Price),
  Stock = VALUES(Stock),
  Image = VALUES(Image);

-- Seed bookings (safe with unique key)
INSERT INTO bookings (booking_date, time_slot, customer_name, customer_tel, note)
VALUES
  (CURDATE() + INTERVAL 1 DAY, 'morning', 'ลูกค้าตัวอย่าง A', '0811111111', 'วัดหน้างานติดตั้ง'),
  (CURDATE() + INTERVAL 2 DAY, 'afternoon', 'ลูกค้าตัวอย่าง B', '0822222222', 'ชั้น 2 คอนโด')
ON DUPLICATE KEY UPDATE
  customer_name = VALUES(customer_name),
  customer_tel = VALUES(customer_tel),
  note = VALUES(note);
