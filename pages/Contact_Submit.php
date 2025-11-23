<?php
// pages/contact_submit.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Basic server-side validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $subject === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: contact.php');
    exit;
}

// Recommended: store to DB or send email. Example: save to a simple CSV for now.
$logDir = __DIR__ . '/data';
if (!is_dir($logDir)) mkdir($logDir, 0755, true);
$line = sprintf(
    "%s\t%s\t%s\t%s\t%s\n",
    date('c'),
    str_replace(["\r","\n","\t"], ' ', $name),
    $email,
    str_replace(["\r","\n","\t"], ' ', $subject),
    str_replace(["\r","\n","\t"], ' ', $message)
);
file_put_contents($logDir . '/contacts.log', $line, FILE_APPEND | LOCK_EX);

// Redirect back to contact page with success flag
header('Location: contact.php?sent=1');
exit;
