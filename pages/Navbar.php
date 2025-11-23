<?php
// pages/navbar.php - FINAL MODERN REDESIGN
if (session_status() === PHP_SESSION_NONE) session_start();

$role = strtolower(trim((string)($_SESSION['role'] ?? 'guest')));
$name = htmlspecialchars(trim((string)($_SESSION['name'] ?? $_SESSION['username'] ?? 'Guest')), ENT_QUOTES, 'UTF-8');

$links = [
    'guest' => [
        ['url' => 'index.php', 'label' => 'Home'],
        ['url' => 'About.php', 'label' => 'About'],
        ['url' => 'Contact.php', 'label' => 'Contact'],
    ],
    'member' => [
        ['url' => 'index.php', 'label' => 'Home'],
        ['url' => 'UIUAppForum.php', 'label' => 'Forum'],
        ['url' => 'UIUComputerClub.php', 'label' => 'Computer Club'],
        ['url' => 'SocialServiceClub.php', 'label' => 'Social Service'],
        ['url' => 'UIURoboticClub.php', 'label' => 'Robotic Club'],
    ],
    'admin' => [
        ['url' => 'Admin_Dashboard.php', 'label' => 'Dashboard'],
        ['url' => 'UIUAppForum.php', 'label' => 'Forum'],
        ['url' => 'UIUComputerClub.php', 'label' => 'Computer Club'],
        ['url' => 'SocialServiceClub.php', 'label' => 'Social Service'],
         ['url' => 'UIURoboticClub.php', 'label' => 'Robotic Club'],
        ['url' => 'manage_users.php', 'label' => 'Manage Users'],
    ],
];

$siteBase = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if (basename($siteBase) === 'pages') {
  $siteBase = dirname($siteBase);
}
$logoutUrl = $siteBase . '/pages/logout.php';
$menu = $links[$role] ?? $links['guest'];
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<style>
    :root {
        --primary-color: #0077ff;
        --dark-blue: #0f172a;
        --text-color: #334155;
        --border-color: #e2e8f0;
        --container-width: 1280px;
    }
    body {
        margin-top: 70px; /* Prevent content from hiding behind sticky header */
    }
    header {
        background: #fff;
        border-bottom: 1px solid var(--border-color);
        position: fixed; /* Changed to fixed for better UX */
        top: 0; left: 0; right: 0;
        z-index: 1000;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }
    .navbar {
        max-width: var(--container-width);
        margin: auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 24px;
        height: 70px;
    }
    .logo {
        display: flex; align-items: center; gap: 12px;
        text-decoration: none; color: inherit;
    }
    .logo img { height: 45px; }
    .logo .brand-text { font-weight: 700; font-size: 1.2rem; color: var(--dark-blue); }
    .nav-links {
        list-style: none; display: flex;
        gap: 12px; margin: 0; padding: 0;
    }
    .nav-links a {
        color: var(--text-color);
        text-decoration: none;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 8px;
        transition: all 0.2s ease;
        position: relative;
    }
    .nav-links a:hover {
        background-color: #f1f5f9;
        color: var(--primary-color);
    }
    .nav-links a.active {
        color: var(--primary-color);
        background-color: #eff6ff;
    }
    .profile {
        display: flex; align-items: center; gap: 16px;
    }
    .user-info { text-align: right; }
    .user-name { font-weight: 600; color: var(--dark-blue); }
    .user-meta { display: flex; gap: 8px; align-items: center; margin-top: 2px; }
    .user-role {
        font-size: 0.8rem; font-weight: 700; text-transform: uppercase;
        padding: 2px 8px; border-radius: 50px;
    }
    .user-role.admin { background-color: #fef2f2; color: #dc2626; }
    .user-role.member { background-color: #eff6ff; color: #2563eb; }
    .logout-btn {
        background: none; border: none; color: var(--muted-text);
        cursor: pointer; font-size: 0.9rem; font-weight: 600;
        transition: color 0.2s ease;
    }
    .logout-btn:hover { color: #ef4444; }
    .profile-img {
        height: 48px; width: 48px; border-radius: 50%; object-fit: cover;
        border: 2px solid var(--border-color);
    }
    .hamburger { display: none; } /* Add responsive styles if needed */
</style>

<header>
  <div class="navbar" role="navigation" aria-label="Main">
    <a class="logo" href="<?php echo $role === 'admin' ? 'Admin_Dashboard.php' : 'index.php'; ?>">
      <img src="../images/CMS_Logo.png" alt="Logo">
      <!-- <span class="brand-text">CMS Panel</span> -->
    </a>

    <nav aria-label="Primary">
      <ul class="nav-links">
        <?php foreach ($menu as $item): ?>
          <li><a href="<?php echo htmlspecialchars($item['url']); ?>" class="<?php echo $currentFile === basename($item['url']) ? 'active' : ''; ?>"><?php echo htmlspecialchars($item['label']); ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="profile">
      <?php if (isset($_SESSION['user_id'])): ?>
        <div class="user-info">
          <div class="user-name"><?php echo $name; ?></div>
          <div class="user-meta">
            <span class="user-role <?php echo $role; ?>"><?php echo ucfirst($role); ?></span>
            <form method="POST" action="<?php echo htmlspecialchars($logoutUrl); ?>" style="margin:0;">
                <button type="submit" name="logout" class="logout-btn">Logout</button>
            </form>
          </div>
        </div>
        <img src="../images/profile1.png" alt="Profile" class="profile-img">
      <?php else: ?>
        <a class="nav-links" href="Login.php">Login</a>
      <?php endif; ?>
    </div>
  </div>
</header>