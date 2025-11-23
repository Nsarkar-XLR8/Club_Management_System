<?php
// AddEventSocialService.php — With modern design and Remove Event functionality
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. CSRF Token Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "❌ Invalid security token. Please try again.";
    } else {
        // --- HANDLE EVENT REMOVAL ---
        if (isset($_POST['action']) && $_POST['action'] === 'remove') {
            $event_id_to_remove = filter_input(INPUT_POST, 'event_id_to_remove', FILTER_VALIDATE_INT);
            if ($event_id_to_remove) {
                // Get image path to delete the file
                $stmt_get_image = $conn->prepare("SELECT image FROM social_service_events WHERE event_id = ? AND club_id = ?");
                $stmt_get_image->bind_param("ii", $event_id_to_remove, $clubId);
                $stmt_get_image->execute();
                $result = $stmt_get_image->get_result();
                if ($row = $result->fetch_assoc()) {
                    $imagePathToDelete = realpath(__DIR__ . '/../' . substr($row['image'], 3));
                    if ($imagePathToDelete && strpos($imagePathToDelete, 'default_event.png') === false) {
                        @unlink($imagePathToDelete);
                    }
                }
                $stmt_get_image->close();

                // Delete the event record
                $stmt_delete = $conn->prepare("DELETE FROM social_service_events WHERE event_id = ? AND club_id = ?");
                $stmt_delete->bind_param("ii", $event_id_to_remove, $clubId);
                if ($stmt_delete->execute()) {
                    $_SESSION['flash_message'] = "✅ Event removed successfully.";
                } else {
                    $_SESSION['flash_message'] = "❌ Error removing event.";
                }
                $stmt_delete->close();
                header("Location: AddEventSocialService.php");
                exit;
            }
        }
        // --- HANDLE EVENT ADDITION ---
        elseif (isset($_POST['action']) && $_POST['action'] === 'add') {
            $title       = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $event_date  = trim($_POST['event_date'] ?? '');
            $location    = trim($_POST['location'] ?? '');
            
            if (empty($title) || empty($description) || empty($event_date) || empty($location)) {
                $message = "❌ Please fill in all required fields.";
            } else {
                $event_time  = trim($_POST['event_time'] ?? '');
                $type        = trim($_POST['type'] ?? 'upcoming');
                $imagePath   = "../images/default_event.png";
                
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $targetDir = __DIR__ . "/../images/";
                    if (!is_dir($targetDir)) mkdir($targetDir, 0775, true);
                    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES["image"]["name"]));
                    $fileName = uniqid() . "_" . $safeName;
                    $targetFile = $targetDir . $fileName;
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (in_array($_FILES['image']['type'], $allowedTypes) && $_FILES['image']['size'] < 2097152) { // 2MB
                        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                            $imagePath = "../images/" . $fileName;
                        }
                    } else {
                        $message = "❌ Invalid file. Please upload a JPG, PNG, or GIF under 2MB.";
                    }
                }
                
                if (empty($message)) {
                    $sql = "INSERT INTO social_service_events (club_id, title, description, image, event_date, event_time, location, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        $stmt->bind_param("isssssss", $clubId, $title, $description, $imagePath, $event_date, $event_time, $location, $type);
                        if ($stmt->execute()) {
                            $message = "✅ Event added successfully!";
                        } else {
                            $message = "❌ Error adding event: " . $stmt->error;
                        }
                        $stmt->close();
                    }
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

