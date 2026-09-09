CREATE TABLE IF NOT EXISTS product_batches (
    product_batch_id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id INT NOT NULL,
    product_id INT NOT NULL,
    initial_quantity INT NOT NULL DEFAULT 0,
    current_quantity INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_product_batches_batch
        FOREIGN KEY (batch_id) REFERENCES batches(batch_id),
    CONSTRAINT fk_product_batches_product
        FOREIGN KEY (product_id) REFERENCES products(product_id),
    UNIQUE KEY uq_product_batches_batch_product (batch_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
