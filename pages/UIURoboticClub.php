<?php
// pages/UIURoboticClub.php - FINAL NEXT-LEVEL REDESIGN
if (session_status() === PHP_SESSION_NONE) session_start();
include realpath(__DIR__ . '/../db.php');

$clubId = 4; // UIU Robotic Club

// --- Fetch Total Member Count ---
$memberCount = 0;
if ($stmt = $conn->prepare("SELECT COUNT(*) FROM robotic_club_members WHERE club_id = ?")) {
    $stmt->bind_param("i", $clubId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $memberCount = $count;
    $stmt->close();
}

// --- Fetch Members with All Details for New Card Design ---
$members = [];
$searchQuery = trim($_GET['q'] ?? '');
$sql = "SELECT u.user_id, u.name, u.email, rcm.position, rcm.joined_at
        FROM robotic_club_members rcm
        JOIN users u ON rcm.user_id = u.user_id
        WHERE rcm.club_id = ?";

if (!empty($searchQuery)) {
    $searchTerm = "%{$searchQuery}%";
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR rcm.position LIKE ?)";
    $sql .= " ORDER BY u.name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $clubId, $searchTerm, $searchTerm, $searchTerm);
} else {
    // Sort by role importance first, then by name
    $sql .= " ORDER BY CASE 
                WHEN rcm.position LIKE 'President' THEN 1
                WHEN rcm.position LIKE 'Vice President' THEN 2
                WHEN rcm.position LIKE '%Coordinator%' THEN 3
                WHEN rcm.position LIKE '%Head%' THEN 4
                ELSE 5
              END, u.name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $clubId);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}
$stmt->close();

// --- Fetch Upcoming Events ---
$upcomingEvents = [];
$sqlUpcoming = "SELECT event_id, title, event_date, location 
                FROM events 
                WHERE club_id = ? AND event_date >= CURDATE() 
                ORDER BY event_date ASC LIMIT 4";
if ($stmt = $conn->prepare($sqlUpcoming)) {
    $stmt->bind_param("i", $clubId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $upcomingEvents[] = $row;
    }
    $stmt->close();
}

// --- Fetch Recent Events ---
$recentEvents = [];
$sqlRecent = "SELECT event_id, title, description, image 
              FROM events 
              WHERE club_id = ? AND event_date < CURDATE() 
              ORDER BY event_date DESC LIMIT 3";
if ($stmt = $conn->prepare($sqlRecent)) {
    $stmt->bind_param("i", $clubId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recentEvents[] = $row;
    }
    $stmt->close();
}