// Fetch upcoming events for this club
$upcoming = [];
$stmt_upcoming = $conn->prepare("SELECT event_id, title, image, event_date FROM social_service_events WHERE club_id = ? AND event_date >= CURDATE() ORDER BY event_date ASC, event_time ASC LIMIT 5");
if ($stmt_upcoming) {
    $stmt_upcoming->bind_param("i", $clubId);
    $stmt_upcoming->execute();
    $result = $stmt_upcoming->get_result();
    while ($ev = $result->fetch_assoc()) $upcoming[] = $ev;
    $stmt_upcoming->close();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Add Event — Social Service Club</title>
  <link rel="stylesheet" href="../AddEvent.css">
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
    .nav .nav-link:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .nav .nav-link.active { background: linear-gradient(90deg, var(--primary-blue), var(--secondary-cyan)); color: #fff; box-shadow: 0 4px 12px rgba(37,99,235,0.2); font-weight: 600; }
    .nav-link.logout { background: transparent; width: 100%; border: none; font-family: inherit; font-size: inherit; text-align: left; cursor: pointer; }
    .content-wrap { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    .topbar { display: flex; align-items: center; justify-content: space-between; padding: 18px 28px; border-bottom: 1px solid #eef2f7; background: #fff; }
    .panel { flex: 1; padding: var(--gap); }
    .panel-body { display: grid; grid-template-columns: 0.4fr 0.6fr; gap: var(--gap); align-items: start; }
    @media (max-width: 1100px) { .panel-body { grid-template-columns: 1fr; } }
    .card { background: #fff; border-radius: var(--panel-radius); border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; }
    .card-head { padding: 14px 18px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; font-weight: 700; }
    .card-body { padding: 18px; }
    .preview-card img { width: 100%; height: 180px; object-fit: cover; display: block; background: #f1f5f9; }
    .preview-desc { color: #475569; margin: 8px 0; font-size: 0.9rem; line-height: 1.5; }
    .preview-meta { display: flex; flex-wrap: wrap; gap: 8px 16px; color: var(--muted-text); font-size: 0.85rem; margin-top: 12px; border-top: 1px solid #f1f5f9; padding-top: 12px; }
    .up-item { display: flex; gap: 12px; align-items: flex-start; justify-content: space-between; }
    .up-item-details { display: flex; gap: 12px; align-items: flex-start; flex-grow: 1; }
    .up-thumb { width: 64px; height: 64px; border-radius: 8px; object-fit: cover; background: #f1f5f9; flex-shrink: 0; }
    .up-title { font-weight: 600; }
    .up-info { font-size: 13px; color: #64748b; margin-top: 2px; }
    .btn-remove { background: none; border: none; color: var(--muted-text); cursor: pointer; padding: 4px; border-radius: 4px; }
    .btn-remove:hover { color: var(--danger-red); background-color: #fee2e2; }
    .form-grid { display: grid; grid-template-columns: 1fr; gap: 18px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; }
    input, textarea, select { width: 100%; padding: 12px 14px; border-radius: 8px; border: 1px solid #d1d5db; font-size: 15px; }
    .btn-primary { padding: 12px 24px; border-radius: 8px; border: none; color: #fff; background: linear-gradient(135deg, var(--primary-blue), var(--secondary-cyan)); font-weight: 700; cursor: pointer; }
    .message { padding: 12px 16px; border-radius: 8px; font-weight: 600; margin-bottom: 18px; border: 1px solid; }
    .message.success { background: #f0fdf4; color: #166534; border-color: #a7f3d0; }
    .message.error { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
  </style>
</head>
<body>
<div class="app">
  <aside class="sidebar" aria-label="Admin navigation">
      <div style="display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 1.1rem;"><i class="fas fa-layer-group" style="font-size: 22px; color: var(--primary-blue);"></i><span>CMS Panel</span></div>
      <div style="display: flex; gap: 12px; align-items: center; margin-top: 10px; padding: 10px; background: rgba(255,255,255,0.05); border-radius: 8px;">
          <img src="../images/profile1.png" alt="Profile" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover;">
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
        <h1 style="margin:0; font-size: 18px; font-weight:700;">Add New Social Service Event</h1>
        <a href="SocialServiceClub.php" style="text-decoration:none; color: var(--muted-text); display:inline-flex; align-items:center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Club Page
        </a>
    </header>
    <main class="panel">
      <div class="panel-body">
        <aside>
          <div class="card" style="margin-bottom: var(--gap);">
            <div class="card-head"><i class="fas fa-eye" style="margin-right:8px; color: var(--primary-blue);"></i>Live Preview</div>
            <div class="preview-card">
              <img id="previewImage" src="../images/default_event.png" alt="Preview image">
              <div class="card-body">
                <h3 id="previewTitle" style="margin:0; font-size: 1.1rem; line-height: 1.3;">Event Title</h3>
                <p id="previewDesc" class="preview-desc">Event description will appear here...</p>
                <div class="preview-meta">
                  <div><i class="fas fa-calendar"></i> <span id="previewDate">—</span></div>
                  <div><i class="fas fa-clock"></i> <span id="previewTime">—</span></div>
                  <div><i class="fas fa-map-marker-alt"></i> <span id="previewLocation">—</span></div>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-head"><i class="fas fa-calendar-check" style="margin-right:8px; color: var(--primary-blue);"></i>Upcoming Events</div>
            <div class="card-body">
              <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 16px;">
                <?php if (!empty($upcoming)): foreach ($upcoming as $ev): ?>
                  <li class="up-item">
                    <div class="up-item-details">
                        <img class="up-thumb" src="<?php echo htmlspecialchars($ev['image'] ?: '../images/default_event.png'); ?>" alt="">
                        <div>
                          <div class="up-title"><?php echo htmlspecialchars($ev['title']); ?></div>
                          <div class="up-info"><?php echo htmlspecialchars(date("D, M j, Y", strtotime($ev['event_date']))); ?></div>
                        </div>
                    </div>
                    <form method="POST" action="AddEventSocialService.php" onsubmit="return confirm('Are you sure you want to remove this event?');">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="event_id_to_remove" value="<?php echo $ev['event_id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($CSRF); ?>">
                        <button type="submit" class="btn-remove" title="Remove Event">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                  </li>
                <?php endforeach; else: ?>
                  <li class="up-item"><div class="up-info">No upcoming events for this club.</div></li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </aside>

        <section>
            <div class="card">
                <div class="card-head"><i class="fas fa-edit" style="margin-right:8px; color: var(--primary-blue);"></i>Event Details</div>
                <div class="card-body">
                  <?php if (!empty($message)): ?>
                    <div class="message <?php echo str_starts_with($message,'✅') ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></div>
                  <?php endif; ?>
                  <form method="POST" action="AddEventSocialService.php" enctype="multipart/form-data" class="form-grid">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($CSRF); ?>">
                    <div>
                      <label for="titleInput">Title</label>
                      <input type="text" name="title" id="titleInput" required placeholder="e.g., Winter Clothes Drive">
                    </div>
                    <div>
                      <label for="descInput">Description</label>
                      <textarea name="description" id="descInput" required placeholder="Describe the event's goals and activities..."></textarea>
                    </div>
                    <div class="form-row">
                      <div>
                        <label for="dateInput">Date</label>
                        <input type="date" name="event_date" id="dateInput" required>
                      </div>
                      <div>
                        <label for="timeInput">Time <span style="color:var(--muted-text); font-weight:400;">(Optional)</span></label>
                        <input type="time" name="event_time" id="timeInput">
                      </div>
                    </div>
                    <div>
                        <label for="locInput">Location</label>
                        <input type="text" name="location" id="locInput" required placeholder="e.g., UIU Campus Plaza">
                    </div>
                    <div class="form-row">
                        <div>
                            <label for="typeInput">Type</label>
                            <select name="type" id="typeInput" required>
                                <option value="upcoming" selected>Upcoming</option>
                                <option value="recent">Recent</option>
                            </select>
                        </div>
                        <div>
                            <label for="imgInput">Event Image <span style="color:var(--muted-text); font-weight:400;">(Optional)</span></label>
                            <input type="file" name="image" id="imgInput" accept="image/*" style="padding:9px 14px;">
                        </div>
                    </div>
                    <div style="margin-top: 12px; text-align: right;">
                      <button type="submit" name="action" value="add" class="btn-primary"><i class="fas fa-plus"></i> Add Event</button>
                    </div>
                  </form>
                </div>
            </div>
        </section>
      </div>
    </main>
    <footer style="text-align:center; padding: 18px; background:#fff; border-top:1px solid #eef2f7; color:var(--muted-text); font-size:0.9rem;">&copy; <?php echo date('Y'); ?> Club Management System</footer>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const fields = {
        titleInput: { el: 'previewTitle', default: 'Event Title' },
        descInput: { el: 'previewDesc', default: 'Event description will appear here...' },
        dateInput: { el: 'previewDate', default: '—' },
        timeInput: { el: 'previewTime', default: '—' },
        locInput: { el: 'previewLocation', default: '—' }
    };
    for (const [inputId, preview] of Object.entries(fields)) {
        const inputElement = document.getElementById(inputId);
        const previewElement = document.getElementById(preview.el);
        if (inputElement && previewElement) {
            inputElement.addEventListener('input', () => {
                previewElement.textContent = inputElement.value || preview.default;
            });
        }
    }
    const imgInput = document.getElementById('imgInput');
    const previewImage = document.getElementById('previewImage');
    if (imgInput && previewImage) {
        imgInput.addEventListener('change', (e) => {
            const file = e.target.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = ev => { previewImage.src = ev.target.result; };
            reader.readAsDataURL(file);
        });
    }
  });
</script>
</body>
</html>