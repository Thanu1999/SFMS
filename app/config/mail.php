<?php
// Email Configuration Settings
// IMPORTANT: For production, use environment variables (.env file) instead of storing credentials here.

return [
    'driver' => 'smtp', // Or 'mail', 'sendmail' - smtp is most common
    'host' => 'smtp.mailersend.net', // Your SMTP server hostname 
    'port' => 587, // Common ports: 587 (TLS), 465 (SSL), 25 (unencrypted - avoid)
    'username' => $_ENV['MAIL_USERNAME'] ?? null, // Using $_ENV for username too
    'password' => $_ENV['MAIL_PASSWORD'] ?? null, // <-- Reads from .env via $_ENV
    'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
    'from_address' => 'MS_O5o00J@test-51ndgwvv8yxlzqx8.mlsender.net', // Default 'From' email address
    'from_name' => 'Your School Name SFMS', // Default 'From' name
];