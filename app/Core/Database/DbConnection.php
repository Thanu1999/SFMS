<?php

namespace App\Core\Database; // Use namespaces for better organization

use PDO;
use PDOException;

class DbConnection {

    private static $pdoInstance = null;

    // Private constructor to prevent direct object creation
    private function __construct() {}

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {}

    /**
     * Gets the PDO database connection instance (Singleton pattern).
     * Reads configuration from app/config/database.php.
     *
     * @return PDO|null Returns the PDO instance or null on failure.
     */
    public static function getInstance(): ?PDO {
        if (self::$pdoInstance === null) {
            // Construct the path to the config file relative to this file's directory
            // Adjust the number of '/..' based on your final structure if needed.
            $configPath = __DIR__ . '/../../config/database.php';

            if (!file_exists($configPath)) {
                // Handle error: Config file not found
                // You might want to throw an exception or log an error here
                error_log("Database configuration file not found at: " . $configPath);
                return null;
            }

            $config = require($configPath);

            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch results as associative arrays
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
            ];

            try {
                self::$pdoInstance = new PDO($dsn, $config['username'], $config['password'], $options);
            } catch (PDOException $e) {
                // Handle error: Connection failed
                // In a real app, log this error securely, don't echo details directly
                error_log("Database Connection Error: " . $e->getMessage());
                // Optionally re-throw or handle more gracefully
                // throw new PDOException($e->getMessage(), (int)$e->getCode());
                return null; // Return null or throw exception based on your error handling strategy
            }
        }

        return self::$pdoInstance;
    }
}