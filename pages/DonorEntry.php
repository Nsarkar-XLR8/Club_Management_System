<?php
// DonorEntry.php — With modern design and Add/Remove functionality
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

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. CSRF Token Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "❌ Invalid security token. Please try again.";
    } else {
        // --- HANDLE DONOR REMOVAL ---
        if (isset($_POST['action']) && $_POST['action'] === 'remove') {
            $donor_id_to_remove = filter_input(INPUT_POST, 'donor_id_to_remove', FILTER_VALIDATE_INT);
            if ($donor_id_to_remove) {
                
                $stmt = $conn->prepare("DELETE FROM donors WHERE donor_id = ?");
                $stmt->bind_param("i", $donor_id_to_remove);
                if ($stmt->execute()) {
                    $_SESSION['flash_message'] = "✅ Donor record removed successfully.";
                } else {
                    $_SESSION['flash_message'] = "❌ Error removing donor.";
                }
                $stmt->close();
                header("Location: DonorEntry.php");
                exit;
            }
        }
        
        // --- HANDLE DONOR ADDITION ---
        elseif (isset($_POST['action']) && $_POST['action'] === 'add') {
            // Basic validation
            if (empty($_POST['full_name']) || empty($_POST['contact_number']) || empty($_POST['blood_group'])) {
                $message = "❌ Full Name, Contact, and Blood Group are required.";
            } else {
                // (Optional) Handle file uploads for photo and documents here
                // ...

                $stmt = $conn->prepare("INSERT INTO donors (club_id, full_name, father_name, mother_name, dob, nid, email, contact_number, blood_group, permanent_address, gender, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                if ($stmt) {
                  $clubId = 3;
                    $stmt->bind_param("ssssisssss", 
                    $clubId,
                        $_POST['full_name'], 
                        $_POST['father_name'], 
                        $_POST['mother_name'], 
                        $_POST['dob'], 
                        $_POST['nid'], 
                        $_POST['email'], 
                        $_POST['contact_number'], 
                        $_POST['blood_group'], 
                        $_POST['permanent_address'], 
                        $_POST['gender']
                    );
                    if ($stmt->execute()) {
                        $message = "✅ Donor registered successfully!";
                    } else {
                        $message = "❌ Error registering donor: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
        // Regenerate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $CSRF = $_SESSION['csrf_token'];
    }
}

// Check for flash messages from redirects
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Fetch recent donors
$recentDonors = [];
$sql_recent = "SELECT donor_id, full_name, blood_group FROM donors ORDER BY created_at DESC LIMIT 5";
if ($result = $conn->query($sql_recent)) {
    while ($donor = $result->fetch_assoc()) {
        $recentDonors[] = $donor;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Add Donor — Admin</title>
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
    .nav { margin-top: 18px; display: flex; flex-direction: column; gap: 8px; flex-grow: 1; }
    .nav .nav-link { display: flex; gap: 12px; align-items: center; padding: 11px 14px; color: rgba(255,255,255,0.8); text-decoration: none; border-radius: 8px; transition: all .2s ease; }
    .nav .nav-link.active { background: linear-gradient(90deg, var(--primary-blue), var(--secondary-cyan)); color: #fff; font-weight: 600; }
    .nav-link.logout { background: transparent; width: 100%; border: none; font-family: inherit; cursor: pointer; }
    .content-wrap { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    .topbar { display: flex; align-items: center; justify-content: space-between; padding: 18px 28px; border-bottom: 1px solid #eef2f7; background: #fff; }
    .panel-body { display: grid; grid-template-columns: 0.4fr 0.6fr; gap: var(--gap); align-items: start; padding: var(--gap); }
    @media (max-width: 1100px) { .panel-body { grid-template-columns: 1fr; } }
    .card { background: #fff; border-radius: var(--panel-radius); border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .card-head { padding: 14px 18px; border-bottom: 1px solid #f1f5f9; font-weight: 700; }
    .card-body { padding: 18px; }
    .donor-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 16px; }
    .donor-item { display: flex; gap: 12px; align-items: center; }
    .donor-icon { width: 40px; height: 40px; border-radius: 50%; background: #fee2e2; color: var(--danger-red); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
    .donor-details { flex-grow: 1; }
    .donor-name { font-weight: 600; }
    .donor-info { font-size: 13px; color: #64748b; }
    .btn-remove { background: none; border: none; color: var(--muted-text); cursor: pointer; padding: 4px; border-radius: 4px; }
    .btn-remove:hover { color: var(--danger-red); background-color: #fee2e2; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .form-grid .full-width { grid-column: 1 / -1; }
    label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; }
    input, select, textarea { width: 100%; padding: 12px 14px; border-radius: 8px; border: 1px solid #d1d5db; font-size: 15px; }
    .btn-primary { padding: 12px 24px; border-radius: 8px; border: none; color: #fff; background: linear-gradient(135deg, var(--primary-blue), var(--secondary-cyan)); font-weight: 700; cursor: pointer; }
    .message { padding: 12px 16px; border-radius: 8px; font-weight: 600; margin-bottom: 18px; border: 1px solid; }
    .message.success { background: #f0fdf4; color: #166534; border-color: #a7f3d0; }
    .message.error { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
  </style>
</head>
<body>
<div class="app">
  <aside class="sidebar" aria-label="Admin navigation">
      <div style="display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 1.1rem; color: #fff;"><i class="fas fa-layer-group" style="font-size: 22px; color: var(--primary-blue);"></i><span>CMS Panel</span></div>
      <div style="display: flex; gap: 12px; align-items: center; margin-top: 10px; padding: 10px; background: rgba(255,255,255,0.05); border-radius: 8px;">
          <img src="../images/profile1.png" alt="Profile" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover;">
          <div>
              <div style="font-weight:600; color: #fff;"><?php echo $adminName; ?></div>
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
        <h1 style="margin:0; font-size: 18px; font-weight:700;">New Blood Donor Entry</h1>
        <a href="SocialServiceClub.php" style="text-decoration:none; color: var(--muted-text);"><i class="fas fa-arrow-left"></i> Back to Service Page</a>
    </header>
    <main class="panel-body">
        <aside>
          <div class="card">
            <div class="card-head"><i class="fas fa-users" style="margin-right:8px;"></i>Recently Registered Donors</div>
            <div class="card-body">
              <ul class="donor-list">
                <?php if (!empty($recentDonors)): foreach ($recentDonors as $donor): ?>
                  <li class="donor-item">
                    <div class="donor-icon"><?php echo htmlspecialchars($donor['blood_group']); ?></div>
                    <div class="donor-details">
                      <div class="donor-name"><?php echo htmlspecialchars($donor['full_name']); ?></div>
                      <div class="donor-info">Blood Group: <?php echo htmlspecialchars($donor['blood_group']); ?></div>
                    </div>
                    <form method="POST" action="DonorEntry.php" onsubmit="return confirm('Are you sure you want to remove this donor?');">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="donor_id_to_remove" value="<?php echo $donor['donor_id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($CSRF); ?>">
                        <button type="submit" class="btn-remove" title="Remove Donor"><i class="fas fa-trash-alt"></i></button>
                    </form>
                  </li>
                <?php endforeach; else: ?>
                  <li class="donor-item"><div class="donor-info">No recent donors found.</div></li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </aside>

        <section>
            <div class="card">
                <div class="card-head"><i class="fas fa-edit" style="margin-right:8px;"></i>Donor Registration Form</div>
                <div class="card-body">
                  <?php if (!empty($message)): ?>
                    <div class="message <?php echo str_starts_with($message,'✅') ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></div>
                  <?php endif; ?>

                  <form method="POST" action="DonorEntry.php" enctype="multipart/form-data" class="form-grid">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($CSRF); ?>">
                    
                    <div class="full-width">
                      <label for="full_name">Full Name</label>
                      <input type="text" name="full_name" id="full_name" required placeholder="Enter donor's full name">
                    </div>
                    <div>
                      <label for="father_name">Father's Name</label>
                      <input type="text" name="father_name" id="father_name">
                    </div>
                    <div>
                      <label for="mother_name">Mother's Name</label>
                      <input type="text" name="mother_name" id="mother_name">
                    </div>
                    <div>
                      <label for="dob">Date of Birth</label>
                      <input type="date" name="dob" id="dob" required>
                    </div>
                    <div>
                      <label for="nid">NID Number</label>
                      <input type="number" name="nid" id="nid">
                    </div>
                    <div>
                      <label for="email">Email Address</label>
                      <input type="email" name="email" id="email" placeholder="example@domain.com">
                    </div>
                    <div>
                      <label for="contact_number">Contact Number</label>
                      <input type="text" name="contact_number" id="contact_number" required placeholder="+8801...">
                    </div>
                    <div>
                      <label for="blood_group">Blood Group</label>
                      <select name="blood_group" id="blood_group" required>
                            <option value="">Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                      </select>
                    </div>
                    <div class="full-width">
                      <label for="permanent_address">Permanent Address</label>
                      <textarea name="permanent_address" id="permanent_address" rows="3"></textarea>
                    </div>
                    <div>
                      <label>Gender</label>
                      <div style="display:flex; gap: 20px; padding-top: 10px;">
                        <label><input type="radio" name="gender" value="Male" checked> Male</label>
                        <label><input type="radio" name="gender" value="Female"> Female</label>
                      </div>
                    </div>
                    <div class="full-width" style="margin-top: 12px; text-align: right;">
                      <button type="submit" name="action" value="add" class="btn-primary"><i class="fas fa-user-plus"></i> Register Donor</button>
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