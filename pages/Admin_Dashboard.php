<?php
// Admin_Dashboard.php - FINAL PERFECTED VERSION
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || strtolower(trim((string)($_SESSION['role'] ?? ''))) !== 'admin') {
    header('Location: Login.php?return_to=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

include realpath(__DIR__ . '/../db.php');

// --- SECURE KPI & METRICS QUERIES ---
function get_count(mysqli $conn, string $sql) {
    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_row()[0] ?? 0;
        $stmt->close();
        return (int)$count;
    }
    return 0;
}

// Global KPIs
$totalUsers     = get_count($conn, "SELECT COUNT(*) FROM users");
$upcomingEvents = get_count($conn, "SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()");
$totalAdmins    = get_count($conn, "SELECT COUNT(*) FROM users WHERE role = 'admin'");

// Detailed Club Metrics
$club_metrics = [
    1 => ['name' => 'UIU App Forum', 'url' => 'UIUAppForum.php', 'icon' => 'fa-laptop-code', 'members' => 0, 'events' => 0],
    2 => ['name' => 'UIU Computer Club', 'url' => 'UIUComputerClub.php', 'icon' => 'fa-desktop', 'members' => 0, 'events' => 0],
    3 => ['name' => 'UIU Social Service Club', 'url' => 'SocialServiceClub.php', 'icon' => 'fa-hands-helping', 'members' => 0, 'events' => 0],
    4 => ['name' => 'UIU Robotic Club', 'url' => 'UIURoboticClub.php', 'icon' => 'fa-robot', 'members' => 0, 'events' => 0],
];

// Member counts per club
$sql_members = "SELECT club_id, COUNT(user_id) as count FROM (
    SELECT club_id, user_id FROM app_forum_members
    UNION ALL SELECT club_id, user_id FROM computer_club_members
    UNION ALL SELECT club_id, user_id FROM social_service_members
    UNION ALL SELECT club_id, user_id FROM robotic_club_members
) as all_members GROUP BY club_id";
if ($result = $conn->query($sql_members)) {
    while ($row = $result->fetch_assoc()) {
        if (isset($club_metrics[$row['club_id']])) {
            $club_metrics[$row['club_id']]['members'] = $row['count'];
        }
    }
}

// Event counts per club
$sql_events = "SELECT club_id, COUNT(event_id) as count FROM events WHERE event_date >= CURDATE() GROUP BY club_id";
if ($result = $conn->query($sql_events)) {
    while ($row = $result->fetch_assoc()) {
        if (isset($club_metrics[$row['club_id']])) {
            $club_metrics[$row['club_id']]['events'] = $row['count'];
        }
    }
}

// Recent Activity Log
$activities = [];
$sql_log = "SELECT log_id, actor_name, action, description, timestamp FROM activity_log ORDER BY timestamp DESC LIMIT 7";
if ($stmt = $conn->prepare($sql_log)) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();
}

