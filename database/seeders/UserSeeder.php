<?php
// database/seeders/UserSeeder.php

require_once __DIR__ . '/../../app/config/env.php';
loadEnv(__DIR__ . '/../../.env');
require_once __DIR__ . '/../../app/config/database.php';

class UserSeeder {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function run() {
        // Updated user data with the new roles
        $users = [
            [
                'username' => 'cashier',
                'password' => 'password',
                'full_name' => 'Cashier User', 
                'role' => 'cashier'
            ],
            [
                'username' => 'officer',
                'password' => 'password',
                'full_name' => 'Officer User', 
                'role' => 'officer'
            ],
            [
                'username' => 'admin',
                'password' => 'password',
                'full_name' => 'Admin User', 
                'role' => 'admin'
            ]
        ];

        $query = "INSERT INTO users (username, password_hash, full_name, role) VALUES (:username, :password_hash, :full_name, :role)";
        $stmt = $this->db->prepare($query);

        $successCount = 0;

        foreach ($users as $user) {
            try {
                // Securely hash the password
                $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);

                $stmt->bindParam(':username', $user['username']);
                $stmt->bindParam(':password_hash', $hashedPassword);
                $stmt->bindParam(':full_name', $user['full_name']);
                $stmt->bindParam(':role', $user['role']);
                
                $stmt->execute();
                $successCount++;
                echo "Seeded user: {$user['username']} ({$user['role']})\n";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { 
                    echo "User {$user['username']} already exists.\n";
                } else {
                    echo "Error seeding {$user['username']}: " . $e->getMessage() . "\n";
                }
            }
        }

        echo "\nSeeding complete. $successCount users added.\n";
    }
}

$seeder = new UserSeeder();
$seeder->run();