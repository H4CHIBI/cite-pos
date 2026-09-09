ALTER TABLE transactions
    ADD COLUMN is_void BOOLEAN NOT NULL DEFAULT FALSE AFTER change_amount;
