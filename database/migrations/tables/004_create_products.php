<?php
return "
DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255) NULL,
    is_batch_tracked BOOLEAN NOT NULL DEFAULT FALSE,
    requires_releasing BOOLEAN NOT NULL DEFAULT FALSE,
    stocks INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
