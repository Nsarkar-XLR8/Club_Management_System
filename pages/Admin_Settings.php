<?php
// pages/Admin_Settings.php - FINAL PERFECTED VERSION
if (session_status() === PHP_SESSION_NONE) session_start();
include realpath(__DIR__ . '/../db.php');

// Admin guard
if (!isset($_SESSION['user_id']) || strtolower((string)$_SESSION['role']) !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$adminName = htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? 'Admin User', ENT_QUOTES, 'UTF-8');

$siteBase = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if (basename($siteBase) === 'pages') $siteBase = dirname($siteBase);
$logoutUrl = $siteBase . '/pages/logout.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$CSRF = $_SESSION['csrf_token'];

$message = '';
$message_type = ''; // 'success' or 'error'

// Function to log activities (place this in a central functions file or db.php if you prefer)
if (!function_exists('log_activity')) {
    function log_activity($conn, $actor_id, $actor_name, $action, $description) {
        $stmt = $conn->prepare("INSERT INTO activity_log (actor_id, actor_name, action, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $actor_id, $actor_name, $action, $description);
        $stmt->execute();
        $stmt->close();
    }
}


// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "Invalid security token.";
        $message_type = 'error';
    } else {
        $action = $_POST['action'] ?? '';

        // --- UPDATE PROFILE ---
        if ($action === 'update_profile') {
            $newName = trim($_POST['name'] ?? '');
            $newEmail = trim($_POST['email'] ?? '');
            if (empty($newName) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $message = "Please provide a valid name and email address.";
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
                $stmt->bind_param("ssi", $newName, $newEmail, $admin_id);
                if ($stmt->execute()) {
                    $_SESSION['name'] = $newName; // Update session immediately
                    $adminName = htmlspecialchars($newName, ENT_QUOTES, 'UTF-8');
                    $message = "Profile updated successfully.";
                    $message_type = 'success';
                    log_activity($conn, $admin_id, $adminName, 'PROFILE_UPDATE', "$adminName updated their profile.");
                } else {
                    $message = "Error updating profile.";
                    $message_type = 'error';
                }
                $stmt->close();
            }
        }

        // --- CHANGE PASSWORD ---
        elseif ($action === 'change_password') {
            $currentPass = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            if (strlen($newPass) < 8) {
                $message = "New password must be at least 8 characters long.";
                $message_type = 'error';
            } elseif ($newPass !== $confirmPass) {
                $message = "New passwords do not match.";
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $admin_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($user = $result->fetch_assoc()) {
                    if (password_verify($currentPass, $user['password'])) {
                        $newHashedPassword = password_hash($newPass, PASSWORD_BCRYPT);
                        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                        $updateStmt->bind_param("si", $newHashedPassword, $admin_id);
                        if ($updateStmt->execute()) {
                            $message = "Password changed successfully.";
                            $message_type = 'success';
                            log_activity($conn, $admin_id, $adminName, 'PASSWORD_CHANGE', "$adminName changed their password.");
                        } else {
                            $message = "Error changing password.";
                            $message_type = 'error';
                        }
                        $updateStmt->close();
                    } else {
                        $message = "Incorrect current password.";
                        $message_type = 'error';
                    }
                }
                $stmt->close();
            }
        }
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $CSRF = $_SESSION['csrf_token'];
}

