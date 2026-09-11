<?php
// database/migration/Migrate.php

require_once __DIR__ . '/../../app/Config/env.php';
loadEnv(__DIR__ . '/../../.env');
require_once __DIR__ . '/../../app/Config/Database.php';

class Migration {
    private $db;
    private $dbName;

    public function __construct() {
        $this->dbName = getenv('DB_DATABASE');
    }

    public function run() {
        echo "Initializing database migration process...\n\n";

        // Step 1: Connect to MySQL server without selecting a db, then create it if missing
        $databaseServer = new Database();
        $serverConn = $databaseServer->getServerConnection();

        try {
            // Safely quote or construct the database creation query
            $safeDbName = "`" . str_replace("`", "``", $this->dbName) . "`";
            $serverConn->exec("CREATE DATABASE IF NOT EXISTS {$safeDbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
            echo "Database '{$this->dbName}' is ready.\n\n";
        } catch (PDOException $e) {
            die("Error creating database: " . $e->getMessage() . "\n");
        }

        // Step 2: Now connect using the standard connection linked to the target database
        $databaseApp = new Database();
        $this->db = $databaseApp->getConnection();

        // Step 3: Run table migrations
        echo "Starting table migrations...\n\n";
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");

        $migrationFiles = glob(__DIR__ . '/tables/*.php');
        sort($migrationFiles);

        $successCount = 0;

        foreach ($migrationFiles as $file) {
            $filename = basename($file);
            $sql = require $file; 

            if (empty(trim($sql))) {
                echo "Skipped: {$filename} (Empty SQL)\n";
                continue;
            }

            try {
                $this->db->exec($sql);
                echo "Migrated: {$filename}\n";
                $successCount++;
            } catch (PDOException $e) {
                echo "Error migrating {$filename}: " . $e->getMessage() . "\n";
            }
        }

        $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");

        echo "\nMigration completed successfully! {$successCount} table files processed.\n";
    }
}

$migration = new Migration();
$migration->run();