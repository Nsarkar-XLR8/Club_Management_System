<?php
// pages/SocialServiceClub.php - FINAL PERFECTED VERSION with ALL FEATURES
if (session_status() === PHP_SESSION_NONE) session_start();
include realpath(__DIR__ . '/../db.php');

$clubId = 3; // UIU Social Service Club

// --- API LOGIC: Handle Donor Search Requests ---
if (isset($_GET['action']) && $_GET['action'] === 'search_donors') {
    header("Content-Type: application/json");

    $bloodGroup = $_GET['blood_group'] ?? '';
    $location = $_GET['location'] ?? '';

    $where = ["club_id = ?"]; // ALWAYS filter by the current club
    $params = [$clubId];
    $types = "i";

    if (!empty($bloodGroup)) {
        $where[] = "blood_group = ?";
        $params[] = $bloodGroup;
        $types .= "s";
    }
    if (!empty($location)) {
        $where[] = "permanent_address LIKE ?";
        $params[] = "%" . $location . "%";
        $types .= "s";
    }

    $sql = "SELECT full_name, permanent_address, contact_number, blood_group FROM donors WHERE " . implode(" AND ", $where) . " LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $donors = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($donors);
    exit; 
}

// --- STANDARD PAGE LOGIC ---
$memberCount = 0;
if ($stmt = $conn->prepare("SELECT COUNT(*) FROM social_service_members WHERE club_id = ?")) {
    $stmt->bind_param("i", $clubId); $stmt->execute(); $stmt->bind_result($count);
    $stmt->fetch(); $memberCount = $count; $stmt->close();
}

$members = [];
$searchQuery = trim($_GET['q'] ?? '');
$sql_members = "SELECT u.user_id, u.name, u.email, ssm.position, ssm.joined_at FROM social_service_members ssm JOIN users u ON ssm.user_id = u.user_id WHERE ssm.club_id = ?";
if (!empty($searchQuery)) {
    $searchTerm = "%{$searchQuery}%"; $sql_members .= " AND (u.name LIKE ? OR u.email LIKE ? OR ssm.position LIKE ?)";
    $stmt = $conn->prepare($sql_members);
    $stmt->bind_param("isss", $clubId, $searchTerm, $searchTerm, $searchTerm);
} else {
    $sql_members .= " ORDER BY CASE WHEN ssm.position LIKE 'President' THEN 1 ELSE 2 END, u.name ASC";
    $stmt = $conn->prepare($sql_members);
    $stmt->bind_param("i", $clubId);
}
$stmt->execute(); $result = $stmt->get_result();
while ($row = $result->fetch_assoc()) { $members[] = $row; }
$stmt->close();

$upcomingEvents = [];
$sqlUpcoming = "SELECT event_id, title, event_date, location FROM social_service_events WHERE club_id = ? AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 4";
if ($stmt = $conn->prepare($sqlUpcoming)) {
    $stmt->bind_param("i", $clubId); $stmt->execute(); $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) { $upcomingEvents[] = $row; } $stmt->close();
}

$recentEvents = [];
$sqlRecent = "SELECT event_id, title, description, image FROM social_service_events WHERE club_id = ? AND event_date < CURDATE() ORDER BY event_date DESC LIMIT 3";
if ($stmt = $conn->prepare($sqlRecent)) {
    $stmt->bind_param("i", $clubId); $stmt->execute(); $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) { $recentEvents[] = $row; } $stmt->close();
}

