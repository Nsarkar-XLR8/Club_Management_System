<?php
// pages/Activity_Log.php — Final, Perfected Version
if (session_status() === PHP_SESSION_NONE) session_start();
include realpath(__DIR__ . '/../db.php');

// Admin guard
if (!isset($_SESSION['role']) || strtolower((string)$_SESSION['role']) !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

$adminName = htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? 'Admin User', ENT_QUOTES, 'UTF-8');
$siteBase = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if (basename($siteBase) === 'pages') $siteBase = dirname($siteBase);
$logoutUrl = $siteBase . '/pages/logout.php';

// --- PAGINATION & FILTERING LOGIC ---
$limit = 15; // Number of records per page
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
$offset = ($page - 1) * $limit;

$filter_action = trim($_GET['filter_action'] ?? '');

// Build the query
$where_clauses = [];
$params = [];
$types = "";

if (!empty($filter_action)) {
    $where_clauses[] = "action = ?";
    $params[] = $filter_action;
    $types .= "s";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : "";

// Get total records for pagination
$count_sql = "SELECT COUNT(*) FROM activity_log " . $where_sql;
$stmt_count = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_records = $stmt_count->get_result()->fetch_row()[0];
$total_pages = ceil($total_records / $limit);
$stmt_count->close();

// Get the records for the current page
$log_sql = "SELECT log_id, actor_name, action, description, timestamp FROM activity_log " . $where_sql . " ORDER BY timestamp DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt_log = $conn->prepare($log_sql);
$stmt_log->bind_param($types, ...$params);
$stmt_log->execute();
$activities = $stmt_log->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_log->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --sidebar-w: 260px; --gap: 24px; --panel-radius: 12px; --muted-text: #6b7280; 
            --primary-blue: #2563eb; --secondary-cyan: #06b6d4; --danger-red: #dc2626;
            --dark-blue: #0f172a;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: #f8fafc; color: var(--text-color); }
        .app { display: flex; min-height: 100vh; }
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
        .panel { padding: var(--gap); }
        .card { background: #fff; border-radius: var(--panel-radius); border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .card-head { padding: 14px 18px; border-bottom: 1px solid #f1f5f9; font-weight: 700; display: flex; justify-content: space-between; align-items: center; }
        .card-body { padding: 0; } /* Remove padding for table */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 18px; text-align: left; border-bottom: 1px solid #eef2f7; }
        th { font-size: 0.8rem; text-transform: uppercase; color: var(--muted-text); }
        td { color: var(--text-color); }
        .action-tag { display: inline-flex; align-items: center; gap: 8px; padding: 4px 10px; border-radius: 50px; font-weight: 600; font-size: 0.8rem; }
        .action-tag.add { background-color: #f0fdf4; color: #166534; }
        .action-tag.remove { background-color: #fef2f2; color: #b91c1c; }
        .action-tag.update { background-color: #eff6ff; color: #2563eb; }
        .pagination { display: flex; justify-content: space-between; align-items: center; padding: 18px; }
        .pagination a { text-decoration: none; background-color: #fff; border: 1px solid var(--border-color); color: var(--text-color); padding: 8px 16px; border-radius: 8px; font-weight: 600; }
        .pagination a:hover { background-color: #f8fafc; }
        .pagination a.disabled { opacity: 0.5; cursor: not-allowed; }
        .filter-form { display: flex; gap: 16px; align-items: center; }
        .filter-form select, .filter-form button { padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand"><i class="fas fa-layer-group"></i><span>CMS Panel</span></div>
        <div class="user-meta">
            <img src="../images/profile1.png" alt="Profile" style="width: 44px; height: 44px; border-radius: 8px;">
            <div>
                <strong style="font-weight:600;"><?php echo $adminName; ?></strong>
                <div style="font-size:13px; color:rgba(255,255,255,0.7);">Admin</div>
            </div>
        </div>
        <nav class="nav">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='Admin_Dashboard.php'?'active':''; ?>" href="Admin_Dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <div class="nav-section">Management</div>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='Manage_Users.php'?'active':''; ?>" href="Manage_Users.php"><i class="fas fa-users-cog"></i><span>Manage Users</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddMember.php'?'active':''; ?>" href="AddMember.php"><i class="fas fa-user-plus"></i><span>Add Member Forum</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddEvent.php'?'active':''; ?>" href="AddEvent.php"><i class="fas fa-calendar-plus"></i><span>Add Event Forum</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddMemberComputerClub.php'?'active':''; ?>" href="AddMemberComputerClub.php"><i class="fas fa-user-plus"></i><span>Add Member Computer</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddEventComputerClub.php'?'active':''; ?>" href="AddEventComputerClub.php"><i class="fas fa-calendar-plus"></i><span>Add Event Computer</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddMemberSocialService.php'?'active':''; ?>" href="AddMemberSocialService.php"><i class="fas fa-user-plus"></i><span>Add Member Social Club</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddEventSocialService.php'?'active':''; ?>" href="AddEventSocialService.php"><i class="fas fa-calendar-plus"></i><span>Add Event Social Club</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddMemberRoboticClub.php'?'active':''; ?>" href="AddMemberRoboticClub.php"><i class="fas fa-user-plus"></i><span>Add Member Robotic</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddEventRoboticClub.php'?'active':''; ?>" href="AddEventRoboticClub.php"><i class="fas fa-calendar-plus"></i><span>Add Event Robotic</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='DonorEntry.php'?'active':''; ?>" href="DonorEntry.php"><i class="fas fa-user-plus"></i><span>Add Donor Entry</span></a>
            <div class="nav-section">Clubs</div>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='UIUAppForum.php'?'active':''; ?>" href="UIUAppForum.php"><i class="fas fa-laptop-code"></i><span>App Forum</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='UIUComputerClub.php'?'active':''; ?>" href="UIUComputerClub.php"><i class="fas fa-laptop-code"></i><span>Computer Club</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='UIURoboticClub.php'?'active':''; ?>" href="UIURoboticClub.php"><i class="fas fa-robot"></i><span>Robotics Club</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='SocialServiceClub.php'?'active':''; ?>" href="SocialServiceClub.php"><i class="fas fa-hands-helping"></i><span>Social Service</span></a>
            <div class="nav-section">System</div>
            <a class="nav-link active" href="activity_log.php"><i class="fas fa-history"></i><span>Activity Log</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='Admin_Settings.php'?'active':''; ?>" href="Admin_Settings.php"><i class="fas fa-cogs"></i><span>Settings</span></a>
            <form method="POST" action="<?php echo htmlspecialchars($logoutUrl); ?>" style="margin-top:auto;">
                <button class="nav-link logout" type="submit" name="logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button>
            </form>
        </nav>
    </aside>
    <div class="content-wrap">
        <header class="topbar">
            <h1 style="margin:0;">System Activity Log</h1>
        </header>
        <main class="panel">
            <div class="card">
                <div class="card-head">
                    <span>All Recorded Activities</span>
                    <form method="GET" class="filter-form">
                        <select name="filter_action" onchange="this.form.submit()">
                            <option value="">All Actions</option>
                            <option value="MEMBER_ADD" <?php echo ($filter_action === 'MEMBER_ADD') ? 'selected' : ''; ?>>Member Added</option>
                            <option value="MEMBER_REMOVE" <?php echo ($filter_action === 'MEMBER_REMOVE') ? 'selected' : ''; ?>>Member Removed</option>
                            <option value="EVENT_CREATE" <?php echo ($filter_action === 'EVENT_CREATE') ? 'selected' : ''; ?>>Event Created</option>
                            <option value="EVENT_REMOVE" <?php echo ($filter_action === 'EVENT_REMOVE') ? 'selected' : ''; ?>>Event Removed</option>
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Performed By</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($activities)): foreach ($activities as $activity): 
                                $action_type = explode('_', $activity['action'])[1] ?? '';
                                $action_verb = explode('_', $activity['action'])[0] ?? '';
                                $tag_class = strtolower($action_verb) === 'add' || strtolower($action_verb) === 'create' ? 'add' : 'remove';
                                $icon = $tag_class === 'add' ? 'fa-plus' : 'fa-trash';
                            ?>
                            <tr>
                                <td>
                                    <span class="action-tag <?php echo $tag_class; ?>">
                                        <i class="fas <?php echo $icon; ?>"></i>
                                        <span><?php echo htmlspecialchars(ucfirst(strtolower($action_type))); ?></span>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($activity['description']); ?></td>
                                <td><?php echo htmlspecialchars($activity['actor_name']); ?></td>
                                <td><?php echo date("M d, Y, g:i A", strtotime($activity['timestamp'])); ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding: 40px; color: var(--muted-text);">No activities found matching your criteria.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    <div>
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&filter_action=<?php echo urlencode($filter_action); ?>">Previous</a>
                        <?php else: ?>
                            <a href="#" class="disabled">Previous</a>
                        <?php endif; ?>
                    </div>
                    <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                    <div>
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&filter_action=<?php echo urlencode($filter_action); ?>">Next</a>
                        <?php else: ?>
                            <a href="#" class="disabled">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>