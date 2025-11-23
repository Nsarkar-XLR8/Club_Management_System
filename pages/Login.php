<?php
// pages/Login.php
// Secure login with RBAC normalization, plaintext-password migration fallback, and role-based redirects.
// Requirements: include ../db.php that sets $conn (mysqli).
if (session_status() === PHP_SESSION_NONE) session_start();
include __DIR__ . '/../db.php';

// Configuration
$MAX_ATTEMPTS = 6;
$LOCK_MINUTES = 10;
$DEFAULT_ADMIN_REDIRECT = 'Admin_Dashboard.php';
$DEFAULT_MEMBER_REDIRECT = 'index.php';

// Initialize attempt tracking
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['login_locked_until'])) $_SESSION['login_locked_until'] = 0;

// Ensure CSRF token exists for subsequent pages/actions
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
$CSRF = $_SESSION['csrf_token'];

// Helper: canonicalize role from DB to 'admin'|'member'
function canonical_role($raw) {
    $r = strtolower(trim((string)$raw));
    $map = [
        'admin' => 'admin',
        'administrator' => 'admin',
        'superadmin' => 'admin',
        '1' => 'admin',
        'root' => 'admin',
        'member' => 'member',
        'user' => 'member',
        '0' => 'member',
    ];
    return $map[$r] ?? ($r === 'admin' ? 'admin' : 'member');
}

// Safe return-to handling: allow only local relative paths (no host, no ..)
function safe_return_to($path) {
    if (!$path) return null;
    $p = parse_url($path, PHP_URL_PATH);
    if (!$p) return null;
    if (strpos($p, '..') !== false) return null;
    return $p;
}

$message = '';
$redirectTo = null;

// If return_to specified, sanitize it
if (!empty($_GET['return_to'])) {
    $safe = safe_return_to($_GET['return_to']);
    if ($safe) $redirectTo = $safe;
}

// Lockout check
if (time() < (int)$_SESSION['login_locked_until']) {
    $remaining = (int)($_SESSION['login_locked_until'] - time());
    $minutes = ceil($remaining / 60);
    $message = "Too many failed attempts. Try again in {$minutes} minute(s).";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $message = "Please enter both email and password.";
    } else {
        // Fetch user by email
        $sql = "SELECT user_id, name, email, password, role FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $message = "Service temporarily unavailable.";
        } else {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res && $res->num_rows ? $res->fetch_assoc() : null;
            $stmt->close();

            // Verification with migration support:
            // 1) If password column holds a valid hash, use password_verify.
            // 2) If password column appears plain-text and equals input, accept and re-hash immediately.
            $verified = false;
            if ($user) {
                $stored = (string)($user['password'] ?? '');
                // Detect plausible hash: starts with $2y$ or $2b$ or $argon2i or $argon2id
                $isHash = (strpos($stored, '$2y$') === 0 || strpos($stored, '$2b$') === 0
                           || stripos($stored, 'argon2') === 0 || strpos($stored, '$argon2') === 0);
                if ($isHash) {
                    $verified = password_verify($password, $stored);
                } else {
                    // legacy plain-text fallback (migration only)
                    if ($stored === $password) {
                        $verified = true;
                        // Re-hash with password_hash and update DB immediately
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $ustmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                        if ($ustmt) {
                            $ustmt->bind_param('si', $newHash, $user['user_id']);
                            $ustmt->execute();
                            $ustmt->close();
                        }
                    } else {
                        // Anti-timing: run a dummy hash verify
                        password_verify($password, password_hash('dummy_protect', PASSWORD_DEFAULT));
                    }
                }
            } else {
                // Dummy hash verify to equalize timing when user missing
                password_verify($password, password_hash('dummy_protect', PASSWORD_DEFAULT));
            }

            if ($user && $verified) {
                // Successful login
                $_SESSION['login_attempts'] = 0;
                $_SESSION['login_locked_until'] = 0;
                session_regenerate_id(true);

                $role = canonical_role($user['role'] ?? 'member');

                // Save session identity
                $_SESSION['user_id'] = (int)$user['user_id'];
                $_SESSION['role'] = $role;
                $_SESSION['name'] = $user['name'] ?? $user['email'];
                $_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? $CSRF;

                // Redirect to safe target if provided, else role-based landing
                if ($redirectTo) {
                    header('Location: ' . $redirectTo);
                    exit;
                }

                if ($role === 'admin') {
                    header('Location: ' . $DEFAULT_ADMIN_REDIRECT);
                    exit;
                } else {
                    header('Location: ' . $DEFAULT_MEMBER_REDIRECT);
                    exit;
                }
            } else {
                // Failed login; increment attempt counter
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                if ($_SESSION['login_attempts'] >= $MAX_ATTEMPTS) {
                    $_SESSION['login_locked_until'] = time() + ($LOCK_MINUTES * 60);
                    $message = "Too many failed attempts. Your account is locked for {$LOCK_MINUTES} minute(s).";
                } else {
                    $remaining = $MAX_ATTEMPTS - $_SESSION['login_attempts'];
                    $message = "Invalid credentials. You have {$remaining} attempt(s) remaining.";
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Login • CMS Panel</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../Login.css"> <!-- your CSS file in same folder -->
</head>
<body>
  <section>
    <div class="login-box" role="main" aria-labelledby="loginTitle">
      <form method="POST" action="" style="width:100%;display:flex;flex-direction:column;align-items:center;" novalidate>
        <h2 id="loginTitle">Login</h2>

        <?php if ($message): ?>
          <p style="color:#b91c1c; font-weight:600; margin-top:8px;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <div class="input-box">
          <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
          <label for="email">Email</label>
          <span class="icon">&#9993;</span>
        </div>

        <div class="input-box">
          <input type="password" name="password" id="password" required>
          <label for="password">Password</label>
          <span class="icon">&#128274;</span>
        </div>

        <div class="remember-forgot">
          <label><input type="checkbox" name="remember"> Remember me</label>
          <a href="forgot_password.php">Forgot Password?</a>
        </div>

        <button type="submit">Login</button>

        <div class="register-link">
          <p>Don't have an account? <a href="Signup.php">Sign Up</a></p>
        </div>
      </form>
    </div>
  </section>

  <script>
    // Label lift UX to match your CSS
    document.querySelectorAll('.input-box input').forEach(input => {
      const label = input.nextElementSibling;
      const toggle = () => {
        if (input.value && input.value.trim() !== '') {
          label.style.top = '-5px';
          label.style.fontSize = '0.85em';
        } else {
          label.style.top = '50%';
          label.style.fontSize = '1em';
        }
      };
      input.addEventListener('input', toggle);
      input.addEventListener('focus', () => { label.style.top = '-5px'; label.style.fontSize = '0.85em'; });
      input.addEventListener('blur', toggle);
      toggle();
    });
  </script>
</body>
</html>
