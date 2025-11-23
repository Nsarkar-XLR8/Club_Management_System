<?php


if (session_status() === PHP_SESSION_NONE) session_start();

// Admin guard
if (!isset($_SESSION['user_id']) || strtolower(trim((string)($_SESSION['role'] ?? ''))) !== 'admin') {
  header('Location: Login.php?return_to=' . urlencode($_SERVER['REQUEST_URI']));
  exit;
}

// DB connection (expects $conn as mysqli)
$dbPath = realpath(__DIR__ . '/../db.php');
if ($dbPath === false) die('Database connection file not found at: ' . __DIR__ . '/../db.php');
include $dbPath;

// CSRF
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
$CSRF = $_SESSION['csrf_token'];

// Helpers: detect columns and safe ordering
function column_exists(mysqli $conn, string $table, string $col): bool {
  $t = $conn->real_escape_string($table);
  $c = $conn->real_escape_string($col);
  $res = $conn->query("SHOW COLUMNS FROM `{$t}` LIKE '{$c}'");
  return ($res && $res->num_rows > 0);
}
function build_order_clause(mysqli $conn, string $table, array $candidateCols): string {
  $existing = [];
  foreach ($candidateCols as $c) if (column_exists($conn, $table, $c)) $existing[] = "`$c`";
  if (count($existing) === 0) return '';
  return 'ORDER BY COALESCE(' . implode(', ', $existing) . ') DESC';
}

// Fetch users (paged)
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

// total count
$totalUsers = 0;
$resCount = $conn->query("SELECT COUNT(*) FROM users");
if ($resCount && $r = $resCount->fetch_row()) $totalUsers = (int)$r[0];
$totalPages = max(1, (int)ceil($totalUsers / $perPage));

// safe ordering
$order = build_order_clause($conn, 'users', ['created_at','created_on','joined_at','added_at']);
$sql = "SELECT * FROM users " . ($order ?: "ORDER BY COALESCE(created_at, created_on, NOW()) DESC") . " LIMIT {$perPage} OFFSET {$offset}";
$users = [];
if ($res = $conn->query($sql)) {
  while ($row = $res->fetch_assoc()) $users[] = $row;
}