// Fetch current admin data
$stmt = $conn->prepare("SELECT name, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$current_admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --sidebar-w: 260px; --gap: 24px; --panel-radius: 16px; --muted-text: #6b7280; 
            --primary-blue: #2563eb; --secondary-cyan: #06b6d4; --danger-red: #dc2626;
            --dark-blue: #0f172a; --text-color: #334155;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: #f8fafc; color: var(--text-color); }
        .app { display: flex; min-height: 100vh; }
        .sidebar { width: var(--sidebar-w); min-width: var(--sidebar-w); background: var(--dark-blue); color: #fff; height: 100vh; position: sticky; top: 0; display: flex; flex-direction: column; padding: 20px; overflow-y: auto; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 1.2rem; padding-bottom: 20px; color: #fff; }
        .user-meta { display: flex; gap: 12px; align-items: center; margin-bottom: 20px; padding: 10px; background: rgba(255,255,255,0.05); border-radius: 8px; }
        .user-meta img { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; }
        .nav { display: flex; flex-direction: column; gap: 4px; flex-grow: 1; }
        .nav-link { display: flex; gap: 12px; align-items: center; padding: 11px 14px; color: #cbd5e1; text-decoration: none; border-radius: 8px; font-size: 0.95rem; font-weight: 500; }
        .nav-link:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .nav-link.active { background: linear-gradient(90deg, var(--primary-blue), var(--secondary-cyan)); color: #fff; font-weight: 600; }
        .nav-section { margin-top: 16px; color: rgba(255,255,255,0.4); font-weight: 700; font-size: 11px; padding: 8px 4px; text-transform: uppercase; letter-spacing: 0.5px; }
        .nav-link.logout { background: transparent; width: 100%; border: none; font-family: inherit; cursor: pointer; }
        .content-wrap { flex: 1; display: flex; flex-direction: column; }
        .topbar { padding: 18px 28px; background: #fff; border-bottom: 1px solid #eef2f7; }
        .topbar h1 { margin: 0; font-size: 1.5rem; font-weight: 700; }
        .main { padding: var(--gap); max-width: 900px; margin: 0 auto; width: 100%; }
        .card { background: #fff; border-radius: var(--panel-radius); border: 1px solid #e5e7eb; margin-bottom: var(--gap); }
        .card-head { padding: 20px; border-bottom: 1px solid #eef2f7; }
        .card-head h2 { margin: 0; font-size: 1.2rem; }
        .card-body { padding: 20px; }
        .form-grid { display: grid; gap: 20px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        label { font-weight: 600; margin-bottom: 8px; font-size: 0.9rem; display: block; }
        input { width: 100%; padding: 12px 16px; border-radius: 8px; border: 1px solid #d1d5db; font-size: 1rem; }
        input:focus { outline: none; border-color: var(--primary-blue); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }
        .card-foot { padding: 16px 20px; background-color: #f8fafc; border-top: 1px solid #eef2f7; display: flex; justify-content: flex-end; align-items: center; }
        .btn { padding: 10px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; font-size: 0.9rem; }
        .btn-primary { background: var(--primary-blue); color: #fff; }
        .message { padding: 16px; border-radius: 8px; font-weight: 600; margin-bottom: var(--gap); border: 1px solid; }
        .message.success { background-color: #f0fdf4; color: #166534; border-color: #a7f3d0; }
        .message.error { background-color: #fef2f2; color: #b91c1c; border-color: #fecaca; }
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
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='Admin_Dashboard.php'?'active':''; ?>" href="Admin_Dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
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
            <a class="nav-link active" href="Admin_Settings.php"><i class="fas fa-cogs"></i><span>Settings</span></a>
            <form method="POST" action="<?php echo htmlspecialchars($logoutUrl); ?>" style="margin-top:auto;">
                <button class="nav-link logout" type="submit" name="logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button>
            </form>
        </nav>
    </aside>

    <div class="content-wrap">
        <header class="topbar">
            <h1>System Settings</h1>
        </header>
        <main class="main">
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Admin Profile Card -->
            <form method="POST" action="Admin_Settings.php">
                <input type="hidden" name="action" value="update_profile">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($CSRF); ?>">
                <div class="card">
                    <div class="card-head"><h2>Admin Profile</h2></div>
                    <div class="card-body form-grid">
                        <div class="form-row">
                            <div>
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($current_admin['name']); ?>">
                            </div>
                            <div>
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($current_admin['email']); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="card-foot">
                        <button type="submit" class="btn btn-primary">Save Profile</button>
                    </div>
                </div>
            </form>

            <!-- Change Password Card -->
            <form method="POST" action="Admin_Settings.php">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($CSRF); ?>">
                <div class="card">
                    <div class="card-head"><h2>Change Password</h2></div>
                    <div class="card-body form-grid">
                        <div>
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-row">
                            <div>
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            <div>
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    <div class="card-foot">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>
</body>
</html>