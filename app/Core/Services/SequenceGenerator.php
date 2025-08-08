<?php
namespace App\Core\Services;

use App\Core\Database\DbConnection;
use PDO;
use PDOException;

class SequenceGenerator {

    private ?PDO $pdo;

    public function __construct() {
        $this->pdo = DbConnection::getInstance();
    }

    /**
     * Gets the next value for a named sequence using a dedicated counter table.
     * Uses transactions and row locking to prevent race conditions.
     *
     * @param string $sequenceName The name of the sequence (e.g., 'admission_number').
     * @return int|null The next sequence value, or null on error.
     */
    public function getNextValue(string $sequenceName): ?int {
        if (!$this->pdo) {
            error_log("SequenceGenerator: Database connection failed.");
            return null;
        }

        try {
            $this->pdo->beginTransaction();

            // Lock the row for update to prevent other transactions reading/writing it
            $stmt = $this->pdo->prepare("SELECT current_value FROM sequence_counters WHERE sequence_name = :name FOR UPDATE");
            $stmt->bindParam(':name', $sequenceName, PDO::PARAM_STR);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                // Sequence doesn't exist - should ideally not happen if initialized
                error_log("SequenceGenerator: Sequence '{$sequenceName}' not found in sequence_counters table.");
                $this->pdo->rollBack();
                return null; // Or maybe insert initial value here? Depends on requirements.
            }

            $currentValue = (int)$row['current_value'];
            $nextValue = $currentValue + 1;

            // Update the counter with the new value
            $updateStmt = $this->pdo->prepare("UPDATE sequence_counters SET current_value = :next_value WHERE sequence_name = :name");
            $updateStmt->bindParam(':next_value', $nextValue, PDO::PARAM_INT);
            $updateStmt->bindParam(':name', $sequenceName, PDO::PARAM_STR);
            $updateStmt->execute();

            $this->pdo->commit();

            return $nextValue;

        } catch (PDOException $e) {
            // Roll back transaction on error
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("SequenceGenerator Error for '{$sequenceName}': " . $e->getMessage());
            return null;
        }
    }
}