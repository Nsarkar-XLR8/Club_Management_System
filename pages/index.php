<?php
// pages/index.php - FINAL NEXT-LEVEL REDESIGN
if (session_status() === PHP_SESSION_NONE) session_start();
include realpath(__DIR__ . '/../db.php');

// --- Fetch Upcoming Events (using dates, not 'type') ---
// $upcomingEvents = [];
// if (isset($conn) && $conn instanceof mysqli) {
//     $sqlUpcoming = "SELECT event_id, title, event_date, location, club_id FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 4";
//     if ($stmt = $conn->prepare($sqlUpcoming)) 
//         {
//         $stmt->execute();
//         $result = $stmt->get_result();
//         while ($row = $result->fetch_assoc()) {
//             $upcomingEvents[] = $row;
//         }
//         $stmt->close();
//     }
// }

// --- Fetch Recent Events (using dates, not 'type') ---
// $recentEvents = [];
// if (isset($conn) && $conn instanceof mysqli) {
//     $sqlRecent = "SELECT event_id, title, description, image, club_id FROM events WHERE event_date < CURDATE() ORDER BY event_date DESC LIMIT 3";
//     if ($stmt = $conn->prepare($sqlRecent)) {
//         $stmt->execute();
//         $result = $stmt->get_result();
//         while ($row = $result->fetch_assoc()) {
//             $recentEvents[] = $row;
//         }
//         $stmt->close();
//     }
// }

// Define Club information - this makes the links and names easy to manage
$clubs = [
    1 => ['name' => 'UIU App Forum', 'url' => 'UIUAppForum.php', 'icon' => 'fa-laptop-code'],
    2 => ['name' => 'UIU Computer Club', 'url' => 'UIUComputerClub.php', 'icon' => 'fa-desktop'],
    3 => ['name' => 'UIU Social Service Club', 'url' => 'SocialServiceClub.php', 'icon' => 'fa-hands-helping'],
    4 => ['name' => 'UIU Robotic Club', 'url' => 'UIURoboticClub.php', 'icon' => 'fa-robot'],
];

