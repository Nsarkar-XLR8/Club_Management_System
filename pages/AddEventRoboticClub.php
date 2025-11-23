<?php
// pages/AddEventRoboticClub.php — With modern admin design, full nav, and live preview
if (session_status() === PHP_SESSION_NONE) session_start();
include realpath(__DIR__ . '/../db.php');

if (!isset($_SESSION['role']) || strtolower((string)$_SESSION['role']) !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

$adminName = htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? 'Admin User', ENT_QUOTES, 'UTF-8');
$siteBase = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if (basename($siteBase) === 'pages') $siteBase = dirname($siteBase);
$logoutUrl = $siteBase . '/pages/logout.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$CSRF = $_SESSION['csrf_token'];

$clubId = 4; // UIU Robotic Club
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "❌ Invalid security token. Please try again.";
    } else {
        if (isset($_POST['action']) && $_POST['action'] === 'remove') {
            $event_id_to_remove = filter_input(INPUT_POST, 'event_id_to_remove', FILTER_VALIDATE_INT);
            if ($event_id_to_remove) {
                $stmt_get_image = $conn->prepare("SELECT image FROM events WHERE event_id = ? AND club_id = ?");
                $stmt_get_image->bind_param("ii", $event_id_to_remove, $clubId);
                $stmt_get_image->execute();
                if ($row = $stmt_get_image->get_result()->fetch_assoc()) {
                    $imagePathToDelete = realpath(__DIR__ . '/../' . substr($row['image'], 3));
                    if ($imagePathToDelete && strpos($imagePathToDelete, 'default_event.png') === false) {
                        @unlink($imagePathToDelete);
                    }
                }
                $stmt_get_image->close();

                $stmt_delete = $conn->prepare("DELETE FROM events WHERE event_id = ? AND club_id = ?");
                $stmt_delete->bind_param("ii", $event_id_to_remove, $clubId);
                $_SESSION['flash_message'] = $stmt_delete->execute() ? "✅ Event removed successfully." : "❌ Error removing event.";
                $stmt_delete->close();
                header("Location: AddEventRoboticClub.php");
                exit;
            }
        }
        elseif (isset($_POST['action']) && $_POST['action'] === 'add') {
            $title = trim($_POST['title'] ?? '');
            if (empty($title)) {
                $message = "❌ Title is a required field.";
            } else {
                $description = trim($_POST['description'] ?? '');
                $event_date  = trim($_POST['event_date'] ?? '');
                $location    = trim($_POST['location'] ?? '');
                $event_time  = trim($_POST['event_time'] ?? null);
                $type        = trim($_POST['type'] ?? 'upcoming');
                $imagePath   = "../images/default_event.png";
                
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES["image"]["name"]));
                    $imagePath = "../images/" . uniqid() . "_" . $safeName;
                    move_uploaded_file($_FILES['image']['tmp_name'], realpath(__DIR__ . '/../') . '/' . substr($imagePath, 3));
                }
                
                $sql = "INSERT INTO events (club_id, title, description, image, event_date, event_time, location, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssssss", $clubId, $title, $description, $imagePath, $event_date, $event_time, $location, $type);
                $message = $stmt->execute() ? "✅ Event added successfully!" : "❌ Error adding event: " . $stmt->error;
                $stmt->close();
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

