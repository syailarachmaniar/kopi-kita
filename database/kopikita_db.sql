-- ============================================================
-- KopiKita - Coffee Shop Management System
-- Database : kopikita_db  (MySQL / MariaDB, utf8mb4, InnoDB)
-- Import   : phpMyAdmin > Import > pilih file ini
-- Akun     : admin/admin123 | kasir/kasir123 | pelanggan/pelanggan123
-- ============================================================
CREATE DATABASE IF NOT EXISTS kopikita_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kopikita_db;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS newsletter_subscribers, gallery_images, sales, order_details, orders, products, categories, customers, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','cashier','customer') NOT NULL DEFAULT 'customer',
  avatar VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE customers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(25) NULL,
  email VARCHAR(150) NULL,
  address TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_customers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  slug VARCHAR(90) NOT NULL UNIQUE,
  description TEXT NULL,
  icon VARCHAR(50) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  category_id BIGINT UNSIGNED NOT NULL,
  sku VARCHAR(40) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  description TEXT NULL,
  price DECIMAL(12,2) NOT NULL,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  min_stock INT UNSIGNED NOT NULL DEFAULT 10,
  image VARCHAR(255) NULL,
  has_size_option TINYINT(1) NOT NULL DEFAULT 1,
  is_best_seller TINYINT(1) NOT NULL DEFAULT 0,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(30) NOT NULL UNIQUE,
  customer_id BIGINT UNSIGNED NOT NULL,
  processed_by BIGINT UNSIGNED NULL,
  order_channel ENUM('online','cashier') NOT NULL DEFAULT 'online',
  order_type ENUM('dine_in','takeaway') NOT NULL,
  table_number VARCHAR(10) NULL,
  order_status ENUM('pending','confirmed','preparing','ready','completed','cancelled') NOT NULL DEFAULT 'pending',
  subtotal DECIMAL(12,2) NOT NULL,
  discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(12,2) NOT NULL,
  notes TEXT NULL,
  cancel_reason VARCHAR(255) NULL,
  stock_deducted TINYINT(1) NOT NULL DEFAULT 0,
  ordered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_orders_status (order_status),
  INDEX idx_orders_ordered_at (ordered_at),
  CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON UPDATE CASCADE,
  CONSTRAINT fk_orders_processed FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_details (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  product_name VARCHAR(120) NOT NULL,
  size ENUM('regular','large') NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  line_total DECIMAL(12,2) NOT NULL,
  item_notes TEXT NULL,
  CONSTRAINT chk_qty CHECK (quantity > 0),
  CONSTRAINT fk_details_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_details_product FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE sales (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL UNIQUE,
  invoice_number VARCHAR(30) NOT NULL UNIQUE,
  cashier_id BIGINT UNSIGNED NULL,
  payment_method ENUM('cash','qris','bank_transfer') NOT NULL,
  amount_due DECIMAL(12,2) NOT NULL,
  amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0,
  change_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  payment_status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  reference_number VARCHAR(100) NULL,
  paid_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_sales_status (payment_status),
  CONSTRAINT fk_sales_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_sales_cashier FOREIGN KEY (cashier_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE gallery_images (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(120) NOT NULL,
  collection ENUM('coffee','drinks','food','ambience','desserts') NOT NULL,
  image VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE newsletter_subscribers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  subscribed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===================== DATA AWAL =====================
INSERT INTO users (id,name,username,email,password,role) VALUES
(1,'Administrator','admin','admin@kopikita.test','$2y$10$uYVtapDF8yfkP50YMsnR2eaD9cI05oQxblHxghYFzz.utJTJ3Y2WC','admin'),
(2,'Kasir Utama','kasir','kasir@kopikita.test','$2y$10$mgtoWt4Y/YbSf.nTBE7HGenbsUpelbM1gXPkjs6vCMH9ghwqHxU/u','cashier'),
(3,'Pelanggan Contoh','pelanggan','pelanggan@kopikita.test','$2y$10$9rjI3wSU9cyPj.g6f1/FQu5UYnaqOCQxZDxZbTcO7FcHPvYKUren2','customer'),
(4,'Budi Santoso','budi','budi@kopikita.test','$2y$10$9rjI3wSU9cyPj.g6f1/FQu5UYnaqOCQxZDxZbTcO7FcHPvYKUren2','customer');

INSERT INTO customers (id,user_id,name,phone,email) VALUES
(1,NULL,'Pelanggan Umum',NULL,NULL),
(2,3,'Pelanggan Contoh','081234567890','pelanggan@kopikita.test'),
(3,4,'Budi Santoso','081298765432','budi@kopikita.test');

INSERT INTO categories (id,name,slug,description,icon,sort_order) VALUES
(1,'Coffee','coffee','Kopi pilihan dari biji lokal yang disangrai segar setiap hari.','coffee',1),
(2,'Non-Coffee','non-coffee','Minuman segar tanpa kopi: matcha, cokelat, teh, dan lainnya.','leaf',2),
(3,'Food & Pastry','food-pastry','Pastry dan kue lembut sebagai teman minum kopi.','cake',3),
(4,'Signature Drinks','signature-drinks','Racikan khas KopiKita yang hanya ada di sini.','star',4);

INSERT INTO products (id,category_id,sku,name,slug,description,price,stock,min_stock,image,has_size_option,is_best_seller) VALUES
(1,1,'CF-001','Espresso','espresso','Shot espresso pekat dengan crema tebal dan aroma karamel.',18000,60,10,'espresso.svg',1,0),
(2,1,'CF-002','Americano','americano','Espresso dengan air panas, bersih dan menyegarkan.',22000,60,10,'americano.svg',1,0),
(3,1,'CF-003','Cappuccino','cappuccino','Espresso, susu steam, dan foam tebal yang lembut.',27000,50,10,'cappuccino.svg',1,0),
(4,1,'CF-004','Caffè Latte','latte','Perpaduan espresso dan susu creamy dengan latte art.',28000,50,10,'latte.svg',1,0),
(5,1,'CF-005','Kopi Susu Aren','kopi-susu-aren','Kopi susu dengan gula aren asli, manis yang pas.',25000,80,15,'kopi-susu-aren.svg',1,1),
(6,1,'CF-006','Mocha','mocha','Espresso, cokelat, dan susu dalam satu cangkir hangat.',30000,45,10,'mocha.svg',1,0),
(7,2,'NC-001','Matcha Latte','matcha-latte','Matcha premium dengan susu segar, lembut dan earthy.',28000,50,10,'matcha-latte.svg',1,1),
(8,2,'NC-002','Chocolate','chocolate','Cokelat pekat dengan susu, favorit semua usia.',26000,50,10,'chocolate.svg',1,0),
(9,2,'NC-003','Red Velvet Latte','red-velvet','Minuman red velvet dengan topping cream cheese tipis.',28000,40,10,'red-velvet.svg',1,0),
(10,2,'NC-004','Lemon Tea','lemon-tea','Teh dingin dengan perasan lemon segar.',18000,70,15,'lemon-tea.svg',1,0),
(11,2,'NC-005','Taro Latte','taro-latte','Taro creamy dengan aroma manis yang khas.',27000,40,10,'taro-latte.svg',1,0),
(12,3,'FP-001','Butter Croissant','butter-croissant','Croissant berlapis renyah dengan mentega Perancis.',18000,30,8,'butter-croissant.svg',0,1),
(13,3,'FP-002','Cheesecake','cheesecake','Cheesecake lembut dengan dasar biskuit gurih.',32000,8,8,'cheesecake.svg',0,0),
(14,3,'FP-003','Tiramisu Slice','tiramisu','Tiramisu klasik dengan kopi dan kakao.',35000,6,8,'tiramisu.svg',0,1),
(15,3,'FP-004','Banana Bread','banana-bread','Roti pisang hangat, lembut dan wangi.',20000,25,8,'banana-bread.svg',0,0),
(16,3,'FP-005','Pain au Chocolat','pain-au-chocolat','Pastry berisi cokelat leleh yang renyah di luar.',22000,28,8,'pain-au-chocolat.svg',0,0),
(17,4,'SG-001','KopiKita Caramel Cloud','caramel-cloud','Signature kami: espresso, cold foam karamel, dan taburan sea salt.',32000,50,10,'caramel-cloud.svg',1,1),
(18,4,'SG-002','Orange Espresso Tonic','orange-espresso-tonic','Espresso, tonic, dan jeruk segar yang menyegarkan.',30000,45,10,'orange-espresso-tonic.svg',1,1),
(19,4,'SG-003','Hazelnut Velvet','hazelnut-velvet','Latte hazelnut dengan foam sutra.',33000,40,10,'hazelnut-velvet.svg',1,0),
(20,4,'SG-004','Coconut Cold Brew','coconut-cold-brew','Cold brew 12 jam dengan air kelapa muda.',31000,35,10,'coconut-cold-brew.svg',1,0),
(21,4,'SG-005','Brown Sugar Oat Latte','brown-sugar-oat','Susu oat, gula cokelat, dan espresso.',32000,40,10,'brown-sugar-oat.svg',1,0);

INSERT INTO gallery_images (title,collection,image,sort_order) VALUES
('Latte Art Pagi','coffee','coffee-1.svg',1),
('Espresso Pekat','coffee','coffee-2.svg',2),
('Cappuccino Hangat','coffee','coffee-3.svg',3),
('Single Origin','coffee','coffee-4.svg',4),
('Kopi Susu Aren','drinks','drinks-1.svg',1),
('Matcha Hijau','drinks','drinks-2.svg',2),
('Lemon Tea Segar','drinks','drinks-3.svg',3),
('Caramel Cloud','drinks','drinks-4.svg',4),
('Croissant Renyah','food','food-1.svg',1),
('Pain au Chocolat','food','food-2.svg',2),
('Pastry Pagi','food','food-3.svg',3),
('Roti Panggang','food','food-4.svg',4),
('Sudut Hangat','ambience','ambience-1.svg',1),
('Lampu Gantung','ambience','ambience-2.svg',2),
('Meja Kayu','ambience','ambience-3.svg',3),
('Ruang Santai','ambience','ambience-4.svg',4),
('Cheesecake','desserts','desserts-1.svg',1),
('Tiramisu','desserts','desserts-2.svg',2),
('Banana Bread','desserts','desserts-3.svg',3),
('Choco Cake','desserts','desserts-4.svg',4);

INSERT INTO newsletter_subscribers (email,subscribed_at) VALUES ('andi@example.com',NOW()),('sari@example.com',NOW());

-- Data contoh transaksi (agar dashboard dan laporan langsung terisi)
INSERT INTO orders (id,order_code,customer_id,processed_by,order_channel,order_type,table_number,order_status,subtotal,discount_amount,total_amount,notes,cancel_reason,stock_deducted,ordered_at,completed_at) VALUES
(1,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '17:04' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(1,4,'0')),1,2,'online','dine_in','9','completed',124000,5000,119000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '17:04' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '17:04' HOUR_MINUTE)),
(2,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '14:02' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(2,4,'0')),1,2,'online','dine_in','9','completed',92000,5000,87000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '14:02' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '14:02' HOUR_MINUTE)),
(3,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 8 DAY),INTERVAL '20:29' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(3,4,'0')),1,2,'cashier','dine_in','2','completed',204000,5000,199000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 8 DAY),INTERVAL '20:29' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 8 DAY),INTERVAL '20:29' HOUR_MINUTE)),
(4,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 7 DAY),INTERVAL '19:44' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(4,4,'0')),1,2,'online','takeaway',NULL,'completed',86000,5000,81000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 7 DAY),INTERVAL '19:44' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 7 DAY),INTERVAL '19:44' HOUR_MINUTE)),
(5,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '13:01' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(5,4,'0')),2,2,'online','takeaway',NULL,'completed',230000,5000,225000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '13:01' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '13:01' HOUR_MINUTE)),
(6,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '11:49' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(6,4,'0')),1,2,'cashier','takeaway',NULL,'completed',51000,0,51000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '11:49' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '11:49' HOUR_MINUTE)),
(7,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 5 DAY),INTERVAL '14:58' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(7,4,'0')),1,2,'online','takeaway',NULL,'completed',60000,0,60000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 5 DAY),INTERVAL '14:58' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 5 DAY),INTERVAL '14:58' HOUR_MINUTE)),
(8,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '16:17' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(8,4,'0')),1,2,'cashier','dine_in','7','completed',56000,0,56000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '16:17' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '16:17' HOUR_MINUTE)),
(9,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:09' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(9,4,'0')),2,2,'online','dine_in','11','completed',145000,5000,140000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:09' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:09' HOUR_MINUTE)),
(10,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:16' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(10,4,'0')),2,2,'cashier','takeaway',NULL,'completed',54000,0,54000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:16' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:16' HOUR_MINUTE)),
(11,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 3 DAY),INTERVAL '13:39' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(11,4,'0')),2,2,'cashier','takeaway',NULL,'completed',84000,5000,79000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 3 DAY),INTERVAL '13:39' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 3 DAY),INTERVAL '13:39' HOUR_MINUTE)),
(12,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '18:43' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(12,4,'0')),3,2,'online','dine_in','8','completed',105000,5000,100000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '18:43' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '18:43' HOUR_MINUTE)),
(13,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '14:03' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(13,4,'0')),1,2,'online','dine_in','2','completed',207000,5000,202000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '14:03' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '14:03' HOUR_MINUTE)),
(14,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:21' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(14,4,'0')),1,2,'online','dine_in','2','completed',18000,0,18000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:21' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:21' HOUR_MINUTE)),
(15,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '9:23' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(15,4,'0')),3,2,'cashier','dine_in','2','completed',96000,5000,91000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '9:23' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '9:23' HOUR_MINUTE)),
(16,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '18:16' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(16,4,'0')),3,2,'cashier','takeaway',NULL,'completed',32000,0,32000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '18:16' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '18:16' HOUR_MINUTE)),
(17,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '12:05' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(17,4,'0')),2,2,'online','dine_in','2','completed',115000,5000,110000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '12:05' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '12:05' HOUR_MINUTE)),
(18,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '11:33' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(18,4,'0')),3,2,'online','takeaway',NULL,'completed',147000,5000,142000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '11:33' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '11:33' HOUR_MINUTE)),
(19,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '20:33' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(19,4,'0')),2,NULL,'online','takeaway',NULL,'pending',35000,0,35000,NULL,NULL,0,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '20:33' HOUR_MINUTE),NULL),
(20,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '20:14' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(20,4,'0')),3,2,'online','takeaway',NULL,'confirmed',206000,5000,201000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '20:14' HOUR_MINUTE),NULL),
(21,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '16:31' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(21,4,'0')),1,2,'cashier','takeaway',NULL,'preparing',120000,5000,115000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '16:31' HOUR_MINUTE),NULL),
(22,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '13:28' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(22,4,'0')),2,2,'online','takeaway',NULL,'ready',162000,5000,157000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '13:28' HOUR_MINUTE),NULL),
(23,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '13:13' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(23,4,'0')),3,2,'online','takeaway',NULL,'cancelled',52000,0,52000,NULL,'Stok habis, dibatalkan pelanggan',0,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '13:13' HOUR_MINUTE),NULL);

