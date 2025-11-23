<?php
// pages/AddMemberRoboticClub.php — With modern admin design, full nav, and remove member functionality
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

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$CSRF = $_SESSION['csrf_token'];

$clubId = 4; // UIU Robotic Club
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "❌ Invalid security token.";
    } else {
        // --- HANDLE MEMBER REMOVAL ---
        if (isset($_POST['action']) && $_POST['action'] === 'remove') {
            $user_id_to_remove = filter_input(INPUT_POST, 'user_id_to_remove', FILTER_VALIDATE_INT);
            if ($user_id_to_remove) {
                $stmt = $conn->prepare("DELETE FROM robotic_club_members WHERE user_id = ? AND club_id = ?");
                $stmt->bind_param("ii", $user_id_to_remove, $clubId);
                $_SESSION['flash_message'] = $stmt->execute() ? "✅ Member removed successfully." : "❌ Error removing member.";
                $stmt->close();
                header("Location: AddMemberRoboticClub.php");
                exit;
            }
        }
        
        // --- HANDLE MEMBER ADDITION (COMPLETE LOGIC) ---
        elseif (isset($_POST['action']) && $_POST['action'] === 'add') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $position = trim($_POST['position'] ?? 'Member');
            if(empty($position)) $position = 'Member';
            
            if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "❌ Please provide a valid name and email address.";
            } else {
                $conn->begin_transaction();
                try {
                    $user_id = null;
                    // Find or create user
                    $stmt_user = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
                    $stmt_user->bind_param("s", $email);
                    $stmt_user->execute();
                    $result_user = $stmt_user->get_result();
                    if ($row_user = $result_user->fetch_assoc()) {
                        $user_id = $row_user['user_id'];
                    } else {
                        $stmt_insert_user = $conn->prepare("INSERT INTO users (name, email, role) VALUES (?, ?, 'member')");
                        $stmt_insert_user->bind_param("ss", $name, $email);
                        $stmt_insert_user->execute();
                        $user_id = $conn->insert_id;
                        $stmt_insert_user->close();
                    }
                    $stmt_user->close();

                    // Check if already in club
                    $stmt_check = $conn->prepare("SELECT member_id FROM robotic_club_members WHERE user_id = ? AND club_id = ?");
                    $stmt_check->bind_param("ii", $user_id, $clubId);
                    $stmt_check->execute();
                    if ($stmt_check->get_result()->num_rows > 0) {
                        $message = "⚠️ This user is already a member of the Robotic Club.";
                    } else {
                        // Add to club
                        $stmt_add = $conn->prepare("INSERT INTO robotic_club_members (user_id, club_id, position) VALUES (?, ?, ?)");
                        $stmt_add->bind_param("iis", $user_id, $clubId, $position);
                        $stmt_add->execute();
                        $message = "✅ Member added successfully!";
                        $stmt_add->close();
                    }
                    $stmt_check->close();
                    $conn->commit();
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = "❌ Database transaction failed: " . $e->getMessage();
                }
            }
        }
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $CSRF = $_SESSION['csrf_token'];
    }
}

if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

