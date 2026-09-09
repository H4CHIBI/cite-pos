UPDATE transaction_items
SET release_status = 'For Releasing'
WHERE release_status = 'Completed';

ALTER TABLE transaction_items
    MODIFY COLUMN release_status ENUM('Void', 'For Releasing', 'Released')
    NOT NULL DEFAULT 'For Releasing';