INSERT INTO order_details (order_id,product_id,product_name,size,unit_price,quantity,line_total) VALUES
(1,13,'Cheesecake',NULL,32000,2,64000),
(1,18,'Orange Espresso Tonic','regular',30000,2,60000),
(2,4,'Caffè Latte','regular',28000,1,28000),
(2,13,'Cheesecake',NULL,32000,2,64000),
(3,6,'Mocha','regular',30000,2,60000),
(3,18,'Orange Espresso Tonic','regular',30000,3,90000),
(3,12,'Butter Croissant',NULL,18000,3,54000),
(4,12,'Butter Croissant',NULL,18000,3,54000),
(4,17,'KopiKita Caramel Cloud','regular',32000,1,32000),
(5,17,'KopiKita Caramel Cloud','large',37000,2,74000),
(5,5,'Kopi Susu Aren','large',30000,2,60000),
(5,13,'Cheesecake',NULL,32000,3,96000),
(6,4,'Caffè Latte','large',33000,1,33000),
(6,12,'Butter Croissant',NULL,18000,1,18000),
(7,18,'Orange Espresso Tonic','regular',30000,2,60000),
(8,4,'Caffè Latte','regular',28000,2,56000),
(9,5,'Kopi Susu Aren','large',30000,2,60000),
(9,5,'Kopi Susu Aren','large',30000,2,60000),
(9,5,'Kopi Susu Aren','regular',25000,1,25000),
(10,1,'Espresso','regular',18000,3,54000),
(11,4,'Caffè Latte','regular',28000,3,84000),
(12,18,'Orange Espresso Tonic','large',35000,3,105000),
(13,6,'Mocha','large',35000,2,70000),
(13,7,'Matcha Latte','regular',28000,2,56000),
(13,3,'Cappuccino','regular',27000,3,81000),
(14,12,'Butter Croissant',NULL,18000,1,18000),
(15,17,'KopiKita Caramel Cloud','regular',32000,3,96000),
(16,17,'KopiKita Caramel Cloud','regular',32000,1,32000),
(17,5,'Kopi Susu Aren','regular',25000,1,25000),
(17,3,'Cappuccino','regular',27000,2,54000),
(17,12,'Butter Croissant',NULL,18000,2,36000),
(18,5,'Kopi Susu Aren','large',30000,2,60000),
(18,12,'Butter Croissant',NULL,18000,3,54000),
(18,4,'Caffè Latte','large',33000,1,33000),
(19,18,'Orange Espresso Tonic','large',35000,1,35000),
(20,8,'Chocolate','regular',26000,3,78000),
(20,8,'Chocolate','regular',26000,3,78000),
(20,5,'Kopi Susu Aren','regular',25000,2,50000),
(21,5,'Kopi Susu Aren','large',30000,1,30000),
(21,6,'Mocha','regular',30000,2,60000),
(21,18,'Orange Espresso Tonic','regular',30000,1,30000),
(22,1,'Espresso','regular',18000,2,36000),
(22,12,'Butter Croissant',NULL,18000,2,36000),
(22,5,'Kopi Susu Aren','large',30000,3,90000),
(23,3,'Cappuccino','regular',27000,1,27000),
(23,5,'Kopi Susu Aren','regular',25000,1,25000);