$upcomingEvents = [];
$stmt_upcoming = $conn->prepare("SELECT event_id, title, image, event_date FROM events WHERE club_id = ? AND event_date >= CURDATE() ORDER BY event_date ASC, event_time ASC LIMIT 5");
$stmt_upcoming->bind_param("i", $clubId);
$stmt_upcoming->execute();
$result = $stmt_upcoming->get_result();
while ($ev = $result->fetch_assoc()) { $upcomingEvents[] = $ev; }
$stmt_upcoming->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event - Robotic Club</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --sidebar-w: 260px; --gap: 24px; --panel-radius: 12px; --muted-text: #6b7280; 
            --primary-blue: #ca8a04; --secondary-cyan: #eab308; --danger-red: #dc2626;
            --dark-blue: #0f172a;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: #f8fafc; color: #0f172a; }
        .app { display: flex; min-height: 100vh; }
        .sidebar { width: var(--sidebar-w); min-width: var(--sidebar-w); background: var(--dark-blue); color: #fff; height: 100vh; position: sticky; top: 0; display: flex; flex-direction: column; padding: 20px; overflow-y: auto; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 1.1rem; padding-bottom: 20px;}
        .brand i { font-size: 22px; color: var(--primary-blue); }
        .user-meta { display: flex; gap: 12px; align-items: center; margin-bottom: 20px; padding: 10px; background: rgba(255,255,255,0.05); border-radius: 8px; }
        .user-meta img { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; }
        .nav { display: flex; flex-direction: column; gap: 4px; flex-grow: 1; }
        .nav-link { display: flex; gap: 12px; align-items: center; padding: 11px 14px; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 8px; font-size: 0.95rem; transition: all 0.2s ease; }
        .nav-link:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .nav-link.active { background: linear-gradient(90deg, var(--primary-blue), var(--secondary-cyan)); color: #fff; font-weight: 600; }
        .nav-section { margin-top: 16px; color: rgba(255,255,255,0.5); font-weight: 700; font-size: 12px; padding: 8px 4px; text-transform: uppercase; }
        .nav-link.logout { background: transparent; width: 100%; border: none; font-family: inherit; cursor: pointer; }
        .content-wrap { flex: 1; display: flex; flex-direction: column; }
        .topbar { padding: 18px 28px; background: #fff; border-bottom: 1px solid #eef2f7; display: flex; justify-content: space-between; align-items: center; }
        .panel-body { display: grid; grid-template-columns: 0.4fr 0.6fr; gap: var(--gap); padding: var(--gap); align-items: start; }
        @media (max-width: 1100px) { .panel-body { grid-template-columns: 1fr; } }
        .card { background: #fff; border-radius: var(--panel-radius); border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .card-head { padding: 14px 18px; border-bottom: 1px solid #f1f5f9; font-weight: 700; }
        .card-body { padding: 18px; }
        .preview-card img { width: 100%; height: 180px; object-fit: cover; background: #f1f5f9; }
        .preview-meta { display: flex; flex-wrap: wrap; gap: 8px 16px; color: var(--muted-text); font-size: 0.85rem; margin-top: 12px; }
        .form-grid { display: grid; gap: 18px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        label { font-weight: 600; margin-bottom: 6px; font-size: 0.9rem; display: block; }
        input, select, textarea { width: 100%; padding: 12px 14px; border-radius: 8px; border: 1px solid #d1d5db; font-size: 1rem; }
        .btn-primary { padding: 12px 24px; border-radius: 8px; border: none; background: var(--primary-blue); color: #fff; font-weight: 700; cursor: pointer; }
        .up-item { display: flex; justify-content: space-between; align-items: center; }
        .btn-remove { background: none; border: none; color: var(--muted-text); cursor: pointer; }
        .btn-remove:hover { color: var(--danger-red); }
        .message { padding: 12px; border-radius: 8px; margin-bottom: 16px; font-weight: 600; }
        .message.success { background: #f0fdf4; color: #166534; }
        .message.error { background: #fef2f2; color: #b91c1c; }
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
            <h1 style="margin:0;">Add Robotic Club Event</h1>
            <a href="UIURoboticClub.php" style="color: var(--muted-text); text-decoration: none;">Back to Club Page</a>
        </header>
        <main class="panel-body">
            <aside>
                <div class="card" style="margin-bottom: var(--gap);">
                    <div class="card-head">Live Preview</div>
                    <div class="preview-card">
                        <img id="previewImage" src="../images/default_event.png" alt="Preview">
                        <div class="card-body">
                            <h3 id="previewTitle" style="margin:0; font-size:1.2rem;">Event Title</h3>
                            <p id="previewDesc" style="color:var(--muted-text); font-size:0.9rem; margin:8px 0;">Description will appear here...</p>
                            <div class="preview-meta">
                                <span><i class="fas fa-calendar"></i> <span id="previewDate">—</span></span>
                                <span style="margin-left:16px;"><i class="fas fa-map-marker-alt"></i> <span id="previewLocation">—</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-head">Upcoming Events</div>
                    <div class="card-body">
                        <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:16px;">
                            <?php if (!empty($upcomingEvents)): foreach ($upcomingEvents as $ev): ?>
                                <li class="up-item">
                                    <div style="display:flex; gap:12px; align-items:center;">
                                        <img src="<?php echo htmlspecialchars($ev['image'] ?: '../images/default_event.png'); ?>" alt="" style="width:48px; height:48px; border-radius:8px; object-fit:cover;">
                                        <div>
                                            <div style="font-weight:600;"><?php echo htmlspecialchars($ev['title']); ?></div>
                                            <div style="font-size:0.8rem; color:var(--muted-text);"><?php echo date("M d, Y", strtotime($ev['event_date'])); ?></div>
                                        </div>
                                    </div>
                                    <form method="POST" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="event_id_to_remove" value="<?php echo $ev['event_id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($CSRF); ?>">
                                        <button type="submit" class="btn-remove" title="Remove"><i class="fas fa-trash"></i></button>
                                    </form>
                                </li>
                            <?php endforeach; else: ?>
                                <li>No upcoming events.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </aside>
            <section>
                <div class="card">
                    <div class="card-head">New Event Details</div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="message <?php echo str_starts_with($message, '✅') ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></div>
                        <?php endif; ?>
                        <form method="POST" enctype="multipart/form-data" class="form-grid">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($CSRF); ?>">
                            <div><label for="titleInput">Title</label><input type="text" id="titleInput" name="title" required placeholder="e.g., Line Follower Challenge"></div>
                            <div><label for="descInput">Description</label><textarea id="descInput" name="description" rows="4" placeholder="Describe the competition or workshop..."></textarea></div>
                            <div class="form-row">
                                <div><label for="dateInput">Date</label><input type="date" id="dateInput" name="event_date" required></div>
                                <div><label for="timeInput">Time (Optional)</label><input type="time" id="timeInput" name="event_time"></div>
                            </div>
                            <div><label for="locInput">Location</label><input type="text" id="locInput" name="location" required placeholder="e.g., UIU Multipurpose Hall"></div>
                            <div class="form-row">
                                <div><label for="type">Type</label><select id="type" name="type"><option value="upcoming" selected>Upcoming</option><option value="recent">Recent</option></select></div>
                                <div><label for="imgInput">Event Image</label><input type="file" id="imgInput" name="image" accept="image/*"></div>
                            </div>
                            <div style="text-align:right;">
                                <button type="submit" name="action" value="add" class="btn-primary">Add Event</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const bindPreview = (inputId, previewId, defaultValue) => {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            if(input && preview) input.addEventListener('input', () => { preview.textContent = input.value || defaultValue; });
        };
        bindPreview('titleInput', 'previewTitle', 'Event Title');
        bindPreview('descInput', 'previewDesc', 'Description will appear here...');
        bindPreview('dateInput', 'previewDate', '—');
        bindPreview('locInput', 'previewLocation', '—');

        const imgInput = document.getElementById('imgInput');
        const previewImage = document.getElementById('previewImage');
        if (imgInput && previewImage) {
            imgInput.addEventListener('change', (e) => {
                const file = e.target.files?.[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (event) => { previewImage.src = event.target.result; };
                reader.readAsDataURL(file);
            });
        }
    });
</script>
</body>
</html>