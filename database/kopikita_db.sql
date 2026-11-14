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
(1,1,'CF-001','Espresso','espresso','Kopi pekat dengan cita rasa kuat',18000,60,10,'Coffe/Espresso.jpg',0,0),
(2,1,'CF-002','Americano','americano','Espresso dengan tambahan air',22000,60,10,'Coffe/Iced Americano.jpg',1,0),
(3,1,'CF-003','Café Latte','cafe-latte','Espresso dengan susu creamy',28000,50,10,'Coffe/Coffe Latte.jpg',1,1),
(4,1,'CF-004','Cappuccino','cappuccino','Kopi susu dengan foam lembut',27000,50,10,'Coffe/Cappuccino.jpg',1,1),
(5,1,'CF-005','Caramel Macchiato','caramel-macchiato','Kopi susu dengan sirup karamel',32000,45,10,'Coffe/Iced Caramel Macchiato.jpg',1,1),
(6,1,'CF-006','Café Mocha','cafe-mocha','Perpaduan kopi dan cokelat',30000,45,10,'Coffe/Mocha Flavored Coffee.jpg',1,0),
(7,1,'CF-007','Vanilla Latte','vanilla-latte','Latte dengan aroma vanilla',30000,45,10,'Coffe/Iced Almond Vanilla Latte.jpg',1,0),
(8,1,'CF-008','Hazelnut Latte','hazelnut-latte','Kopi susu dengan hazelnut',30000,45,10,'Coffe/Hazelnut Iced Coffee.jpg',1,0),
(9,1,'CF-009','Flat White','flat-white','Espresso dengan microfoam lembut',29000,40,10,'Coffe/Flat White.jpg',1,0),
(10,1,'CF-010','Affogato','affogato','Espresso dengan es krim vanilla',33000,30,8,'Coffe/Affogato.jpg',0,0),
(11,2,'NC-001','Matcha Latte','matcha-latte','Teh hijau Jepang dengan susu',28000,50,10,'Non-Coffe/Matcha Latte.jpeg',1,1),
(12,2,'NC-002','Chocolate Milk','chocolate-milk','Cokelat creamy dengan susu',25000,50,10,'Non-Coffe/Chocolate Milk.jpeg',1,0),
(13,2,'NC-003','Taro Latte','taro-latte','Minuman talas dengan susu',26000,45,10,'Non-Coffe/Taro Latte.jpeg',1,0),
(14,2,'NC-004','Red Velvet','red-velvet','Minuman red velvet creamy',27000,45,10,'Non-Coffe/RedVelvet.jpeg',1,1),
(15,2,'NC-005','Thai Tea','thai-tea','Teh Thailand dengan susu',22000,50,10,'Non-Coffe/ThaiTea.jpeg',1,0),
(16,2,'NC-006','Brown Sugar Milk Tea','brown-sugar-milk-tea','Susu dengan gula aren',24000,50,10,'Non-Coffe/Brown Sugar Milk Tea.jpeg',1,0),
(17,2,'NC-007','Green Tea Latte','green-tea-latte','Teh hijau dengan susu',26000,45,10,'Non-Coffe/Grean Latte.jpeg',1,0),
(18,2,'NC-008','Strawberry Milk','strawberry-milk','Susu segar rasa stroberi',24000,45,10,'Non-Coffe/StrowberryMilk.jpeg',1,0),
(19,2,'NC-009','Banana Milk','banana-milk','Susu dengan rasa pisang',23000,45,10,'Non-Coffe/BananaMilk.jpeg',1,0),
(20,2,'NC-010','Matcha Chocolate','matcha-chocolate','Perpaduan matcha dan cokelat',29000,40,10,'Non-Coffe/Matcha Chocolatte.jpeg',1,0),
(21,4,'SG-001','Sakura Dream','sakura-dream','Lychee soda dengan warna pink',30000,40,10,'Signature Drink/Sakura Dream.jpg',1,1),
(22,4,'SG-002','Ocean Breeze','ocean-breeze','Blue citrus soda yang menyegarkan',28000,40,10,'Signature Drink/Ocean Breeze.jpg',1,0),
(23,4,'SG-003','Caramel Bliss','caramel-bliss','Kopi susu dengan salted caramel',33000,40,10,'Signature Drink/Caramel Bliss.jpg',1,1),
(24,4,'SG-004','Cloud Matcha','cloud-matcha','Matcha dengan whipped cream',32000,40,10,'Signature Drink/Cloud Matcha.jpg',1,1),
(25,4,'SG-005','Berry Fizz','berry-fizz','Soda stroberi dengan buah berry',29000,40,10,'Signature Drink/Berry Fizz.jpg',1,0),
(26,4,'SG-006','Vanilla Sky','vanilla-sky','Vanilla milk dengan caramel drizzle',31000,40,10,'Signature Drink/Vanilla Sky.jpg',1,0),
(27,4,'SG-007','Tropical Glow','tropical-glow','Minuman mangga yang segar',28000,40,10,'Signature Drink/Tropical Glow.jpg',1,0),
(28,4,'SG-008','Choco Berry','choco-berry','Cokelat dengan perpaduan berry',30000,40,10,'Signature Drink/Choco Berry.jpg',1,0),
(29,4,'SG-009','Pink Paradise','pink-paradise','Minuman dragon fruit creamy',31000,40,10,'Signature Drink/Pink Paradise.jpg',1,0),
(30,4,'SG-010','Golden Hour','golden-hour','Honey lemon sparkling drink',29000,40,10,'Signature Drink/Golden Hour.jpg',1,0),
(31,3,'FP-001','Butter Croissant','butter-croissant','Pastry renyah dengan butter',18000,30,8,'Food&Pastry/Butter Croissant.jpeg',0,1),
(32,3,'FP-002','Chocolate Croissant','chocolate-croissant','Croissant isi cokelat',22000,30,8,'Food&Pastry/Chocolate Croissant.jpeg',0,0),
(33,3,'FP-003','Cheese Danish','cheese-danish','Pastry dengan topping keju',24000,25,8,'Food&Pastry/Cheese Danish.jpeg',0,0),
(34,3,'FP-004','Blueberry Muffin','blueberry-muffin','Muffin dengan blueberry',20000,25,8,'Food&Pastry/Blueberry Muffin.jpeg',0,0),
(35,3,'FP-005','Banana Bread','banana-bread','Roti lembut rasa pisang',20000,25,8,'Food&Pastry/Banana Bread.jpeg',0,0),
(36,3,'FP-006','Choco Lava Cake','choco-lava-cake','Cake cokelat dengan lava',32000,6,8,'Food&Pastry/Choco Lava Cake.jpeg',0,1),
(37,3,'FP-007','Strawberry Shortcake','strawberry-shortcake','Cake vanilla dan stroberi',34000,7,8,'Food&Pastry/Strawberry Shortcake.jpeg',0,0),
(38,3,'FP-008','Classic Cheesecake','classic-cheesecake','Cheesecake lembut dan creamy',32000,20,8,'Food&Pastry/Classic Cheescake.jpeg',0,0),
(39,3,'FP-009','French Fries','french-fries','Kentang goreng crispy',22000,40,10,'Food&Pastry/French Fries.jpeg',0,0),
(40,3,'FP-010','Chicken Sandwich','chicken-sandwich','Sandwich isi ayam dan sayuran',30000,25,8,'Food&Pastry/Chicken Sandwich.jpeg',0,0);