INSERT INTO sales (order_id,invoice_number,cashier_id,payment_method,amount_due,amount_paid,change_amount,payment_status,reference_number,paid_at) VALUES
(1,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '17:04' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(1,4,'0')),2,'cash',119000,120000,1000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '17:04' HOUR_MINUTE)),
(2,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '14:02' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(2,4,'0')),2,'cash',87000,90000,3000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '14:02' HOUR_MINUTE)),
(3,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 8 DAY),INTERVAL '20:29' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(3,4,'0')),2,'cash',199000,200000,1000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 8 DAY),INTERVAL '20:29' HOUR_MINUTE)),
(4,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 7 DAY),INTERVAL '19:44' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(4,4,'0')),2,'qris',81000,81000,0,'paid','QR100004',DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 7 DAY),INTERVAL '19:44' HOUR_MINUTE)),
(5,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '13:01' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(5,4,'0')),2,'cash',225000,230000,5000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '13:01' HOUR_MINUTE)),
(6,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '11:49' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(6,4,'0')),2,'cash',51000,60000,9000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '11:49' HOUR_MINUTE)),
(7,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 5 DAY),INTERVAL '14:58' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(7,4,'0')),2,'cash',60000,60000,0,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 5 DAY),INTERVAL '14:58' HOUR_MINUTE)),
(8,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '16:17' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(8,4,'0')),2,'cash',56000,60000,4000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '16:17' HOUR_MINUTE)),
(9,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:09' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(9,4,'0')),2,'qris',140000,140000,0,'paid','QR100009',DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:09' HOUR_MINUTE)),
(10,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:16' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(10,4,'0')),2,'cash',54000,60000,6000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:16' HOUR_MINUTE)),
(11,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 3 DAY),INTERVAL '13:39' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(11,4,'0')),2,'cash',79000,80000,1000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 3 DAY),INTERVAL '13:39' HOUR_MINUTE)),
(12,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '18:43' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(12,4,'0')),2,'cash',100000,100000,0,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '18:43' HOUR_MINUTE)),
(13,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '14:03' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(13,4,'0')),2,'cash',202000,210000,8000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '14:03' HOUR_MINUTE)),
(14,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:21' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(14,4,'0')),2,'cash',18000,20000,2000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:21' HOUR_MINUTE)),
(15,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '9:23' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(15,4,'0')),2,'qris',91000,91000,0,'paid','QR100015',DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '9:23' HOUR_MINUTE)),
(16,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '18:16' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(16,4,'0')),2,'qris',32000,32000,0,'paid','QR100016',DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '18:16' HOUR_MINUTE)),
(17,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '12:05' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(17,4,'0')),2,'cash',110000,110000,0,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '12:05' HOUR_MINUTE)),
(18,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '11:33' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(18,4,'0')),2,'cash',142000,150000,8000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '11:33' HOUR_MINUTE)),
(19,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '20:33' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(19,4,'0')),NULL,'qris',35000,0,0,'pending',NULL,NULL),
(20,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '20:14' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(20,4,'0')),NULL,'cash',201000,0,0,'pending',NULL,NULL),
(21,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '16:31' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(21,4,'0')),2,'cash',115000,120000,5000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '16:31' HOUR_MINUTE)),
(22,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '13:28' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(22,4,'0')),2,'qris',157000,157000,0,'paid','QR100022',DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '13:28' HOUR_MINUTE)),
(23,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '13:13' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(23,4,'0')),NULL,'cash',52000,0,0,'pending',NULL,NULL);
