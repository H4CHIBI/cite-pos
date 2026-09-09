-- Default development accounts. Change these passwords after the first login.
-- Password for all seeded accounts: password
INSERT INTO users (username, password_hash, full_name, role)
VALUES
    ('cashier', '$2y$10$/5g0ZHG8fbrdpzknd5U7sucQMS1exlbUF3kS2EdqcAienBkTCL2Ge', 'CITE POS Cashier', 'cashier'),
    ('officer', '$2y$10$/5g0ZHG8fbrdpzknd5U7sucQMS1exlbUF3kS2EdqcAienBkTCL2Ge', 'CITE POS Officer', 'officer'),
    ('admin', '$2y$10$/5g0ZHG8fbrdpzknd5U7sucQMS1exlbUF3kS2EdqcAienBkTCL2Ge', 'CITE POS Administrator', 'admin')
ON DUPLICATE KEY UPDATE
    password_hash = VALUES(password_hash),
    full_name = VALUES(full_name),
    role = VALUES(role);