$adminName = htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? 'Admin User', ENT_QUOTES, 'UTF-8');
$siteBase = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if (basename($siteBase) === 'pages') $siteBase = dirname($siteBase);
$logoutUrl = $siteBase . '/pages/logout.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --sidebar-w: 260px; --gap: 24px; --panel-radius: 12px; --muted-text: #6b7280; 
            --primary-blue: #2563eb; --secondary-cyan: #06b6d4; --danger-red: #dc2626;
            --dark-blue: #0f172a;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: #f8fafc; color: #0f172a; }
        .app { display: flex; min-height: 100vh; }
        /* Sidebar and Nav styles from your other admin pages */
        .sidebar { width: var(--sidebar-w); min-width: var(--sidebar-w); background: var(--dark-blue); color: #fff; height: 100vh; position: sticky; top: 0; display: flex; flex-direction: column; padding: 20px; overflow-y: auto; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 1.1rem; padding-bottom: 20px; }
        .user-meta { display: flex; gap: 12px; align-items: center; margin-bottom: 20px; padding: 10px; background: rgba(255,255,255,0.05); border-radius: 8px; }
        .user-meta img { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; }
        .nav { display: flex; flex-direction: column; gap: 4px; flex-grow: 1; }
        .nav-link { display: flex; gap: 12px; align-items: center; padding: 11px 14px; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 8px; font-size: 0.95rem; }
        .nav-link:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .nav-link.active { background: linear-gradient(90deg, var(--primary-blue), var(--secondary-cyan)); color: #fff; font-weight: 600; }
        .nav-section { margin-top: 16px; color: rgba(255,255,255,0.5); font-weight: 700; font-size: 12px; padding: 8px 4px; text-transform: uppercase; }
        .nav-link.logout { background: transparent; width: 100%; border: none; font-family: inherit; cursor: pointer; }
        
        .content-wrap { flex: 1; display: flex; flex-direction: column; }
        .topbar { padding: 18px 28px; background: #fff; border-bottom: 1px solid #eef2f7; display: flex; justify-content: space-between; align-items: center; }
        .main { padding: var(--gap); }
        .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--gap); margin-bottom: var(--gap); }
        .kpi-card { background: #fff; border-radius: var(--panel-radius); padding: 20px; display: flex; align-items: center; gap: 16px; border: 1px solid #e2e8f0; }
        .kpi-icon { font-size: 1.5rem; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .kpi-card:nth-child(1) .kpi-icon { background: #eff6ff; color: #2563eb; }
        .kpi-card:nth-child(2) .kpi-icon { background: #f0fdf4; color: #16a34a; }
        .kpi-card:nth-child(3) .kpi-icon { background: #fefce8; color: #ca8a04; }
        .kpi-card p { font-size: 1.5rem; font-weight: 700; margin: 0; line-height: 1; color: var(--dark-blue); }
        .kpi-card h3 { font-size: 0.9rem; font-weight: 600; margin: 0; color: var(--muted-text); }
        
        .grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: var(--gap); }
        @media (max-width: 1200px) { .grid { grid-template-columns: 1fr; } }
        
        .panel { background: #fff; border-radius: var(--panel-radius); border: 1px solid #e2e8f0; }
        .panel-head { padding: 16px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .panel-head h2 { margin: 0; font-size: 1.2rem; }
        .panel-body { padding: 0; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { font-size: 0.8rem; text-transform: uppercase; color: var(--muted-text); }
        td { color: var(--text-color); font-size: 0.9rem; }
        tr:last-child td { border-bottom: none; }
        .action-tag { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 50px; font-weight: 600; font-size: 0.8rem; }
        .action-tag.add { background-color: #f0fdf4; color: #166534; }
        .action-tag.remove { background-color: #fef2f2; color: #b91c1c; }
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand"><i class="fas fa-layer-group"></i><span>CMS Panel</span></div>
        <div class="user-meta">
            <img src="../images/profile1.png" alt="Profile">
            <div>
                <strong><?php echo $adminName; ?></strong>
                <div style="font-size:13px; color:rgba(255,255,255,0.7);">Admin</div>
            </div>
        </div>
        <nav class="nav">
            <a class="nav-link active" href="Admin_Dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <div class="nav-section">Management</div>
            <a class="nav-link" href="Manage_Users.php"><i class="fas fa-users-cog"></i><span>Manage Users</span></a>
            <a class="nav-link" href="AddMember.php"><i class="fas fa-user-plus"></i><span>Add Member Forum</span></a>
            <a class="nav-link" href="AddEvent.php"><i class="fas fa-calendar-plus"></i><span>Add Event Forum</span></a>
            <a class="nav-link" href="AddMemberComputerClub.php"><i class="fas fa-user-plus"></i><span>Add Member Computer</span></a>
            <a class="nav-link" href="AddEventComputerClub.php"><i class="fas fa-calendar-plus"></i><span>Add Event Computer</span></a>
            <a class="nav-link" href="AddMemberSocialService.php"><i class="fas fa-user-plus"></i><span>Add Member Social Club</span></a>
            <a class="nav-link" href="AddEventSocialService.php"><i class="fas fa-calendar-plus"></i><span>Add Event Social Club</span></a>
            <a class="nav-link" href="AddMemberRoboticClub.php"><i class="fas fa-user-plus"></i><span>Add Member Robotic</span></a>
            <a class="nav-link" href="AddEventRoboticClub.php"><i class="fas fa-calendar-plus"></i><span>Add Event Robotic</span></a>
            <a class="nav-link" href="DonorEntry.php"><i class="fas fa-user-plus"></i><span>Add Donor Entry</span></a>
            <div class="nav-section">Clubs</div> 
            <a class="nav-link" href="UIUAppForum.php"><i class="fas fa-laptop-code"></i><span>App Forum</span></a>
            <a class="nav-link" href="UIUComputerClub.php"><i class="fas fa-laptop-code"></i><span>Computer Club</span></a>
            <a class="nav-link" href="UIURoboticClub.php"><i class="fas fa-robot"></i><span>Robotics Club</span></a>
            <a class="nav-link" href="SocialServiceClub.php"><i class="fas fa-hands-helping"></i><span>Social Service</span></a>
            <div class="nav-section">System</div>
            <a class="nav-link" href="Activity_Log.php"><i class="fas fa-history"></i><span>Activity Log</span></a>
            <a class="nav-link" href="Admin_Settings.php"><i class="fas fa-cogs"></i><span>Settings</span></a>
            <form method="POST" action="<?php echo htmlspecialchars($logoutUrl); ?>" style="margin-top:auto;">
                <button class="nav-link logout" type="submit" name="logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button>
            </form>
        </nav>
    </aside>

    <div class="content-wrap">
        <main class="main" role="main">
            <div class="topbar">
                <h1 style="margin:0;">Dashboard</h1>
                
            </div>

            <section class="kpis" aria-label="Key metrics">
                <div class="kpi-card">
                    <div class="kpi-icon"><i class="fas fa-users"></i></div>
                    <div><h3>Total Users</h3><p><?php echo $totalUsers; ?></p></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon"><i class="fas fa-calendar-check"></i></div>
                    <div><h3>Upcoming Events</h3><p><?php echo $upcomingEvents; ?></p></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon"><i class="fas fa-user-shield"></i></div>
                    <div><h3>Admins</h3><p><?php echo $totalAdmins; ?></p></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
                    <div><h3>Engagement</h3><p>—</p></div>
                </div>
            </section>

            <section class="grid" aria-label="Club metrics and recent activity">
                <div class="panel">
                    <div class="panel-head"><h2>Club Metrics at a Glance</h2></div>
                    <div class="panel-body">
                        <table>
                            <thead>
                                <tr><th>Club Name</th><th>Members</th><th>Upcoming Events</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($club_metrics as $club): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo $club['url']; ?>" style="text-decoration: none; color: inherit; font-weight: 600; display:flex; align-items:center; gap: 8px;">
                                                <i class="fas <?php echo $club['icon']; ?>" style="color: var(--muted-text);"></i>
                                                <?php echo $club['name']; ?>
                                            </a>
                                        </td>
                                        <td><?php echo $club['members']; ?></td>
                                        <td><?php echo $club['events']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-head">
                        <h2>Recent Activity</h2>
                        <a href="Activity_Log.php" style="text-decoration:none; color: var(--primary-blue); font-weight: 600; font-size: 0.9rem;">View All</a>
                    </div>
                    <div class="panel-body">
                        <table>
                            <thead>
                                <tr><th>Action</th><th>Performed By</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($activities)): foreach ($activities as $row): 
                                    $action_verb = explode('_', $row['action'])[0] ?? '';
                                    $tag_class = strtolower($action_verb) === 'add' || strtolower($action_verb) === 'create' ? 'add' : 'remove';
                                    $icon = $tag_class === 'add' ? 'fa-plus' : 'fa-trash-alt';
                                ?>
                                <tr>
                                    <td>
                                        <span class="action-tag <?php echo $tag_class; ?>">
                                            <i class="fas <?php echo $icon; ?>"></i>
                                            <span><?php echo htmlspecialchars(str_replace('_', ' ', $row['action'])); ?></span>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['by']); ?></td>
                                    <td><?php echo date("M d, Y", strtotime($row['date'])); ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="3" style="text-align:center; padding: 20px;">No recent activity logged.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
        <footer style="padding:18px;text-align:center;color:#6b7280;background:transparent">&copy; <?php echo date('Y'); ?> Club Management System</footer>
    </div>
</div>
</body>
</html>