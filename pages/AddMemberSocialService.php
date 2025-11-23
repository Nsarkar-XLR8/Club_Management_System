<?php
// AddMemberSocialService.php — CORRECTED
include('../db.php');
if (session_status() === PHP_SESSION_NONE) session_start();

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

$clubId = 3; // Social Service Club
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. CSRF Token Validation for all POST requests
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "❌ Invalid security token. Please try again.";
    } else {
        // --- HANDLE MEMBER REMOVAL ---
        if (isset($_POST['action']) && $_POST['action'] === 'remove') {
            $user_id_to_remove = filter_input(INPUT_POST, 'user_id_to_remove', FILTER_VALIDATE_INT);
            if ($user_id_to_remove) {
                $stmt = $conn->prepare("DELETE FROM social_service_members WHERE user_id = ? AND club_id = ?");
                $stmt->bind_param("ii", $user_id_to_remove, $clubId);
                if ($stmt->execute()) {
                    $_SESSION['flash_message'] = "✅ Member removed from Social Service Club successfully.";
                } else {
                    $_SESSION['flash_message'] = "❌ Error removing member.";
                }
                $stmt->close();
                // CORRECTED: Redirect now points to the correct filename
                header("Location: AddMemberSocialService.php");
                exit;
            }
        }
        
        // --- HANDLE MEMBER ADDITION ---
        elseif (isset($_POST['action']) && $_POST['action'] === 'add') {
            $name     = trim($_POST['name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $position = trim($_POST['position'] ?? 'Member');
            if (empty($position)) $position = 'Member';

            if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "❌ Please provide a valid name and email address.";
            } else {
                $conn->begin_transaction();
                try {
                    $user_id = null;
                    $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
                    $stmt_check->bind_param("s", $email);
                    $stmt_check->execute();
                    $result = $stmt_check->get_result();

                    if ($result->num_rows > 0) {
                        $user_id = $result->fetch_assoc()['user_id'];
                    } else {
                        $defaultPassword = password_hash("default123", PASSWORD_BCRYPT);
                        $stmt_insert = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'member', NOW())");
                        $stmt_insert->bind_param("sss", $name, $email, $defaultPassword);
                        $stmt_insert->execute();
                        $user_id = $conn->insert_id;
                        $stmt_insert->close();
                    }
                    $stmt_check->close();
                    
                    $stmt_check_member = $conn->prepare("SELECT user_id FROM social_service_members WHERE user_id = ? AND club_id = ?");
                    $stmt_check_member->bind_param("ii", $user_id, $clubId);
                    $stmt_check_member->execute();
                    
                    if ($stmt_check_member->get_result()->num_rows > 0) {
                        $message = "⚠️ This user is already a member of the Social Service Club.";
                    } else {
                        $joined_at = date("Y-m-d H:i:s");
                        $stmt_add_member = $conn->prepare("INSERT INTO social_service_members (user_id, club_id, position, joined_at) VALUES (?, ?, ?, ?)");
                        $stmt_add_member->bind_param("iiss", $user_id, $clubId, $position, $joined_at);
                        $stmt_add_member->execute();
                        $stmt_add_member->close();
                        $message = "✅ Member added successfully!";
                    }
                    $stmt_check_member->close();
                    $conn->commit();
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = "❌ An unexpected error occurred.";
                }
            }
        }
        // Regenerate CSRF token after any successful POST action
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $CSRF = $_SESSION['csrf_token'];
    }
}

// Check for flash messages (from redirects)
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Fetch recent members for this club
$recentMembers = [];
$sql_recent = "SELECT u.user_id, u.name, ssm.position 
               FROM social_service_members ssm
               JOIN users u ON ssm.user_id = u.user_id
               WHERE ssm.club_id = ?
               ORDER BY ssm.joined_at DESC LIMIT 5";