INSERT INTO gallery_images (title,collection,image,sort_order) VALUES
('Coffee Latte Art','coffee','Coffee Latte Art.jpg',1),
('Cappuccino','coffee','Cappuccino.jpg',2),
('Espresso','coffee','Espresso.jpg',3),
('Palm Sugar Coffee','coffee','Palm Sugar Coffe.jpg',4),
('Single Origin Arabica Bliss','coffee','Single Origin Arabica Bliss.jpg',5),
('Caramel Cookie Frappuccino','drinks','Caramel Cookie Frappuccino.jpg',1),
('Lemon Tea','drinks','Lemon Tea.jpg',2),
('Matcha','drinks','Matcha.jpg',3),
('Croissant','food','Croissant.jpg',1),
('Pain au Chocolat','food','Pain au Chocolat.jpg',2),
('Pastry','food','Pastry.jpg',3),
('Curry Bread','food','Curry Bread.jpg',4),
('Banana Chocolate Chip Bread','food','Banana Chocolate Chip Bread.jpg',5),
('Indoor Area','ambience','Indoor Area.jpg',1),
('Outdoor Area','ambience','Outdoor Area.jpg',2),
('Smoking Room Area','ambience','Smoking Room Area.jpg',3),
('Chocolate Cake','desserts','Chocolate Cake.jpg',1),
('Strawberry Cheesecake','desserts','Starberry Cheesecake.jpg',2),
('Tiramisu Cheesecake with Mascarpone Cream','desserts','Tiramisu Cheesecake with Mascarpone Cream.jpg',3);

