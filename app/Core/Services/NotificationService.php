<?php
namespace App\Core\Services;

use App\Core\Database\DbConnection;
use PDO;
use PDOException;
use App\Core\Helpers\SettingsHelper;
// --- PHPMailer Imports ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\SMTP; // Optional, for SMTP debugging
// --- End PHPMailer Imports ---

class NotificationService {

    private ?PDO $pdo;
    private array $mailConfig; // To store mail config

    public function __construct() {
        $this->pdo = DbConnection::getInstance();
        // Load mail configuration
        $configPath = __DIR__ . '/../../config/mail.php';
        if (file_exists($configPath)) {
            $this->mailConfig = require($configPath);
        } else {
            $this->mailConfig = []; // Set empty if config file missing
            error_log("NotificationService Warning: Mail configuration file not found at {$configPath}");
        }
    }

    /**
     * Prepares, logs, and attempts to send a notification.
     *
     * @param int|null $userId Target user ID.
     * @param string $templateCode Template code.
     * @param array $data Data for placeholders.
     * @param string|null $recipientDetail Specific email/phone if $userId is null.
     * @param int|null $relatedEntityId Optional related entity ID.
     * @param string|null $relatedEntityType Optional related entity type.
     *
     * @return bool True if logging was successful (sending might still fail silently if config is bad).
     */
    public function sendNotification(
        ?int $userId,
        string $templateCode,
        array $data = [],
        ?string $recipientDetail = null,
        ?int $relatedEntityId = null,
        ?string $relatedEntityType = null
    ): bool {
        if (!$this->pdo) { /* ... DB connection check ... */ return false; }

        try {
            $sqlTpl = "SELECT template_id, type, subject, body_template FROM notification_templates WHERE template_code = :code AND is_active = TRUE";
            $stmtTpl = $this->pdo->prepare($sqlTpl);
            $stmtTpl->execute([':code' => $templateCode]);
            $templates = $stmtTpl->fetchAll(PDO::FETCH_ASSOC);

            if (empty($templates)) { /* ... No template found check ... */ return false; }

            $logSuccessOverall = true; // Track if *all* logs succeed

            foreach ($templates as $template) {
                // Prepare message body and subject (same as before)
                $messageBody = $template['body_template'];
                foreach ($data as $key => $value) { $messageBody = str_replace('{{' . $key . '}}', (string)$value, $messageBody); }
                $subject = $template['subject'] ?? ('Notification from School');
                 foreach ($data as $key => $value) { $subject = str_replace('{{' . $key . '}}', (string)$value, $subject); }

                $status = 'pending'; // Default status
                $sentAt = null;
                $sentBy = null;
                $currentRecipientDetail = $recipientDetail; // Use passed detail if available
                $fromName = SettingsHelper::get('mail_from_name', 'SFMS System');

                // --- Attempt Actual Sending ---
                $sendError = null; // Store specific sending error
                if ($template['type'] === 'email') {
                    // Determine recipient email
                    $recipientEmail = null;
                    if ($userId) {
                         // Fetch user's email if sending via user ID
                         $stmtUser = $this->pdo->prepare("SELECT email FROM users WHERE user_id = ?");
                         $stmtUser->execute([$userId]);
                         $recipientEmail = $stmtUser->fetchColumn();
                    } elseif ($currentRecipientDetail && filter_var($currentRecipientDetail, FILTER_VALIDATE_EMAIL)) {
                         $recipientEmail = $currentRecipientDetail;
                    }

                    if ($recipientEmail && !empty($this->mailConfig['host'])) { // Check if email found and mail is configured
                         $mail = new PHPMailer(true); // Enable exceptions
                         try {
                             // --- PHPMailer Configuration ---
                             // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output for SMTP issues
                             $mail->isSMTP();
                             $mail->Host       = $this->mailConfig['host'];
                             $mail->SMTPAuth   = true;
                             $mail->Username   = $this->mailConfig['username'];
                             $mail->Password   = $this->mailConfig['password'];
                             if ($this->mailConfig['encryption'] === 'tls') {
                                 $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                             } elseif ($this->mailConfig['encryption'] === 'ssl') {
                                 $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                             }
                             $mail->Port = (int)$this->mailConfig['port'];

                             //Recipients
                             $mail->setFrom($this->mailConfig['from_address'], $fromName);
                             $mail->addAddress($recipientEmail); // Add recipient

                             //Content
                             $mail->isHTML(false); // Send as plain text
                             $mail->Subject = $subject;
                             $mail->Body    = $messageBody;
                             $mail->AltBody = $messageBody; // Optional: plain text body for non-HTML mail clients

                             $mail->send();
                             // If send successful:
                             $status = 'sent';
                             $sentAt = date('Y-m-d H:i:s'); // Current timestamp
                             error_log("NotificationService: Email sent successfully to {$recipientEmail} for template {$templateCode}");

                         } catch (PHPMailerException $e) {
                             $status = 'failed';
                             $sendError = $e->errorMessage(); // Capture PHPMailer error message
                             error_log("NotificationService Error: PHPMailer failed for template {$templateCode} to {$recipientEmail}. Error: {$mail->ErrorInfo}");
                         }
                    } else {
                        $status = 'failed'; // Failed because no valid recipient email or mail not configured
                        $sendError = 'No valid recipient email or mail configuration missing.';
                        error_log("NotificationService Error: Cannot send email for template {$templateCode}. Recipient: " . ($recipientEmail ?: 'Not Found') . ", Mail Config Host: " . ($this->mailConfig['host'] ?? 'Not Set'));
                    }

                } elseif ($template['type'] === 'sms') {
                    // --- TODO: Add SMS Sending Logic Here ---
                    // Requires SMS Gateway API integration
                    // $status = 'failed'; // Until implemented
                    // $sendError = 'SMS sending not implemented.';
                    error_log("NotificationService Info: SMS sending not implemented for template {$templateCode}");
                     $status = 'pending'; // Keep as pending if not implemented
                     $sendError = 'SMS not implemented'; // Can store this in log details

                } else {
                    // System notification, maybe store differently or mark as read immediately?
                    // For now, just log as pending.
                    $status = 'pending'; // Or maybe 'sent' if it's just an internal DB log?
                     error_log("NotificationService Info: System notification logged for template {$templateCode}");
                }
                // --- End Attempt Actual Sending ---


                // 4. Insert into notifications log table (always attempt this)
                $sqlLog = "INSERT INTO notifications (user_id, template_id, subject, message, channel, status, recipient_detail, related_entity_type, related_entity_id, sent_by, created_at, sent_at, details) VALUES (:user_id, :template_id, :subject, :message, :channel, :status, :recipient_detail, :rel_type, :rel_id, :sent_by, NOW(), :sent_at, :details)"; // Added details column
                $stmtLog = $this->pdo->prepare($sqlLog);
                $paramsLog = [
                    ':user_id' => $userId,
                    ':template_id' => $template['template_id'],
                    ':subject' => $subject,
                    ':message' => $messageBody,
                    ':channel' => $template['type'],
                    ':status' => $status, // Use status determined above
                    ':recipient_detail' => $recipientEmail ?? $currentRecipientDetail ?? null, // Log actual recipient used
                    ':rel_type' => $relatedEntityType,
                    ':rel_id' => $relatedEntityId,
                    ':sent_by' => $sentBy,
                    ':sent_at' => $sentAt, // Use sent time determined above
                    ':details' => $sendError // Store sending error message if any
                ];

                if (!$stmtLog->execute($paramsLog)) {
                    error_log("NotificationService Error: Failed to insert log entry for template ID {$template['template_id']}. Status was '{$status}'.");
                    $logSuccessOverall = false;
                }

            } // end foreach template

            return $logSuccessOverall;

        } catch (PDOException $e) { /* ... DB Exception handling ... */ return false; }
    }
}