// Render the modern, self-contained navbar
include __DIR__ . '/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to the Club Management System</title>
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
        --container-width: 1280px;
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
    
    /* --- Hero Section --- */
    .hero {
        background: var(--dark-blue);
        color: #fff;
        padding: 120px 20px 80px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-image: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.3) 0%, transparent 40%),
                          radial-gradient(circle at 90% 80%, rgba(37, 99, 235, 0.3) 0%, transparent 40%);
        opacity: 0.5;
    }
    .hero-content { position: relative; z-index: 1; }
    .hero h1 {
        font-size: 3.5rem; font-weight: 800; margin: 0 0 10px;
        letter-spacing: -1px;
    }
    .hero p {
        font-size: 1.2rem; max-width: 600px;
        margin: 0 auto 30px; color: #cbd5e1;
    }
    .hero-actions a {
        text-decoration: none;
        padding: 14px 32px;
        border-radius: 50px;
        font-weight: 700;
        margin: 0 10px;
        transition: all 0.2s ease;
    }
    .hero-actions .btn-primary { background-color: var(--primary-color); color: #fff; }
    .hero-actions .btn-primary:hover { background-color: #2563eb; transform: translateY(-2px); }
    .hero-actions .btn-secondary { background-color: rgba(255,255,255,0.1); color: #fff; }
    .hero-actions .btn-secondary:hover { background-color: rgba(255,255,255,0.2); }
    
    /* --- Section Styling --- */
    .section { padding: 80px 0; }
    .section.bg-light { background-color: var(--light-gray); }
    .section-title {
        text-align: center;
        margin-bottom: 50px;
    }
    .section-title h2 {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--dark-blue);
        margin: 0 0 10px;
    }
    .section-title p {
        font-size: 1.1rem;
        color: var(--muted-text);
        max-width: 600px;
        margin: 0 auto;
    }
    
    /* --- Featured Clubs --- */
    .clubs-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: var(--gap);
    }
    @media (max-width: 1024px) { .clubs-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 640px) { .clubs-grid { grid-template-columns: 1fr; } }
    .club-card {
        background-color: #fff;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        text-align: center;
        padding: 40px 20px;
        text-decoration: none;
        color: var(--text-color);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .club-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--card-hover-shadow);
    }
    .club-card .icon {
        font-size: 2.5rem;
        color: var(--primary-color);
        margin-bottom: 16px;
    }
    .club-card h3 {
        margin: 0 0 8px;
        font-size: 1.25rem;
        color: var(--dark-blue);
    }

    /* --- Events Feed --- */
    .events-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 40px;
        align-items: start;
    }
    @media (max-width: 900px) { .events-grid { grid-template-columns: 1fr; } }
    
    /* Upcoming Events */
    .events-list { display: grid; gap: var(--gap); }
    .event-card-upcoming {
        background-color: #fff; border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        display: flex; gap: 16px; align-items: center; padding: 16px;
    }
    .event-date {
        flex-shrink: 0; text-align: center; background-color: var(--light-gray);
        border-radius: var(--border-radius); width: 64px; height: 64px;
        display: flex; flex-direction: column; justify-content: center;
    }
    .event-date .month { font-size: 0.8rem; font-weight: 700; color: #ef4444; text-transform: uppercase; }
    .event-date .day { font-size: 1.75rem; font-weight: 800; color: var(--dark-blue); line-height: 1; }
    .event-details h3 { margin: 0 0 4px; font-size: 1.05rem; color: var(--dark-blue); }
    .event-details p { margin: 0; font-size: 0.9rem; color: var(--muted-text); display:flex; align-items:center; gap: 6px; }

    /* Recent Events */
    .recent-events-list { display: grid; gap: var(--gap); }
    .event-card-recent {
        background-color: #fff; border-radius: var(--border-radius);
        box-shadow: var(--card-shadow); overflow: hidden;
    }
    .event-card-recent img { height: 200px; width: 100%; object-fit: cover; }
    .event-card-recent .card-content { padding: 20px; }
    .event-card-recent h3 { margin: 0 0 8px; font-size: 1.2rem; color: var(--dark-blue); }
    .event-card-recent p { margin: 0; font-size: 0.9rem; color: var(--muted-text); }
    
    footer { text-align: center; padding: 30px; background-color: var(--dark-blue); color: #94a3b8; }
  </style>
</head>
<body>
  
  <main>
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to the UIU Club Hub</h1>
            <p>Your central platform for connecting with clubs, staying updated on events, and getting involved in campus life.</p>
            <div class="hero-actions">
                <a href="#clubs" class="btn-primary">Explore Clubs</a>
                <a href="Login.php" class="btn-secondary">Member Login</a>
            </div>
        </div>
    </section>

    <section id="clubs" class="section page-container">
        <div class="section-title">
            <h2>Featured Clubs</h2>
            <p>Discover a community that shares your passion. Join a club and start your journey today.</p>
        </div>
        <div class="clubs-grid">
            <?php foreach ($clubs as $club): ?>
                <a href="<?php echo htmlspecialchars($club['url']); ?>" class="club-card">
                    <div class="icon"><i class="fas <?php echo htmlspecialchars($club['icon']); ?>"></i></div>
                    <h3><?php echo htmlspecialchars($club['name']); ?></h3>
                    <p>Click to learn more</p>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="events" class="section bg-light">
        <div class="page-container">
            <div class="section-title">
                <h2>Campus Events Feed</h2>
                <p>Stay up-to-date with the latest activities and workshops happening across all clubs.</p>
            </div>
            <div class="events-grid">
                <div class="recent-events-list">
                    <?php if (!empty($recentEvents)): foreach ($recentEvents as $event): 
                        $shortDesc = mb_strlen($event['description']) > 100 ? mb_substr($event['description'], 0, 100) . '...' : $event['description'];
                        $imageUrl = '../' . (!empty($event['image']) ? ltrim(str_replace('../', '', $event['image']), '/') : 'images/default_event.png');
                        $clubInfo = $clubs[$event['club_id']] ?? null;
                    ?>
                        <div class="event-card-recent">
                            <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="">
                            <div class="card-content">
                                <?php if ($clubInfo): ?>
                                    <p style="font-weight:600; color:var(--primary-color); font-size:0.8rem; margin-bottom:8px;"><?php echo htmlspecialchars($clubInfo['name']); ?></p>
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p><?php echo htmlspecialchars($shortDesc); ?></p>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <p>No recent events to show.</p>
                    <?php endif; ?>
                </div>

                <aside class="upcoming-events-sidebar">
                    <h3 style="font-size: 1.5rem; color: var(--dark-blue); margin-top:0; margin-bottom:var(--gap);">Upcoming</h3>
                    <div class="events-list">
                      <?php if (!empty($upcomingEvents)): ?>
                        <?php foreach ($upcomingEvents as $event): 
                            $clubInfo = $clubs[$event['club_id']] ?? null;
                        ?>
                          <div class="event-card-upcoming">
                            <div class="event-date">
                                <span class="month"><?php echo date('M', strtotime($event['event_date'])); ?></span>
                                <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                            </div>
                            <div class="event-details">
                                <?php if ($clubInfo): ?>
                                    <p style="font-weight:600; color:var(--primary-color); font-size:0.8rem; margin:0;"><?php echo htmlspecialchars($clubInfo['name']); ?></p>
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <p style="color: var(--muted-text);">No upcoming events scheduled.</p>
                      <?php endif; ?>
                    </div>
                </aside>
            </div>
        </div>
    </section>
  </main>

  <footer>
    <p>&copy; <?php echo date('Y'); ?> Club Management System | All Rights Reserved</p>
  </footer>

</body>
</html>