// Render the modern, self-contained navbar
include __DIR__ . '/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UIU Robotic Club - The Official Hub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    :root {
        --primary-color: #ca8a04;
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
        background-color: var(--light-gray);
        color: var(--text-color);
        line-height: 1.6;
    }
    img { max-width: 100%; display: block; }
    
    .club-hero {
        background: linear-gradient(45deg, rgba(15, 23, 42, 0.9), rgba(60, 50, 20, 0.9)), url('../images/club_banner_robotic.jpg') no-repeat center center/cover;
        color: #fff; padding: 100px 20px; text-align: center;
    }
    .club-hero h1 {
        font-size: 3.5rem; font-weight: 800; margin: 0 0 10px; text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
    }
    .club-hero p {
        font-size: 1.2rem; max-width: 650px; margin: 0 auto 30px; opacity: 0.9;
    }
    .hero-stats {
        display: inline-flex; align-items: center; gap: 10px;
        background: rgba(255,255,255,0.1); padding: 12px 24px;
        border-radius: 50px; font-weight: 600; border: 1px solid rgba(255,255,255,0.2);
    }
    .hero-stats i { color: #facc15; }

    .page-container {
        max-width: var(--container-width); margin: 60px auto; padding: 0 var(--gap);
        display: grid; grid-template-columns: 1fr 360px; gap: 40px; align-items: start;
    }
    @media (max-width: 1024px) {
        .page-container { grid-template-columns: 1fr; }
    }
    .card {
        background-color: #fff; border-radius: var(--border-radius);
        box-shadow: var(--card-shadow); transition: transform 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
    }
    .card:hover { transform: translateY(-5px); box-shadow: var(--card-hover-shadow); }
    .section-header {
        display: flex; justify-content: space-between; align-items: center;
        gap: 20px; margin-bottom: var(--gap); flex-wrap: wrap;
    }
    .section-header h2 { margin: 0; font-size: 1.75rem; color: var(--dark-blue); }

    .search-bar input {
        width: 320px; padding: 12px 20px 12px 45px; border: 1px solid var(--border-color);
        border-radius: 50px; font-size: 1rem; outline-color: var(--primary-color);
    }
    .search-bar { position: relative; }
    .search-bar i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--muted-text); }
    
    .members-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: var(--gap); }
    .member-card {
        display: flex; align-items: flex-start; gap: 20px; padding: 24px;
    }
    .member-card img {
        width: 64px; height: 64px; border-radius: 50%; object-fit: cover; flex-shrink: 0;
    }
    .member-details { display: flex; flex-direction: column; }
    .member-details h3 {
        margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--dark-blue); line-height: 1.2;
    }
    .member-details .position,
    .member-details .email,
    .member-details .joined {
        margin: 0; font-size: 0.9rem; color: var(--muted-text); line-height: 1.5;
    }
    .member-details .position {
        font-weight: 600; color: var(--primary-color); margin-top: 2px;
    }
    .member-details .email { margin-top: 8px; }
    
    .sidebar-content h2 { font-size: 1.5rem; color: var(--dark-blue); margin: 0 0 20px; }
    .sidebar-section { margin-bottom: 40px; }
    .events-list { display: grid; gap: var(--gap); }
    .event-card-upcoming { display: flex; gap: 16px; align-items: center; padding: 16px; }
    .event-date {
        flex-shrink: 0; text-align: center; background-color: #f1f5f9;
        border-radius: var(--border-radius); width: 64px; height: 64px;
        display: flex; flex-direction: column; justify-content: center;
    }
    .event-date .month { font-size: 0.8rem; font-weight: 700; color: #ef4444; text-transform: uppercase; }
    .event-date .day { font-size: 1.75rem; font-weight: 800; color: var(--dark-blue); line-height: 1; }
    .event-details h3 { margin: 0 0 4px; font-size: 1.05rem; color: var(--dark-blue); }
    .event-details p { margin: 0; font-size: 0.9rem; color: var(--muted-text); display:flex; align-items:center; gap: 6px; }
    
    .event-card-recent img { height: 180px; width: 100%; object-fit: cover; }
    .event-card-recent .card-content { padding: 20px; }
    .event-card-recent h3 { margin: 0 0 8px; font-size: 1.2rem; color: var(--dark-blue); }
    .event-card-recent p { margin: 0; font-size: 0.9rem; color: var(--muted-text); }
    
    .no-results {
        grid-column: 1 / -1; background-color: #fff; padding: 40px; text-align: center;
        border-radius: var(--border-radius); color: var(--muted-text); border: 2px dashed var(--border-color);
    }
    
    footer { text-align: center; padding: 30px; background-color: var(--dark-blue); color: #94a3b8; }
  </style>
</head>
<body>

  <section class="club-hero">
    <h1>UIU Robotic Club</h1>
    <p>Build the Future. Join a team of innovators and engineers passionate about robotics, automation, and artificial intelligence.</p>
    <div class="hero-stats">
        <i class="fas fa-robot"></i>
        <span><?php echo $memberCount; ?> Active Members</span>
    </div>
  </section>

  <main class="page-container">
    <div class="main-content">
      <div class="section-header">
        <h2>Our Team</h2>
        <form method="GET" action="UIURoboticClub.php" class="search-bar">
          <i class="fas fa-search"></i>
          <input type="text" name="q" placeholder="Search by name, email, or position..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        </form>
      </div>
      
      <?php if (!empty($members)): ?>
        <div class="members-grid">
          <?php foreach ($members as $member): ?>
            <div class="card member-card">
              <img src="../images/PresidentProfile1.png" alt="<?php echo htmlspecialchars($member['name']); ?>">
              <div class="member-details">
                <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                <p class="position"><?php echo htmlspecialchars($member['position'] ?: 'Member'); ?></p>
                <p class="email"><?php echo htmlspecialchars($member['email']); ?></p>
                <p class="joined">Joined: <?php echo date("F j, Y", strtotime($member['joined_at'])); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="no-results">
          <h3>No Members Found</h3>
          <p>Your search for "<?php echo htmlspecialchars($searchQuery); ?>" did not match any members.</p>
        </div>
      <?php endif; ?>
    </div>

    <aside class="sidebar-content">
      <div class="sidebar-section">
        <h2><i class="fas fa-calendar-alt" style="color: var(--primary-color); margin-right: 8px;"></i>Upcoming Workshops</h2>
        <div class="events-list">
          <?php if (!empty($upcomingEvents)): ?>
            <?php foreach ($upcomingEvents as $event): ?>
              <div class="card event-card-upcoming">
                <div class="event-date">
                    <span class="month"><?php echo date('M', strtotime($event['event_date'])); ?></span>
                    <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                </div>
                <div class="event-details">
                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="color: var(--muted-text);">No upcoming workshops scheduled.</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="sidebar-section">
        <h2><i class="fas fa-history" style="color: var(--primary-color); margin-right: 8px;"></i>Past Competitions</h2>
        <div class="events-list">
          <?php if (!empty($recentEvents)): ?>
            <?php foreach ($recentEvents as $event): 
                $desc = htmlspecialchars($event['description'] ?? '');
                $shortDesc = mb_strlen($desc) > 80 ? mb_substr($desc, 0, 80) . '...' : $desc;
                $imageUrl = '../' . (!empty($event['image']) ? ltrim(str_replace('../', '', $event['image']), '/') : 'images/default_event.png');
            ?>
              <div class="card event-card-recent">
                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                <div class="card-content">
                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                    <p><?php echo $shortDesc; ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="color: var(--muted-text);">No past competitions to show.</p>
          <?php endif; ?>
        </div>
      </div>
    </aside>
  </main>

  <footer>
    <p>&copy; <?php echo date('Y'); ?> UIU Robotic Club | All Rights Reserved</p>
  </footer>

</body>
</html>
