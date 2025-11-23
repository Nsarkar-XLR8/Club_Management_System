<?php
// pages/Contact.php - FINAL NEXT-LEVEL REDESIGN
if (session_status() === PHP_SESSION_NONE) session_start();

$message = '';
$message_type = ''; // 'success' or 'error'

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['message'] ?? '');

    // Server-side validation
    if (empty($name) || empty($email) || empty($subject) || empty($body)) {
        $message = 'Please fill out all fields.';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please provide a valid email address.';
        $message_type = 'error';
    } else {
        // --- Email Sending Logic Would Go Here ---
        // In a real application, you would use a library like PHPMailer to send the email.
        // For this example, we will simulate success and redirect.
        // mail($to, $subject, $body, $headers);

        // Redirect to prevent form resubmission (Post-Redirect-Get pattern)
        header("Location: Contact.php?sent=1");
        exit;
    }
}

// Handle success message after redirect
if (isset($_GET['sent']) && $_GET['sent'] === '1') {
    $message = 'Thank you! Your message has been received. We will reply to the email you provided.';
    $message_type = 'success';
}

// Render the modern, self-contained navbar
include __DIR__ . '/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - Club Management System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    :root {
        --primary-color: #0077ff;
        --dark-blue: #0f172a;
        --light-gray: #f1f5f9;
        --text-color: #334155;
        --muted-text: #64748b;
        --border-color: #e2e8f0;
        --card-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
        --border-radius: 12px;
        --container-width: 1100px;
        --gap: 24px;
    }
    *, *::before, *::after { box-sizing: border-box; }
    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--light-gray);
        color: var(--text-color);
        line-height: 1.6;
    }
    
    .page-container {
        max-width: var(--container-width);
        margin: 60px auto;
        padding: 0 var(--gap);
    }

    .section-title { text-align: center; margin-bottom: 50px; }
    .section-title h1 { font-size: 3rem; font-weight: 800; color: var(--dark-blue); margin: 0 0 10px; }
    .section-title p { font-size: 1.1rem; color: var(--muted-text); max-width: 600px; margin: 0 auto; }

    .contact-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 40px;
        background-color: #fff;
        padding: 40px;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
    }
    @media (max-width: 900px) {
        .contact-grid { grid-template-columns: 1fr; }
    }

    .info-panel h2 {
        font-size: 1.75rem;
        margin: 0 0 16px;
        color: var(--dark-blue);
    }
    .info-panel p {
        margin: 0 0 30px;
        color: var(--muted-text);
    }
    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 24px;
    }
    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }
    .info-item .icon {
        font-size: 1.25rem;
        color: var(--primary-color);
        flex-shrink: 0;
        margin-top: 4px;
    }
    .info-item strong {
        display: block;
        color: var(--dark-blue);
        font-weight: 600;
    }
    .info-item span { color: var(--muted-text); }

    .form-grid { display: grid; grid-template-columns: 1fr; gap: var(--gap); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: var(--gap); }
    label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem; }
    input, textarea {
        width: 100%; padding: 12px 16px; border-radius: 8px;
        border: 1px solid var(--border-color); font-size: 1rem;
        font-family: 'Poppins', sans-serif; transition: all 0.2s ease;
    }
    input:focus, textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(0, 119, 255, 0.15);
    }
    textarea { resize: vertical; min-height: 120px; }
    .btn-primary {
        background-color: var(--primary-color); color: #fff; border: none; padding: 14px 24px;
        border-radius: 8px; font-weight: 700; font-size: 1rem; cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .btn-primary:hover { background-color: #005fcc; }
    
    .message {
        padding: 16px; border-radius: 8px; font-weight: 600; margin-bottom: var(--gap);
        border: 1px solid;
    }
    .message.success { background-color: #f0fdf4; color: #166534; border-color: #a7f3d0; }
    .message.error { background-color: #fef2f2; color: #b91c1c; border-color: #fecaca; }

    footer { text-align: center; padding: 30px; background-color: #fff; color: var(--muted-text); border-top: 1px solid var(--border-color); }
  </style>
</head>
<body>

  <main>
    <div class="page-container">
        <section class="section">
            <div class="section-title">
                <h1>Get in Touch</h1>
                <p>Have questions about joining a club, reporting an issue, or just want to say hello? Send us a message and our team will get back to you shortly.</p>
            </div>

            <div class="contact-grid">
                <div class="info-panel">
                    <h2>Contact Information</h2>
                    <p>Find us at our campus office or reach out via phone or email during business hours.</p>
                    <ul class="info-list">
                        <li class="info-item">
                            <i class="fas fa-map-marker-alt icon"></i>
                            <div>
                                <strong>Campus Office</strong>
                                <span>123 University Drive, Dhaka, BD</span>
                            </div>
                        </li>
                        <li class="info-item">
                            <i class="fas fa-envelope icon"></i>
                            <div>
                                <strong>Email Us</strong>
                                <span>contact@uiucms.edu</span>
                            </div>
                        </li>
                        <li class="info-item">
                            <i class="fas fa-phone icon"></i>
                            <div>
                                <strong>Call Us</strong>
                                <span>+880 123 456 7890</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="form-panel">
                    <?php if ($message): ?>
                        <div class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="Contact.php" class="form-grid" novalidate>
                        <div class="form-row">
                            <div>
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div>
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        <div>
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        <div>
                            <label for="message">Your Message</label>
                            <textarea id="message" name="message" required></textarea>
                        </div>
                        <div>
                            <button class="btn-primary" type="submit">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
  </main>
  
  <footer>
    <p>&copy; <?php echo date('Y'); ?> Club Management System | All Rights Reserved</p>
  </footer>
  
</body>
</html>