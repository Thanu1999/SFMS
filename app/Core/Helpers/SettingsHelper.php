<?php
namespace App\Core\Helpers;

use App\Core\Database\DbConnection;
use PDO;
use PDOException;

class SettingsHelper {
    private static array $cache = [];

    // Gets a setting value, using cache
    public static function get(string $key, mixed $defaultValue = null): mixed {
        if (isset(self::$cache[$key])) { return self::$cache[$key]; }
        $pdo = DbConnection::getInstance();
        if (!$pdo) { return $defaultValue; }
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = :key LIMIT 1");
            $stmt->execute([':key' => $key]);
            $value = $stmt->fetchColumn();
            if ($value !== false) {
                self::$cache[$key] = $value;
                return $value;
            }
        } catch (PDOException $e) { error_log("SettingsHelper::get Error: " . $e->getMessage()); }
        return $defaultValue;
    }

    // Sets (Inserts or Updates) a setting value
    public static function set(string $key, string $value, ?int $userId = null): bool {
         $pdo = DbConnection::getInstance();
         if (!$pdo) { return false; }
         try {
             $sql = "INSERT INTO system_settings (setting_key, setting_value, updated_by_user_id, updated_at)
                     VALUES (:key, :value, :user_id, NOW())
                     ON DUPLICATE KEY UPDATE
                     setting_value = VALUES(setting_value),
                     updated_by_user_id = VALUES(updated_by_user_id),
                     updated_at = NOW()";
             $stmt = $pdo->prepare($sql);
             $success = $stmt->execute([':key' => $key, ':value' => $value, ':user_id' => $userId]);
             if ($success) { self::$cache[$key] = $value; } // Update cache
             return $success;
         } catch (PDOException $e) { error_log("SettingsHelper::set Error: " . $e->getMessage()); return false; }
    }

    // Fetches multiple settings
    public static function getMultiple(array $keys): array {
        $settings = []; $keysToFetch = [];
        foreach ($keys as $key) { if (isset(self::$cache[$key])) { $settings[$key] = self::$cache[$key]; } else { $keysToFetch[] = $key; } }
        if (empty($keysToFetch)) { return $settings; }
        $pdo = DbConnection::getInstance();
        if (!$pdo) { return $settings; }
        try {
            $placeholders = implode(',', array_fill(0, count($keysToFetch), '?'));
            $sql = "SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ({$placeholders})";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($keysToFetch);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { self::$cache[$row['setting_key']] = $row['setting_value']; $settings[$row['setting_key']] = $row['setting_value']; }
        } catch (PDOException $e) { error_log("SettingsHelper::getMultiple Error: " . $e->getMessage()); }
        return $settings;
    }

    // Clears the static cache if needed
    public static function clearCache(): void { self::$cache = []; }
}