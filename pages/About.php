<?php
// pages/About.php - FINAL NEXT-LEVEL REDESIGN
if (session_status() === PHP_SESSION_NONE) session_start();
include realpath(__DIR__ . '/../db.php');

// --- DYNAMICALLY FETCH KPIS (Key Performance Indicators) ---
$totalClubs = 0;
$totalMembers = 0;
$totalEvents = 0;

if (isset($conn) && $conn instanceof mysqli) {
    // Count total unique members across all club member tables
    $sqlMembers = "SELECT COUNT(*) FROM (
        SELECT user_id FROM app_forum_members
        UNION
        SELECT user_id FROM computer_club_members
        UNION
        SELECT user_id FROM social_service_members
        UNION
        SELECT user_id FROM robotic_club_members
    ) AS all_unique_members";
    if ($result = $conn->query($sqlMembers)) {
        $totalMembers = $result->fetch_row()[0];
    }

    // Count total events
    if ($result = $conn->query("SELECT COUNT(*) FROM events")) {
        $totalEvents = $result->fetch_row()[0];
    }
    
    // Count total clubs (assuming one table per club for members)
    // A better approach would be a dedicated 'clubs' table, but this works for now.
    $clubTables = ['app_forum_members', 'computer_club_members', 'social_service_members', 'robotic_club_members'];
    $totalClubs = count($clubTables);
}

// Render the modern, self-contained navbar
include __DIR__ . '/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - Club Management System</title>
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
        --card-hover-shadow: 0 10px 15px -3px rgba(0,0,0,0.07), 0 4px 6px -4px rgba(0,0,0,0.07);
        --border-radius: 12px;
        --container-width: 1100px;
        --gap: 24px;
    }
    *, *::before, *::after { box-sizing: border-box; }
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #fff;
        color: var(--text-color);
        line-height: 1.6;
    }
    img { max-width: 100%; display: block; }
    .page-container {
        max-width: var(--container-width);
        margin: 0 auto;
        padding: 0 var(--gap);
    }
    .section { padding: 80px 0; }
    .section.bg-light { background-color: var(--light-gray); }
    .section-title { text-align: center; margin-bottom: 50px; }
    .section-title h2 { font-size: 2.5rem; font-weight: 800; color: var(--dark-blue); margin: 0 0 10px; }
    .section-title p { font-size: 1.1rem; color: var(--muted-text); max-width: 600px; margin: 0 auto; }

    /* Hero Section */
    .about-hero {
        padding: 80px 0;
        text-align: center;
    }
    .about-hero .pill {
        display: inline-block; padding: 8px 16px; border-radius: 999px;
        background-color: #eff6ff; color: var(--primary-color);
        font-weight: 700; font-size: 0.9rem; margin-bottom: 16px;
    }
    .about-hero h1 { font-size: 3.5rem; color: var(--dark-blue); margin: 0 0 16px; letter-spacing: -1px; }
    .about-hero p.lead { color: var(--muted-text); font-size: 1.2rem; max-width: 700px; margin: 0 auto 24px; }
    
    /* Stats Section */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--gap);
        max-width: 800px;
        margin: 40px auto 0;
    }
    @media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } }
    .stat-card {
        background-color: #fff;
        padding: 24px;
        border-radius: var(--border-radius);
        text-align: center;
        border: 1px solid var(--border-color);
    }
    .stat-card strong {
        display: block; font-size: 2.5rem; color: var(--primary-color);
        font-weight: 800; line-height: 1;
    }
    .stat-card span { font-size: 1rem; color: var(--muted-text); }

    /* Features Section */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--gap);
    }
    @media (max-width: 900px) { .features-grid { grid-template-columns: 1fr; } }
    .feature-card {
        background-color: #fff;
        padding: 32px;
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
        text-align: center;
    }
    .feature-card .icon {
        font-size: 2rem; color: var(--primary-color);
        margin-bottom: 16px;
        width: 64px; height: 64px;
        background-color: #eff6ff;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .feature-card h3 { font-size: 1.25rem; margin: 0 0 8px; color: var(--dark-blue); }
    .feature-card p { margin: 0; }
    
    /* CTA Section */
    .cta-section {
        background-color: var(--dark-blue);
        color: #fff;
        border-radius: var(--border-radius);
        padding: 60px;
        text-align: center;
    }
    .cta-section h2 { font-size: 2.25rem; margin: 0 0 16px; }
    .cta-section p { max-width: 600px; margin: 0 auto 30px; color: #cbd5e1; }
    .cta-section .btn-primary {
        text-decoration: none; padding: 14px 32px; border-radius: 50px;
        font-weight: 700; background-color: var(--primary-color); color: #fff;
        transition: all 0.2s ease;
    }
    .cta-section .btn-primary:hover { background-color: #2563eb; transform: translateY(-2px); }

    footer { text-align: center; padding: 30px; background-color: #fff; color: var(--muted-text); border-top: 1px solid var(--border-color); }
  </style>
</head>
<body>

  <main>
    <section class="section about-hero">
        <div class="page-container">
            <span class="pill">Our Mission</span>
            <h1>We make student life visible and simple.</h1>
            <p class="lead">
                The Club Management System is a centralized platform designed to help university clubs plan events, manage members, and publish beautiful public pages. Our goal is to make it easy for students to discover activities and get involved with confidence.
            </p>
            <div class="stats-grid">
                <div class="stat-card">
                    <strong><?php echo (int)$totalClubs; ?></strong>
                    <span>Active Clubs</span>
                </div>
                <div class="stat-card">
                    <strong><?php echo (int)$totalMembers; ?></strong>
                    <span>Total Members</span>
                </div>
                <div class="stat-card">
                    <strong><?php echo (int)$totalEvents; ?></strong>
                    <span>Events Hosted</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section bg-light">
        <div class="page-container">
            <div class="section-title">
                <h2>Core Features</h2>
                <p>A lightweight, accessible, and easy-to-use system for both club administrators and students.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="icon"><i class="fas fa-globe-americas"></i></div>
                    <h3>Public Club Pages</h3>
                    <p>Each club gets a discoverable hub that showcases their members, upcoming events, and past activities to attract new participants.</p>
                </div>
                <div class="feature-card">
                    <div class="icon"><i class="fas fa-users-cog"></i></div>
                    <h3>Role-Aware Access</h3>
                    <p>Admins, members, and guests see different controls and content. All administrative actions are protected for security and clarity.</p>
                </div>
                <div class="feature-card">
                    <div class="icon"><i class="fas fa-server"></i></div>
                    <h3>Lightweight & Simple</h3>
                    <p>Runs on standard web servers like XAMPP. Perfect for student projects, demos, and clubs that need a fast, maintainable solution.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="page-container">
            <div class="cta-section">
                <h2>Ready to Explore?</h2>
                <p>Browse our club pages, check out the upcoming events, or contact us if you'd like this system installed for your own club.</p>
                <a href="index.php#clubs" class="btn-primary">Explore All Clubs</a>
            </div>
        </div>
    </section>
  </main>
  
  <footer>
    <p>&copy; <?php echo date('Y'); ?> Club Management System | All Rights Reserved</p>
  </footer>
  
</body>
</html>