$recentMembers = [];
$sql_recent = "SELECT u.user_id, u.name, rcm.position FROM robotic_club_members rcm JOIN users u ON rcm.user_id = u.user_id WHERE rcm.club_id = ? ORDER BY rcm.joined_at DESC LIMIT 5";
if ($stmt_recent = $conn->prepare($sql_recent)) {
    $stmt_recent->bind_param("i", $clubId);
    $stmt_recent->execute();
    $result = $stmt_recent->get_result();
    while ($member = $result->fetch_assoc()) {
        $recentMembers[] = $member;
    }
    $stmt_recent->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Member - Robotic Club</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --sidebar-w: 260px; --gap: 24px; --panel-radius: 12px; --muted-text: #6b7280; 
            --primary-color: #ca8a04; --dark-blue: #0f172a; --danger-red: #dc2626;
            --primary-blue-gradient: #2563eb; --secondary-cyan-gradient: #06b6d4;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: #f8fafc; color: #0f172a; }
        .app { display: flex; min-height: 100vh; }
        .sidebar { width: var(--sidebar-w); min-width: var(--sidebar-w); background: var(--dark-blue); color: #fff; height: 100vh; position: sticky; top: 0; display: flex; flex-direction: column; padding: 20px; overflow-y: auto; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 1.1rem; padding-bottom: 20px; }
        .user-meta { display: flex; gap: 12px; align-items: center; margin-bottom: 20px; padding: 10px; background: rgba(255,255,255,0.05); border-radius: 8px; }
        .user-meta img { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; }
        .nav { display: flex; flex-direction: column; gap: 4px; flex-grow: 1; }
        .nav-link { display: flex; gap: 12px; align-items: center; padding: 11px 14px; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 8px; font-size: 0.95rem; }
        .nav-link:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .nav-link.active { background: linear-gradient(90deg, var(--primary-blue-gradient), var(--secondary-cyan-gradient)); color: #fff; font-weight: 600; }
        .nav-section { margin-top: 16px; color: rgba(255,255,255,0.5); font-weight: 700; font-size: 12px; padding: 8px 4px; text-transform: uppercase; }
        .nav-link.logout { background: transparent; width: 100%; border: none; font-family: inherit; cursor: pointer; }
        .content-wrap { flex: 1; display: flex; flex-direction: column; }
        .topbar { padding: 18px 28px; background: #fff; border-bottom: 1px solid #eef2f7; display: flex; justify-content: space-between; align-items: center; }
        .panel-body { display: grid; grid-template-columns: 0.4fr 0.6fr; gap: var(--gap); padding: var(--gap); align-items: start; }
        @media (max-width: 1100px) { .panel-body { grid-template-columns: 1fr; } }
        .card { background: #fff; border-radius: var(--panel-radius); border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .card-head { padding: 14px 18px; border-bottom: 1px solid #f1f5f9; font-weight: 700; }
        .card-body { padding: 18px; }
        .form-grid { display: grid; gap: 18px; }
        label { font-weight: 600; margin-bottom: 6px; font-size: 0.9rem; display: block; }
        input { width: 100%; padding: 12px 14px; border-radius: 8px; border: 1px solid #d1d5db; font-size: 1rem; }
        .btn-primary { padding: 12px 24px; border-radius: 8px; border: none; background: var(--primary-color); color: #fff; font-weight: 700; cursor: pointer; }
        .member-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 16px; }
        .member-item { display: flex; justify-content: space-between; align-items: center; }
        .btn-remove { background: none; border: none; color: var(--muted-text); cursor: pointer; }
        .btn-remove:hover { color: var(--danger-red); }
        .message { padding: 12px; border-radius: 8px; margin-bottom: 16px; font-weight: 600; }
        .message.success { background: #f0fdf4; color: #166534; }
        .message.error, .message.warning { background: #fef2f2; color: #b91c1c; }
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
            <a class="nav-link active" href="AddMemberRoboticClub.php"><i class="fas fa-user-plus"></i><span>Add Member Robotic</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddEventRoboticClub.php'?'active':''; ?>" href="AddEventRoboticClub.php"><i class="fas fa-calendar-plus"></i><span>Add Event Robotic</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='DonorEntry.php'?'active':''; ?>" href="DonorEntry.php"><i class="fas fa-user-plus"></i><span>Add Donor Entry</span></a>
            <div class="nav-section">Clubs</div>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='UIUAppForum.php'?'active':''; ?>" href="UIUAppForum.php"><i class="fas fa-laptop-code"></i><span>App Forum</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='UIUComputerClub.php'?'active':''; ?>" href="UIUComputerClub.php"><i class="fas fa-laptop-code"></i><span>Computer Club</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='UIURoboticClub.php'?'active':''; ?>" href="UIURoboticClub.php"><i class="fas fa-robot"></i><span>Robotics Club</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='SocialServiceClub.php'?'active':''; ?>" href="SocialServiceClub.php"><i class="fas fa-hands-helping"></i><span>Social Service</span></a>
            <div class="nav-section">System</div>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='activity_log.php'?'active':''; ?>" href="activity_log.php"><i class="fas fa-history"></i><span>Activity Log</span></a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='Admin_Settings.php'?'active':''; ?>" href="Admin_Settings.php"><i class="fas fa-cogs"></i><span>Settings</span></a>
            <form method="POST" action="<?php echo htmlspecialchars($logoutUrl); ?>" style="margin-top:auto;">
                <button class="nav-link logout" type="submit" name="logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button>
            </form>
        </nav>
    </aside>
    <div class="content-wrap">
        <header class="topbar">
            <h1 style="margin:0;">Add Robotic Club Member</h1>
            <a href="UIURoboticClub.php" style="color: var(--muted-text); text-decoration: none;">Back to Club Page</a>
        </header>
        <main class="panel-body">
            <aside>
                <div class="card">
                    <div class="card-head">Recently Added Members</div>
                    <div class="card-body">
                        <ul class="member-list">
                             <?php if (!empty($recentMembers)): foreach ($recentMembers as $member): ?>
                                <li class="member-item">
                                    <div style="display:flex; gap:12px; align-items:center;">
                                        <div style="width:40px; height:40px; border-radius:50%; background:#fefce8; color: var(--primary-color); display:inline-flex; align-items:center; justify-content:center; font-weight:700;"><?php echo strtoupper(substr($member['name'], 0, 1)); ?></div>
                                        <div>
                                            <div style="font-weight:600;"><?php echo htmlspecialchars($member['name']); ?></div>
                                            <div style="font-size:0.8rem; color:var(--muted-text);"><?php echo htmlspecialchars($member['position'] ?: 'Member'); ?></div>
                                        </div>
                                    </div>
                                    <form method="POST" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="user_id_to_remove" value="<?php echo $member['user_id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($CSRF); ?>">
                                        <button type="submit" class="btn-remove" title="Remove"><i class="fas fa-trash"></i></button>
                                    </form>
                                </li>
                            <?php endforeach; else: ?>
                                <li>No recent members.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </aside>
            <section>
                <div class="card">
                    <div class="card-head">New Member Details</div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="message <?php echo str_starts_with($message, '✅') ? 'success' : (str_starts_with($message, '⚠️') ? 'warning' : 'error'); ?>"><?php echo htmlspecialchars($message); ?></div>
                        <?php endif; ?>
                        <form method="POST" class="form-grid">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($CSRF); ?>">
                            <div>
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" required placeholder="e.g., Ada Lovelace">
                            </div>
                            <div>
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required placeholder="ada.l@example.com">
                            </div>
                            <div>
                                <label for="position">Position in Club</label>
                                <input type="text" id="position" name="position" placeholder="e.g., Lead Engineer, Member">
                            </div>
                            <div style="text-align:right;">
                                <button type="submit" name="action" value="add" class="btn-primary">Add Member</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>
</body>
</html>