INSERT INTO newsletter_subscribers (email,subscribed_at) VALUES ('andi@example.com',NOW()),('sari@example.com',NOW());

-- Data contoh transaksi (agar dashboard dan laporan langsung terisi)
INSERT INTO orders (id,order_code,customer_id,processed_by,order_channel,order_type,table_number,order_status,subtotal,discount_amount,total_amount,notes,cancel_reason,stock_deducted,ordered_at,completed_at) VALUES
(1,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '9:32' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(1,4,'0')),1,2,'online','takeaway',NULL,'completed',102000,5000,97000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '9:32' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '9:32' HOUR_MINUTE)),
(2,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '14:02' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(2,4,'0')),1,2,'online','dine_in','9','completed',60000,0,60000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '14:02' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '14:02' HOUR_MINUTE)),
(3,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 8 DAY),INTERVAL '9:17' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(3,4,'0')),1,2,'cashier','takeaway',NULL,'completed',178000,5000,173000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 8 DAY),INTERVAL '9:17' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 8 DAY),INTERVAL '9:17' HOUR_MINUTE)),
(4,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 7 DAY),INTERVAL '19:24' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(4,4,'0')),1,2,'online','takeaway',NULL,'completed',216000,5000,211000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 7 DAY),INTERVAL '19:24' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 7 DAY),INTERVAL '19:24' HOUR_MINUTE)),
(5,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '10:39' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(5,4,'0')),2,2,'online','dine_in','8','completed',36000,0,36000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '10:39' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '10:39' HOUR_MINUTE)),
(6,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '19:15' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(6,4,'0')),1,2,'cashier','takeaway',NULL,'completed',30000,0,30000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '19:15' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '19:15' HOUR_MINUTE)),
(7,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 5 DAY),INTERVAL '16:17' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(7,4,'0')),1,2,'online','dine_in','7','completed',82000,0,82000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 5 DAY),INTERVAL '16:17' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 5 DAY),INTERVAL '16:17' HOUR_MINUTE)),
(8,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:09' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(8,4,'0')),1,2,'cashier','dine_in','11','completed',160000,5000,155000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:09' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:09' HOUR_MINUTE)),
(9,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '17:11' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(9,4,'0')),2,2,'online','takeaway',NULL,'completed',36000,0,36000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '17:11' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '17:11' HOUR_MINUTE)),
(10,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '17:36' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(10,4,'0')),2,2,'cashier','takeaway',NULL,'completed',92000,0,92000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '17:36' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '17:36' HOUR_MINUTE)),
(11,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 3 DAY),INTERVAL '18:43' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(11,4,'0')),2,2,'cashier','dine_in','8','completed',96000,0,96000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 3 DAY),INTERVAL '18:43' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 3 DAY),INTERVAL '18:43' HOUR_MINUTE)),
(12,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '14:03' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(12,4,'0')),3,2,'online','dine_in','2','completed',211000,5000,206000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '14:03' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '14:03' HOUR_MINUTE)),
(13,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:21' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(13,4,'0')),1,2,'online','dine_in','2','completed',18000,0,18000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:21' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:21' HOUR_MINUTE)),
(14,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:23' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(14,4,'0')),1,2,'online','dine_in','2','completed',66000,0,66000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:23' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:23' HOUR_MINUTE)),
(15,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '18:16' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(15,4,'0')),3,2,'cashier','takeaway',NULL,'completed',29000,0,29000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '18:16' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '18:16' HOUR_MINUTE)),
(16,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '12:05' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(16,4,'0')),3,2,'cashier','dine_in','2','completed',118000,5000,113000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '12:05' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '12:05' HOUR_MINUTE)),
(17,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '11:33' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(17,4,'0')),2,2,'online','takeaway',NULL,'completed',161000,5000,156000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '11:33' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '11:33' HOUR_MINUTE)),
(18,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '20:33' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(18,4,'0')),3,2,'online','takeaway',NULL,'completed',32000,0,32000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '20:33' HOUR_MINUTE),DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '20:33' HOUR_MINUTE)),
(19,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '16:34' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(19,4,'0')),2,NULL,'online','takeaway',NULL,'pending',162000,5000,157000,NULL,NULL,0,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '16:34' HOUR_MINUTE),NULL),
(20,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '15:22' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(20,4,'0')),3,2,'online','dine_in','1','confirmed',201000,5000,196000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '15:22' HOUR_MINUTE),NULL),
(21,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '13:28' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(21,4,'0')),1,2,'cashier','takeaway',NULL,'preparing',141000,5000,136000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '13:28' HOUR_MINUTE),NULL),
(22,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '13:13' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(22,4,'0')),2,2,'online','takeaway',NULL,'ready',56000,0,56000,NULL,NULL,1,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '13:13' HOUR_MINUTE),NULL),
(23,CONCAT('KK-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '14:50' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(23,4,'0')),3,2,'online','dine_in','8','cancelled',169000,5000,164000,NULL,'Stok habis, dibatalkan pelanggan',0,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '14:50' HOUR_MINUTE),NULL);

INSERT INTO order_details (order_id,product_id,product_name,size,unit_price,quantity,line_total) VALUES
(1,21,'Sakura Dream','large',35000,2,70000),
(1,14,'Red Velvet','large',32000,1,32000),
(2,21,'Sakura Dream','regular',30000,2,60000),
(3,21,'Sakura Dream','regular',30000,3,90000),
(3,22,'Ocean Breeze','large',33000,2,66000),
(3,2,'Americano','regular',22000,1,22000),
(4,3,'Café Latte','regular',28000,3,84000),
(4,14,'Red Velvet','large',32000,3,96000),
(4,31,'Butter Croissant',NULL,18000,2,36000),
(5,31,'Butter Croissant',NULL,18000,2,36000),
(6,6,'Café Mocha','regular',30000,1,30000),
(7,31,'Butter Croissant',NULL,18000,1,18000),
(7,5,'Caramel Macchiato','regular',32000,2,64000),
(8,11,'Matcha Latte','large',33000,2,66000),
(8,22,'Ocean Breeze','large',33000,2,66000),
(8,11,'Matcha Latte','regular',28000,1,28000),
(9,1,'Espresso',NULL,18000,2,36000),
(10,1,'Espresso',NULL,18000,1,18000),
(10,24,'Cloud Matcha','large',37000,2,74000),
(11,36,'Choco Lava Cake',NULL,32000,3,96000),
(12,38,'Classic Cheesecake',NULL,32000,2,64000),
(12,23,'Caramel Bliss','regular',33000,2,66000),
(12,4,'Cappuccino','regular',27000,3,81000),
(13,31,'Butter Croissant',NULL,18000,1,18000),
(14,2,'Americano','regular',22000,3,66000),
(15,9,'Flat White','regular',29000,1,29000),
(16,22,'Ocean Breeze','regular',28000,1,28000),
(16,4,'Cappuccino','regular',27000,2,54000),
(16,31,'Butter Croissant',NULL,18000,2,36000),
(17,21,'Sakura Dream','large',35000,2,70000),
(17,31,'Butter Croissant',NULL,18000,3,54000),
(17,5,'Caramel Macchiato','large',37000,1,37000),
(18,38,'Classic Cheesecake',NULL,32000,1,32000),
(19,3,'Café Latte','large',33000,2,66000),
(19,36,'Choco Lava Cake',NULL,32000,2,64000),
(19,5,'Caramel Macchiato','regular',32000,1,32000),
(20,11,'Matcha Latte','large',33000,1,33000),
(20,11,'Matcha Latte','regular',28000,3,84000),
(20,11,'Matcha Latte','regular',28000,3,84000),
(21,31,'Butter Croissant',NULL,18000,2,36000),
(21,6,'Café Mocha','large',35000,3,105000),
(22,3,'Café Latte','regular',28000,1,28000),
(22,11,'Matcha Latte','regular',28000,1,28000),
(23,9,'Flat White','regular',29000,2,58000),
(23,17,'Green Tea Latte','regular',26000,3,78000),
(23,3,'Café Latte','large',33000,1,33000);

INSERT INTO sales (order_id,invoice_number,cashier_id,payment_method,amount_due,amount_paid,change_amount,payment_status,reference_number,paid_at) VALUES
(1,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '9:32' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(1,4,'0')),2,'cash',97000,100000,3000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '9:32' HOUR_MINUTE)),
(2,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '14:02' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(2,4,'0')),2,'cash',60000,60000,0,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 9 DAY),INTERVAL '14:02' HOUR_MINUTE)),
(3,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 8 DAY),INTERVAL '9:17' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(3,4,'0')),2,'cash',173000,180000,7000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 8 DAY),INTERVAL '9:17' HOUR_MINUTE)),
(4,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 7 DAY),INTERVAL '19:24' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(4,4,'0')),2,'qris',211000,211000,0,'paid','QR100004',DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 7 DAY),INTERVAL '19:24' HOUR_MINUTE)),
(5,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '10:39' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(5,4,'0')),2,'cash',36000,40000,4000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '10:39' HOUR_MINUTE)),
(6,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '19:15' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(6,4,'0')),2,'cash',30000,30000,0,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 6 DAY),INTERVAL '19:15' HOUR_MINUTE)),
(7,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 5 DAY),INTERVAL '16:17' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(7,4,'0')),2,'cash',82000,90000,8000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 5 DAY),INTERVAL '16:17' HOUR_MINUTE)),
(8,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:09' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(8,4,'0')),2,'cash',155000,160000,5000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '10:09' HOUR_MINUTE)),
(9,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '17:11' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(9,4,'0')),2,'qris',36000,36000,0,'paid','QR100009',DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '17:11' HOUR_MINUTE)),
(10,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '17:36' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(10,4,'0')),2,'cash',92000,100000,8000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 4 DAY),INTERVAL '17:36' HOUR_MINUTE)),
(11,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 3 DAY),INTERVAL '18:43' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(11,4,'0')),2,'cash',96000,100000,4000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 3 DAY),INTERVAL '18:43' HOUR_MINUTE)),
(12,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '14:03' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(12,4,'0')),2,'cash',206000,210000,4000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '14:03' HOUR_MINUTE)),
(13,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:21' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(13,4,'0')),2,'cash',18000,20000,2000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:21' HOUR_MINUTE)),
(14,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:23' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(14,4,'0')),2,'cash',66000,70000,4000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 2 DAY),INTERVAL '9:23' HOUR_MINUTE)),
(15,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '18:16' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(15,4,'0')),2,'qris',29000,29000,0,'paid','QR100015',DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '18:16' HOUR_MINUTE)),
(16,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '12:05' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(16,4,'0')),2,'qris',113000,113000,0,'paid','QR100016',DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '12:05' HOUR_MINUTE)),
(17,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '11:33' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(17,4,'0')),2,'cash',156000,160000,4000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '11:33' HOUR_MINUTE)),
(18,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '20:33' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(18,4,'0')),2,'cash',32000,40000,8000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '20:33' HOUR_MINUTE)),
(19,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '16:34' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(19,4,'0')),NULL,'qris',157000,0,0,'pending',NULL,NULL),
(20,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '15:22' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(20,4,'0')),NULL,'cash',196000,0,0,'pending',NULL,NULL),
(21,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '13:28' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(21,4,'0')),2,'cash',136000,140000,4000,'paid',NULL,DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '13:28' HOUR_MINUTE)),
(22,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '13:13' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(22,4,'0')),2,'qris',56000,56000,0,'paid','QR100022',DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 0 DAY),INTERVAL '13:13' HOUR_MINUTE)),
(23,CONCAT('INV-',DATE_FORMAT(DATE_ADD(DATE_SUB(CURDATE(),INTERVAL 1 DAY),INTERVAL '14:50' HOUR_MINUTE),'%Y%m%d'),'-',LPAD(23,4,'0')),NULL,'cash',164000,0,0,'pending',NULL,NULL);
