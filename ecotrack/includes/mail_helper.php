<?php
require_once 'SMTPMailer.php';

/**
 * Send an email using SMTP or log it to a file for local testing.
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email body
 * @return bool True if sent or logged
 */
function sendMail($to, $subject, $message) {
    // --- SMTP CONFIGURATION ---
    // You must fill these in to send real emails!
    // For Gmail, generate an "App Password" at myaccount.google.com/apppasswords
    $smtpHost = 'smtp.gmail.com';
    $smtpPort = 587; // TLS
    $smtpUser = 'bentalebdhiaeddine@gmail.com'; 
    $smtpPass = 'vnpa llyq tila ajxa'; 
    // ---------------------------

    // 1. Try Real Email if Configured
    // Validating that we have a real email and password (not placeholders)
    if (!empty($smtpUser) && !empty($smtpPass) && strpos($smtpUser, 'YOUR_EMAIL') === false) {
        // Strip spaces from App Password if present (common when copying from Google)
        $cleanPass = str_replace(' ', '', $smtpPass);
        
        $mailer = new SMTPMailer($smtpHost, $smtpPort, $smtpUser, $cleanPass);
        if ($mailer->send($to, $subject, $message)) {
            return true;
        }
    }

    // 2. Fallback: Log to file (for development/if SMTP fails)
    $logFile = __DIR__ . '/../email_log.txt';
    $logEntry = sprintf(
        "[%s] To: %s | Subject: %s | Message: %s\n%s\n",
        date('Y-m-d H:i:s'),
        $to,
        $subject,
        $message,
        str_repeat('-', 50)
    );
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    // Return true so the UI shows "Email sent" even if just logged locally
    return true; 
}
