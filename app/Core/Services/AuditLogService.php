<?php
namespace App\Core\Services;

use App\Core\Database\DbConnection;
use PDO;
use PDOException;

class AuditLogService {

    private ?PDO $pdo;

    public function __construct() {
        $this->pdo = DbConnection::getInstance();
    }

    /**
     * Logs an action to the audit_logs table.
     *
     * @param int|null $userId ID of the user performing the action (null for system actions).
     * @param string $actionType A code representing the action (e.g., 'USER_LOGIN_SUCCESS', 'STUDENT_CREATED', 'PAYMENT_RECORDED').
     * @param string|null $targetTable The database table primarily affected (optional).
     * @param int|null $targetRecordId The ID of the record primarily affected (optional).
     * @param mixed|null $details Additional details about the action (e.g., changed data - consider JSON encoding).
     * @param string|null $ipAddress The IP address of the user performing the action (optional).
     * @param string|null $userAgent The user agent string of the user's browser (optional).
     *
     * @return bool True on success, false on failure.
     */
    public function log(
        ?int $userId,
        string $actionType,
        ?string $targetTable = null,
        ?int $targetRecordId = null,
        mixed $details = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): bool {
        if (!$this->pdo) {
            error_log("AuditLogService Error: Database connection failed.");
            return false; // Cannot log without DB connection
        }

        // Encode details as JSON if it's an array or object for better storage
        $detailsJson = null;
        if (is_array($details) || is_object($details)) {
            $detailsJson = json_encode($details);
        } elseif ($details !== null) {
            $detailsJson = (string)$details; // Cast other types to string if needed
        }

        $sql = "INSERT INTO audit_logs (user_id, action_type, table_name, record_id, action_details, ip_address, user_agent, created_at)
                VALUES (:user_id, :action_type, :table_name, :record_id, :details, :ip_address, :user_agent, NOW())";

        try {
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':action_type', $actionType, PDO::PARAM_STR);
            $stmt->bindValue(':table_name', $targetTable, $targetTable === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':record_id', $targetRecordId, $targetRecordId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':details', $detailsJson, $detailsJson === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':ip_address', $ipAddress, $ipAddress === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':user_agent', $userAgent, $userAgent === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

            return $stmt->execute();

        } catch (PDOException $e) {
            // Log the error trying to write to the audit log, but don't stop the main application flow
            error_log("AuditLogService Error: Failed to write log entry. Action: {$actionType}, User: {$userId}. Error: " . $e->getMessage());
            return false;
        }
    }
}