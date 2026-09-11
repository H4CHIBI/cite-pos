<?php
return "
DROP TABLE IF EXISTS `transaction_items`;
CREATE TABLE IF NOT EXISTS transaction_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    product_id INT NOT NULL,
    batch_id INT NULL,
    owner_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price_at_purchase DECIMAL(10,2) NOT NULL,
    release_status ENUM('Void', 'For Releasing', 'Released') NOT NULL DEFAULT 'For Releasing',
    CONSTRAINT fk_transaction_items_transaction
        FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id),
    CONSTRAINT fk_transaction_items_product
        FOREIGN KEY (product_id) REFERENCES products(product_id),
    CONSTRAINT fk_transaction_items_batch
        FOREIGN KEY (batch_id) REFERENCES batches(batch_id),
    CONSTRAINT fk_transaction_items_owner
        FOREIGN KEY (owner_id) REFERENCES students(student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