if ($stmt_recent = $conn->prepare($sql_recent)) {
    $stmt_recent->bind_param("i", $clubId);
    $stmt_recent->execute();
    $result_recent = $stmt_recent->get_result();
    while ($member = $result_recent->fetch_assoc()) {
        $recentMembers[] = $member;
    }
    $stmt_recent->close();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Add Member — Social Service Club</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root { 
        --sidebar-w: 260px; --gap: 24px; --panel-radius: 12px; --muted-text: #6b7280; 
        --primary-blue: #2563eb; --secondary-cyan: #06b6d4; --danger-red: #dc2626;
    }
    *, *::before, *::after { box-sizing: border-box; }
    html, body { height: 100%; margin: 0; font-family: 'Poppins', sans-serif; background: #f8fafc; color: #0f172a; }
    .app { display: flex; min-height: 100vh; }
    .sidebar { width: var(--sidebar-w); min-width: var(--sidebar-w); background: #0f172a; color: #fff; height: 100vh; position: sticky; top: 0; display: flex; flex-direction: column; gap: 12px; padding: 20px; overflow-y: auto; }
    .sidebar .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 1.1rem; }
    .sidebar .brand i { font-size: 22px; color: var(--primary-blue); }
    .user-meta { display: flex; gap: 12px; align-items: center; margin-top: 10px; padding: 10px; background: rgba(255,255,255,0.05); border-radius: 8px; }
    .user-meta img { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; }
    .nav { margin-top: 18px; display: flex; flex-direction: column; gap: 8px; flex-grow: 1; }
    .nav .nav-link { display: flex; gap: 12px; align-items: center; padding: 11px 14px; color: rgba(255,255,255,0.8); text-decoration: none; border-radius: 8px; transition: all .2s ease; }
    .nav .nav-link:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .nav .nav-link.active { background: linear-gradient(90deg, var(--primary-blue), var(--secondary-cyan)); color: #fff; box-shadow: 0 4px 12px rgba(37,99,235,0.2); font-weight: 600; }
    .nav .nav-section { margin-top: 12px; color: rgba(255,255,255,0.5); font-weight: 700; font-size: 12px; padding: 8px 4px; text-transform: uppercase; letter-spacing: .5px; }
    .nav-link.logout { background: transparent; width: 100%; border: none; font-family: inherit; font-size: inherit; text-align: left; cursor: pointer; }
    .content-wrap { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    .topbar { display: flex; align-items: center; justify-content: space-between; padding: 18px 28px; border-bottom: 1px solid #eef2f7; background: #fff; }
    .topbar h1 { margin: 0; font-size: 18px; font-weight: 700; }
    .panel { flex: 1; padding: var(--gap); }
    .panel-body { display: grid; grid-template-columns: 0.4fr 0.6fr; gap: var(--gap); align-items: start; }
    @media (max-width: 1100px) { .panel-body { grid-template-columns: 1fr; } }
    .card { background: #fff; border-radius: var(--panel-radius); border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; }
    .card-head { padding: 14px 18px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; font-weight: 700; }
    .card-head i { margin-right: 8px; color: var(--primary-blue); }
    .card-body { padding: 18px; }
    .member-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 16px; }
    .member-item { display: flex; gap: 12px; align-items: center; }
    .member-icon { width: 40px; height: 40px; border-radius: 50%; background: #eef2ff; color: var(--primary-blue); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; text-transform: uppercase; }
    .member-details { flex-grow: 1; }
    .member-name { font-weight: 600; color: #0f172a; }
    .member-info { font-size: 13px; color: #64748b; }
    .btn-remove { background: none; border: none; color: var(--muted-text); cursor: pointer; padding: 4px; border-radius: 4px; transition: all .2s ease; }
    .btn-remove:hover { color: var(--danger-red); background-color: #fee2e2; }
    .form-grid { display: grid; grid-template-columns: 1fr; gap: 18px; }
    label { display: block; margin-bottom: 6px; color: #374151; font-weight: 600; font-size: 0.9rem; }
    input[type="text"], input[type="email"] { width: 100%; padding: 12px 14px; border-radius: 8px; border: 1px solid #d1d5db; font-size: 15px; outline: none; transition: box-shadow .2s, border-color .2s; font-family: 'Poppins', sans-serif; }
    input:focus { box-shadow: 0 0 0 3px rgba(37,99,235,0.15); border-color: var(--primary-blue); }
    .btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 24px; border-radius: 8px; border: none; color: #fff; background: linear-gradient(135deg, var(--primary-blue), var(--secondary-cyan)); font-weight: 700; cursor: pointer; box-shadow: 0 6px 20px rgba(37,99,235,0.2); transition: all .2s ease; font-size: 1rem; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(37,99,235,0.3); }
    .muted { color: var(--muted-text); font-size: 13px; font-weight: 400; }
    .message { padding: 12px 16px; border-radius: 8px; font-weight: 600; margin-bottom: 18px; border: 1px solid; }
    .message.success { background: #f0fdf4; color: #166534; border-color: #a7f3d0; }
    .message.error { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
    .message.warning { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .site-footer { text-align: center; padding: 18px 24px; background: #fff; border-top: 1px solid #eef2f7; color: var(--muted-text); font-size: 0.9rem; }
  </style>
</head>
<body>
<div class="app">
  <aside class="sidebar" aria-label="Admin navigation">
    <div class="brand"><i class="fas fa-layer-group"></i><span>CMS Panel</span></div>
    <div class="user-meta">
        <img src="../images/profile1.png" alt="Profile">
        <div>
            <div style="font-weight:600;"><?php echo $adminName; ?></div>
            <div style="font-size:13px; color:rgba(255,255,255,0.7);">Administrator</div>
        </div>
    </div>
   
    <nav class="nav">
  <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='Admin_Dashboard.php'?'active':''; ?>" href="Admin_Dashboard.php">
    <i class="fas fa-home"></i><span>Dashboard</span>
  </a>

  <div class="nav-section">Management</div>
  <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='Manage_Users.php'?'active':''; ?>" href="Manage_Users.php">
    <i class="fas fa-users-cog"></i><span>Manage Users</span>
  </a>
    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddMember.php'?'active':''; ?>" href="AddMember.php">
    <i class="fas fa-user-plus"></i><span>Add Member Forum</span>
  </a>

    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddEvent.php'?'active':''; ?>" href="AddEvent.php">
    <i class="fas fa-calendar-plus"></i><span>Add Event Forum</span>
  </a>
  <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddMemberComputerClub.php'?'active':''; ?>" href="AddMemberComputerClub.php">
    <i class="fas fa-user-plus"></i><span>Add Member Computer</span>
  </a>
  <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddEventComputerClub.php'?'active':''; ?>" href="AddEventComputerClub.php">
    <i class="fas fa-calendar-plus"></i><span>Add Event Computer</span>
  </a>
  <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddMemberSocialService.php'?'active':''; ?>" href="AddMemberSocialService.php">
  <i class="fas fa-user-plus"></i><span>Add Member Social Club</span>
</a>

<a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddMemberRoboticClub.php'?'active':''; ?>" href="AddMemberRoboticClub.php">
        <i class="fas fa-user-plus"></i><span>Add Member Robotic</span>
    </a>
    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddEventRoboticClub.php'?'active':''; ?>" href="AddEventRoboticClub.php">
        <i class="fas fa-calendar-plus"></i><span>Add Event Robotic</span>
    </a>
   
  <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='AddEventSocialService.php'?'active':''; ?>" href="AddEventSocialService.php">
    <i class="fas fa-calendar-plus"></i><span>Add Event Social Club</span>
  </a>
   <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='DonorEntry.php'?'active':''; ?>" href="DonorEntry.php">
  <i class="fas fa-user-plus"></i><span>Add Donor Entry</span>
</a>

  <div class="nav-section">Clubs</div>
  <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='UIUAppForum.php'?'active':''; ?>" href="UIUAppForum.php">
    <i class="fas fa-laptop-code"></i><span>App Forum</span>
  </a>
  <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='UIUComputerClub.php'?'active':''; ?>" href="UIUComputerClub.php">
    <i class="fas fa-laptop-code"></i><span>Computer Club</span>
  </a>
  <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='UIURoboticClub.php'?'active':''; ?>" href="UIURoboticClub.php">
    <i class="fas fa-laptop-code"></i><span>Robotics Club</span>
  </a>
  <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='SocialServiceClub.php'?'active':''; ?>" href="SocialServiceClub.php">
    <i class="fas fa-hands-helping"></i><span>Social Service</span>
  </a>

  <div class="nav-section">System</div>
  <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='activity_log.php'?'active':''; ?>" href="activity_log.php">
    <i class="fas fa-history"></i><span>Activity Log</span>
  </a>
  <a class="nav-link <?php echo basename($_SERVER['PHP_SELF'])==='Admin_Settings.php'?'active':''; ?>" href="Admin_Settings.php">
    <i class="fas fa-cogs"></i><span>Settings</span>
  </a>

  <form method="POST" action="<?php echo htmlspecialchars($logoutUrl); ?>" style="margin-top:12px;">
    <button class="nav-link logout" type="submit" name="logout">
      <i class="fas fa-sign-out-alt"></i><span>Logout</span>
    </button>
  </form>
</nav>

  </aside>

  <div class="content-wrap">
    <header class="topbar">
        <h1>Add New Social Service Club Member</h1>
        <a href="SocialServiceClub.php" class="muted" style="text-decoration:none; display:inline-flex; align-items:center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Club Page
        </a>
    </header>

    <main class="panel" role="main">
      <div class="panel-body">
        <aside>
          <div class="card">
            <div class="card-head"><i class="fas fa-users"></i> Recently Added Members</div>
            <div class="card-body">
              <ul class="member-list">
                <?php if (!empty($recentMembers)): foreach ($recentMembers as $member): ?>
                  <li class="member-item">
                    <div class="member-icon"><?php echo htmlspecialchars(strtoupper(substr($member['name'], 0, 1))); ?></div>
                    <div class="member-details">
                      <div class="member-name"><?php echo htmlspecialchars($member['name']); ?></div>
                      <div class="member-info"><?php echo htmlspecialchars($member['position'] ?: 'Member'); ?></div>
                    </div>
                    <form method="POST" action="AddMemberSocialService.php" onsubmit="return confirm('Are you sure you want to remove this member from the club?');">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="user_id_to_remove" value="<?php echo $member['user_id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($CSRF); ?>">
                        <button type="submit" class="btn-remove" title="Remove Member">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                  </li>
                <?php endforeach; else: ?>
                  <li class="member-item"><div class="member-info">No recent members to show.</div></li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </aside>

        <section>
            <div class="card">
                <div class="card-head"><i class="fas fa-edit"></i> Member Details</div>
                <div class="card-body">
                  <?php if (!empty($message)): 
                    $msgClass = 'success';
                    if (str_starts_with($message, '❌')) $msgClass = 'error';
                    if (str_starts_with($message, '⚠️')) $msgClass = 'warning';
                  ?>
                    <div class="message <?php echo $msgClass; ?>"><?php echo htmlspecialchars($message); ?></div>
                  <?php endif; ?>

                  <form method="POST" action="AddMemberSocialService.php" class="form-grid" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($CSRF); ?>">
                    <div>
                      <label for="nameInput">Full Name</label>
                      <input type="text" name="name" id="nameInput" required placeholder="e.g., Jane Smith">
                    </div>
                    <div>
                      <label for="emailInput">Email Address</label>
                      <input type="email" name="email" id="emailInput" required placeholder="e.g., jane.smith@example.com">
                    </div>
                    <div>
                      <label for="positionInput">Position in Club</label>
                      <input type="text" name="position" id="positionInput" required placeholder="e.g., Volunteer, Coordinator">
                    </div>
                    <div style="margin-top: 12px; text-align: right;">
                      <button type="submit" name="action" value="add" class="btn-primary"><i class="fas fa-user-plus"></i> Add Member</button>
                    </div>
                  </form>
                </div>
            </div>
        </section>
      </div>
    </main>
    <footer class="site-footer">&copy; <?php echo date('Y'); ?> Club Management System</footer>
  </div>
</div>
</body>
</html>