// small helpers
function role_badge(string $role): string {
  $r = strtolower((string)$role);
  if ($r === 'admin') return '<span class="badge success">Admin</span>';
  if ($r === 'moderator' || $r === 'staff') return '<span class="badge info">Moderator</span>';
  return '<span class="badge warning">Member</span>';
}
$adminName = htmlspecialchars($_SESSION['name'] ?? $_SESSION['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
$siteBase = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if (basename($siteBase) === 'pages') $siteBase = dirname($siteBase);
$logoutUrl = $siteBase . '/pages/logout.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Manage Users — Admin</title>

  <!-- Base styles (your existing CSS) -->
  <link rel="stylesheet" href="../Admin_Dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


  <!-- Page-specific improvements -->
  <style>
    /* Layout: fixed header, slim sidebar, centered content */
    html,body { height:100%; }
    .app { display:flex; min-height:100vh; background:#f6f7fb; }
    .sidebar { width:240px; min-width:200px; max-width:260px; }
    .content-wrap { flex:1; display:flex; flex-direction:column; }
    .topbar { position:sticky; top:0; z-index:30; }
    .content { padding:28px; display:flex; justify-content:center; }
    .panel { width:1200px; max-width:100%; border-radius:12px; overflow:hidden; box-shadow:0 8px 30px rgba(2,6,23,0.06); background:#fff; }

    /* Header inside panel */
    .panel-head-compact { display:flex; justify-content:space-between; align-items:center; padding:18px 22px; gap:12px; border-bottom:1px solid #eef2f7; }
    .panel-head-left { display:flex; gap:12px; align-items:center; }
    .page-title { font-size:18px; font-weight:700; color:#0f172a; margin:0; }
    .subtle { color:#6b7280; font-size:13px; }

    /* Search and controls */
    .controls { display:flex; gap:10px; align-items:center; }
    .search { display:flex; align-items:center; gap:8px; background:#f8fafc; padding:8px 10px; border-radius:10px; border:1px solid #e6eef8; }
    .search input { border:none; outline:none; background:transparent; width:320px; font-size:14px; color:#0f172a; }
    .toolbar .btn { padding:8px 12px; border-radius:10px; }

    /* Table area */
    .table-wrap { padding:0 18px 18px 18px; }
    .table-scroll { height:60vh; max-height:68vh; overflow:auto; border-radius:8px; margin-top:12px; }
    table.table { width:100%; border-collapse:collapse; min-width:880px; }
    table.table thead th { position:sticky; top:0; background:linear-gradient(180deg,#fff,#fbfdff); z-index:10; padding:12px 14px; text-align:left; font-weight:700; color:#64748b; border-bottom:1px solid #eef2f7; }
    table.table tbody td { padding:12px 14px; border-bottom:1px solid #f1f5f9; vertical-align:middle; color:#0f172a; }
    .table-avatar { width:40px;height:40px;border-radius:8px;object-fit:cover; }

    /* Action menu */
    .actions { display:flex; gap:8px; justify-content:flex-end; align-items:center; }
    .action-btn { padding:6px 8px; border-radius:8px; font-size:13px; }
    .menu { position:relative; }
    .menu .menu-list { display:none; position:absolute; right:0; top:36px; background:#fff; border:1px solid #e6eef8; box-shadow:0 12px 30px rgba(2,6,23,0.06); border-radius:8px; overflow:hidden; min-width:180px; z-index:40; }
    .menu .menu-list button { width:100%; padding:10px 12px; text-align:left; background:transparent; border:none; cursor:pointer; color:#0f172a; }
    .menu:hover .menu-list { display:block; }

    /* Pagination */
    .pagination { display:flex; gap:8px; align-items:center; padding:14px 18px; justify-content:flex-end; border-top:1px solid #eef2f7; }
    .page-btn { padding:8px 10px; border-radius:8px; background:#f1f5f9; border:none; cursor:pointer; }
    .page-btn.active { background:#2563eb; color:#fff; }

    /* Empty state */
    .empty { padding:36px; text-align:center; color:#64748b; }

    /* Responsive tweaks */
    @media (max-width:1100px) {
      .panel { margin:0 12px; }
      .search input { width:200px; }
    }
    @media (max-width:720px) {
      .sidebar { display:none; }
      .search input { width:120px; }
      table.table { min-width:720px; }
    }
  </style>
</head>
<body>
  <div class="app">
    <!-- Sidebar (same markup but slim) -->
    <aside class="sidebar" aria-label="Admin navigation">
      <div class="brand" style="padding:20px 16px;"><i class="fas fa-layer-group"></i><span style="margin-left:8px">CMS Panel</span></div>
      <div style="padding:0 16px 18px;">
        <div style="display:flex;align-items:center;gap:10px;">
          <img src="../images/profile1.png" alt="" style="width:44px;height:44px;border-radius:8px;">
          <div>
            <div style="font-weight:700;color:#fff;margin-bottom:4px;"><?php echo $adminName; ?></div>
            <div class="role role-admin" style="font-size:12px;">Admin</div>
          </div>
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

    <!-- Content -->
    <div class="content-wrap">
      <header class="topbar">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 22px;">
          <div style="display:flex;align-items:center;gap:12px;">
            <button class="menu-toggle" onclick="document.body.classList.toggle('sidebar-collapsed')" aria-label="Toggle"><i class="fas fa-bars"></i></button>
            <div style="display:flex;flex-direction:column;">
              <div style="font-weight:700;font-size:15px;color:#0f172a">Club Management System</div>
              <div class="subtle">Admin — Manage users</div>
            </div>
          </div>
          <div style="display:flex;gap:10px;align-items:center;">
            <!-- <button id="themeToggle" class="btn ghost"><i class="fas fa-moon"></i></button> -->
            <!-- <a class="btn primary" href="AddMemberComputerClub.php"><i class="fas fa-user-plus"></i> Add member</a> -->
          </div>
        </div>
      </header>

      <div class="content">
        <section class="panel" aria-labelledby="manage-users-heading">
          <div class="panel-head-compact">
            <div class="panel-head-left">
              <h2 class="page-title" id="manage-users-heading">Manage Users</h2>
              <div class="subtle" style="margin-left:8px;"><?php echo number_format($totalUsers); ?> users</div>
            </div>

            <div class="controls toolbar">
              <div class="search" role="search" aria-label="Search users">
                <i class="fas fa-search" style="color:#64748b"></i>
                <input id="searchInput" type="search" placeholder="Search name, email, role" aria-label="Search users">
                <button id="clearSearch" class="btn ghost" title="Clear"><i class="fas fa-times"></i></button>
              </div>
              <a class="btn ghost" href="Manage_Users.php?p=1" title="Refresh"><i class="fas fa-sync"></i> Refresh</a>
            </div>
          </div>

          <div class="table-wrap">
            <div class="table-scroll" id="tableScroll" tabindex="0">
              <?php if (empty($users)): ?>
                <div class="empty">No users to display.</div>
              <?php else: ?>
                <table class="table" role="table" aria-label="Users list">
                  <thead>
                    <tr>
                      <th style="min-width:260px">User</th>
                      <th style="min-width:220px">Email</th>
                      <th style="width:120px">Role</th>
                      <th style="width:120px">Joined</th>
                      <th style="width:110px">Status</th>
                      <th style="width:140px" class="text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="usersTbody">
                    <?php foreach ($users as $u):
                      $uid = $u['id'] ?? $u['user_id'] ?? $u['uid'] ?? null;
                      $uidEsc = htmlspecialchars((string)$uid);
                      $name = htmlspecialchars($u['name'] ?? $u['username'] ?? $u['email'] ?? 'User');
                      $email = htmlspecialchars($u['email'] ?? '');
                      $role = htmlspecialchars($u['role'] ?? 'member');
                      $joined = htmlspecialchars($u['created_at'] ?? $u['created_on'] ?? $u['joined_at'] ?? '');
                      $activeVal = $u['active'] ?? $u['status'] ?? null;
                      $status = 'active';
                      if ($activeVal !== null && ((string)$activeVal === '0' || strtolower((string)$activeVal) === 'inactive')) $status = 'inactive';
                    ?>
                      <tr data-name="<?php echo strtolower($name); ?>" data-email="<?php echo strtolower($email); ?>" data-role="<?php echo strtolower($role); ?>">
                        <td>
                          <div style="display:flex;align-items:center;gap:12px">
                            <img src="<?php echo htmlspecialchars($u['avatar'] ?? '../images/profile1.png'); ?>" alt="" class="table-avatar">
                            <div>
                              <div style="font-weight:700;"><?php echo $name; ?></div>
                              <div class="subtle"><?php echo htmlspecialchars($u['username'] ?? ''); ?></div>
                            </div>
                          </div>
                        </td>
                        <td><?php echo $email; ?></td>
                        <td>
                          <?php echo role_badge($role); ?>
                          <div class="subtle" style="margin-top:6px;"><?php echo htmlspecialchars($role); ?></div>
                        </td>
                        <td><?php echo $joined ?: '<span class="subtle">—</span>'; ?></td>
                        <td><span class="badge <?php echo $status==='active' ? 'info' : 'warning'; ?>"><?php echo ucfirst($status); ?></span></td>
                        <td class="text-right">
                          <div class="actions">
                            <button class="action-btn action view-btn" data-action="view" data-id="<?php echo $uidEsc; ?>" title="View"><i class="fas fa-eye"></i></button>

                            <?php if ($role !== 'admin'): ?>
                              <div class="menu">
                                <button class="action-btn action" aria-haspopup="true" title="More"><i class="fas fa-ellipsis-v"></i></button>
                                <div class="menu-list" role="menu" aria-hidden="true">
                                  <button type="button" data-action="promote" data-id="<?php echo $uidEsc; ?>">Promote to Admin</button>
                                  <button type="button" data-action="demote" data-id="<?php echo $uidEsc; ?>">Demote to Member</button>
                                  <button type="button" data-action="toggle_active" data-id="<?php echo $uidEsc; ?>"><?php echo $status === 'active' ? 'Deactivate' : 'Activate'; ?></button>
                                </div>
                              </div>
                            <?php else: ?>
                              <button class="action-btn" disabled title="Admin"><i class="fas fa-shield-alt"></i></button>
                            <?php endif; ?>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>

            <div class="pagination" role="navigation" aria-label="Pagination">
              <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                if ($page > 1) echo '<a class="page-btn" href="?p=' . ($page-1) . '"><i class="fas fa-chevron-left"></i></a>';
                for ($i = $start; $i <= $end; $i++) {
                  $active = $i === $page ? ' active' : '';
                  echo '<a class="page-btn' . $active . '" href="?p=' . $i . '">' . $i . '</a>';
                }
                if ($page < $totalPages) echo '<a class="page-btn" href="?p=' . ($page+1) . '"><i class="fas fa-chevron-right"></i></a>';
              ?>
            </div>
          </div>
        </section>
      </div>

      <footer style="padding:16px 24px;border-top:1px solid #eef2f7;background:#fff;text-align-center" class="subtle">
        &copy; <?php echo date('Y'); ?> Club Management System
      </footer>
    </div>
  </div>

<script>
  // Client behavior: search, actions, keyboard shortcuts
  const CSRF = '<?php echo addslashes($CSRF); ?>';
  const searchInput = document.getElementById('searchInput');
  const usersTbody = document.getElementById('usersTbody');
  const clearBtn = document.getElementById('clearSearch');

  function filterRows() {
    const q = (searchInput.value || '').trim().toLowerCase();
    for (const tr of usersTbody.querySelectorAll('tr')) {
      if (!q) { tr.style.display = ''; continue; }
      const name = tr.dataset.name || '';
      const email = tr.dataset.email || '';
      const role = tr.dataset.role || '';
      tr.style.display = (name.includes(q) || email.includes(q) || role.includes(q)) ? '' : 'none';
    }
  }
  searchInput?.addEventListener('input', filterRows);
  clearBtn?.addEventListener('click', () => { searchInput.value=''; filterRows(); searchInput.focus(); });

  // Delegated actions
  document.addEventListener('click', async (e) => {
    const el = e.target.closest('[data-action]');
    if (!el) return;
    const action = el.getAttribute('data-action');
    const id = el.getAttribute('data-id');
    if (!action || !id) return;

    if (action === 'view') {
      window.open('../profile.php?id=' + encodeURIComponent(id), '_blank');
      return;
    }

    const confirmMap = {
      promote: 'Promote this user to Admin?',
      demote: 'Demote this user to Member?',
      toggle_active: 'Toggle active status for this user?'
    };
    if (confirmMap[action] && !confirm(confirmMap[action])) return;

    try {
      const form = new FormData();
      form.append('action', action);
      form.append('id', id);
      form.append('csrf_token', CSRF);

      const res = await fetch('admin_action.php', { method:'POST', body: form, credentials:'same-origin' });
      const json = await res.json();
      if (res.ok && json.success) {
        // If the server returns updatedRowHtml, replace the row, otherwise reload
        if (json.updatedRowHtml) {
          const btn = document.querySelector('[data-id="'+id+'"]');
          const row = btn?.closest('tr');
          if (row) row.outerHTML = json.updatedRowHtml;
        } else {
          location.reload();
        }
      } else {
        alert(json.message || 'Action failed');
      }
    } catch (err) {
      console.error(err);
      alert('Request failed. See console for details.');
    }
  });

  // Accessibility: focus table scroll on keyboard press '/'
  document.addEventListener('keydown', (e) => {
    if (e.key === '/') {
      e.preventDefault();
      searchInput.focus();
    }
  });

  // Theme toggle persistence
  (function(){
    const btn = document.getElementById('themeToggle');
    const cls = 'theme-dark';
    if (localStorage.getItem('cms_theme') === 'dark') document.documentElement.classList.add(cls);
    btn?.addEventListener('click', () => {
      const now = document.documentElement.classList.toggle(cls);
      localStorage.setItem('cms_theme', now ? 'dark' : 'light');
    });
  })();
</script>
</body>
</html>