include __DIR__ . '/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UIU Social Service Club - The Official Hub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    :root {
        --primary-color: #16a34a; --dark-blue: #0f172a; --light-gray: #f1f5f9;
        --text-color: #334155; --muted-text: #64748b; --border-color: #e2e8f0;
        --card-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
        --card-hover-shadow: 0 10px 15px -3px rgba(0,0,0,0.07), 0 4px 6px -4px rgba(0,0,0,0.07);
        --border-radius: 12px; --container-width: 1280px; --gap: 24px;
    }
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'Poppins', sans-serif; background-color: var(--light-gray); color: var(--text-color); line-height: 1.6; }
    .page-container { max-width: var(--container-width); margin: 60px auto; padding: 0 var(--gap); }
    
    .club-hero {
        background: linear-gradient(45deg, rgba(15, 23, 42, 0.9), rgba(22, 163, 74, 0.9)), url('../images/club_banner_social.jpg') no-repeat center center/cover;
        color: #fff; padding: 100px 20px; text-align: center;
    }
    .club-hero h1 { font-size: 3.5rem; font-weight: 800; margin: 0 0 10px; }
    .club-hero p { font-size: 1.2rem; max-width: 650px; margin: 0 auto 30px; opacity: 0.9; }
    .hero-stats { display: inline-flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.1); padding: 12px 24px; border-radius: 50px; font-weight: 600; }
    .hero-stats i { color: #86efac; }
    
    .card { background-color: #fff; border-radius: var(--border-radius); box-shadow: var(--card-shadow); overflow: hidden; }
    .card:hover { transform: translateY(-5px); box-shadow: var(--card-hover-shadow); }
    .section-header { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: var(--gap); flex-wrap: wrap; }
    .section-header h2 { margin: 0; font-size: 1.75rem; color: var(--dark-blue); }

    .donor-search-section { margin-bottom: 60px; }
    .search-form-card { padding: 32px; margin-bottom: var(--gap); }
    .search-form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: var(--gap); align-items: end; }
    .search-form-grid label { font-weight: 600; margin-bottom: 8px; display: block; }
    .search-form-grid input, .search-form-grid select { width: 100%; padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 1rem; }
    .search-btn { background-color: var(--primary-color); color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 700; font-size: 1rem; cursor: pointer; }
    .donors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: var(--gap); }
    .donor-card { padding: 24px; display: flex; gap: 20px; align-items: center; }
    .donor-blood-group { font-size: 1.5rem; font-weight: 800; color: #fff; background-color: #ef4444; width: 64px; height: 64px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .donor-details h3 { margin: 0; font-size: 1.2rem; color: var(--dark-blue); }
    .donor-details p { margin: 2px 0 0; color: var(--muted-text); font-size: 0.9rem; }
    
    .main-grid { display: grid; grid-template-columns: 1fr 360px; gap: 40px; align-items: start; }
    @media (max-width: 1024px) { .main-grid { grid-template-columns: 1fr; } }
    
    .members-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: var(--gap); }
    .member-card { display: flex; align-items: flex-start; gap: 20px; padding: 24px; }
    .member-card img { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
    .member-details h3 { margin: 0; font-size: 1.25rem; color: var(--dark-blue); }
    .member-details p { margin: 0; font-size: 0.9rem; color: var(--muted-text); }
    .member-details .position { font-weight: 600; color: var(--primary-color); margin-top: 2px; }
    .member-details .email { margin-top: 8px; }

    .sidebar-content h2 { font-size: 1.5rem; margin: 0 0 20px; }
    .sidebar-section { margin-bottom: 40px; }
    .events-list { display: grid; gap: var(--gap); }
    .event-card-upcoming { display: flex; gap: 16px; align-items: center; padding: 16px; }
    .event-date { flex-shrink: 0; text-align: center; background-color: #f1f5f9; border-radius: 12px; width: 64px; height: 64px; display: flex; flex-direction: column; justify-content: center; }
    .event-date .month { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; }
    .event-date .day { font-size: 1.75rem; font-weight: 800; line-height: 1; }
    .event-details h3 { margin: 0 0 4px; font-size: 1.05rem; }
    .event-details p { margin: 0; font-size: 0.9rem; }
    .event-card-recent img { height: 180px; width: 100%; object-fit: cover; }
    .event-card-recent .card-content { padding: 20px; }
    .no-results { background-color: #fff; padding: 40px; text-align: center; border-radius: var(--border-radius); color: var(--muted-text); border: 2px dashed var(--border-color); }
    
    footer { text-align: center; padding: 30px; background-color: var(--dark-blue); color: #94a3b8; }
  </style>
</head>
<body>

  <section class="club-hero">
    <h1>UIU Social Service Club</h1>
    <p>Making a Difference, Together. Join us to serve the community and create a positive impact.</p>
    <div class="hero-stats"><i class="fas fa-hands-helping"></i><span><?php echo $memberCount; ?> Active Volunteers</span></div>
  </section>

  <div class="page-container">
    <section class="donor-search-section">
        <div class="section-header">
            <h2 style="font-size: 2.5rem;">Find a Blood Donor</h2>
        </div>
        <div class="card search-form-card">
            <div class="search-form-grid">
                <div>
                    <label for="bloodGroupSearch">Blood Group</label>
                    <select id="bloodGroupSearch">
                        <option value="">Any</option>
                        <option value="A+">A+</option><option value="A-">A-</option>
                        <option value="B+">B+</option><option value="B-">B-</option>
                        <option value="AB+">AB+</option><option value="AB-">AB-</option>
                        <option value="O+">O+</option><option value="O-">O-</option>
                    </select>
                </div>
                <div>
                    <label for="locationSearch">Location (City/Area)</label>
                    <input type="text" id="locationSearch" placeholder="e.g., Dhaka">
                </div>
                <div>
                    <button id="searchDonorsBtn" class="search-btn"><i class="fas fa-search"></i> Search</button>
                </div>
            </div>
        </div>
        <div id="donor-results-container">
            <p style="text-align:center; color: var(--muted-text);">Search for donors using the form above.</p>
        </div>
    </section>

    <div class="main-grid">
      <main>
        <div class="section-header">
            <h2>Our Volunteers</h2>
            <form method="GET" action="SocialServiceClub.php" style="position:relative;">
                <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--muted-text);"></i>
                <input type="text" name="q" placeholder="Search volunteers..." value="<?php echo htmlspecialchars($searchQuery); ?>" style="width: 300px; padding: 10px 15px 10px 40px; border: 1px solid var(--border-color); border-radius: 50px;">
            </form>
        </div>
        <?php if (!empty($members)): ?>
        <div class="members-grid">
            <?php foreach ($members as $member): ?>
            <div class="card member-card">
                <img src="../images/PresidentProfile1.png" alt="<?php echo htmlspecialchars($member['name']); ?>">
                <div class="member-details">
                    <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                    <p class="position"><?php echo htmlspecialchars($member['position'] ?: 'Volunteer'); ?></p>
                    <p class="email"><?php echo htmlspecialchars($member['email']); ?></p>
                    <p class="joined">Joined: <?php echo date("F j, Y", strtotime($member['joined_at'])); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="no-results" style="grid-column: 1 / -1;">
            <h3>No Members Found</h3>
        </div>
        <?php endif; ?>
      </main>
      <aside class="sidebar-content">
        <div class="sidebar-section">
            <h2>Upcoming Initiatives</h2>
            <div class="events-list">
              <?php if (!empty($upcomingEvents)): foreach ($upcomingEvents as $event): ?>
              <div class="card event-card-upcoming">
                <div class="event-date"><span class="month"><?php echo date('M', strtotime($event['event_date'])); ?></span><span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span></div>
                <div class="event-details">
                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                </div>
              </div>
              <?php endforeach; else: ?>
              <p>No upcoming initiatives.</p>
              <?php endif; ?>
            </div>
        </div>
        <div class="sidebar-section">
            <h2>Past Activities</h2>
            <div class="events-list">
              <?php if (!empty($recentEvents)): foreach ($recentEvents as $event): ?>
              <div class="card event-card-recent">
                <img src="../<?php echo ltrim(str_replace('../','',$event['image']), '/'); ?>" alt="">
                <div class="card-content">
                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                    <p><?php echo mb_substr(htmlspecialchars($event['description']), 0, 80) . '...'; ?></p>
                </div>
              </div>
              <?php endforeach; else: ?>
              <p>No recent activities.</p>
              <?php endif; ?>
            </div>
        </div>
      </aside>
    </div>
  </div>
  
  <footer>
    <p>&copy; <?php echo date('Y'); ?> UIU Social Service Club</p>
  </footer>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const searchBtn = document.getElementById("searchDonorsBtn");
    const resultsContainer = document.getElementById("donor-results-container");
    
    searchBtn.addEventListener("click", async () => {
        const bloodGroup = document.getElementById("bloodGroupSearch").value;
        const location = document.getElementById("locationSearch").value;
        resultsContainer.innerHTML = '<p style="text-align:center;">Searching...</p>';
        
        const params = new URLSearchParams({ action: 'search_donors', blood_group: bloodGroup, location: location });
        try {
            const response = await fetch(`SocialServiceClub.php?${params}`);
            const donors = await response.json();
            resultsContainer.innerHTML = ""; 

            if (donors.length === 0) {
                resultsContainer.innerHTML = '<div class="no-results"><h3>No Donors Found</h3></div>';
                return;
            }
            const grid = document.createElement('div');
            grid.className = 'donors-grid';
            donors.forEach(d => {
                grid.innerHTML += `
                    <div class="card donor-card">
                        <div class="donor-blood-group">${d.blood_group}</div>
                        <div class="donor-details">
                            <h3>${d.full_name}</h3>
                            <p>${d.permanent_address}</p>
                            <p>${d.contact_number}</p>
                        </div>
                    </div>`;
            });
            resultsContainer.appendChild(grid);
        } catch (error) {
            resultsContainer.innerHTML = '<div class="no-results" style="border-color: #ef4444;"><h3>An Error Occurred</h3></div>';
        }
    });
});
</script>

</body>
</html>