<?php
session_start();
include("../db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$password || !$confirm) {
        $message = "⚠️ All fields are required.";
    } elseif ($password !== $confirm) {
        $message = "⚠️ Passwords do not match.";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "⚠️ Email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'member', NOW())");
            $stmt->bind_param("sss", $name, $email, $hash);

            if ($stmt->execute()) {
                $message = "✅ Signup successful! You can now <a href='login.php'>login</a>.";
            } else {
                $message = "❌ Signup failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up</title>
  <link rel="stylesheet" href="../signup.css">
</head>
<body>
  <section>
    <div class="SignUp-box">
      <form method="POST" action="">
        <h2>Sign Up</h2>
        <?php if ($message): ?>
          <p style="color:red; font-weight:bold;"><?php echo $message; ?></p>
        <?php endif; ?>

        <div class="input-box">
          <input type="text" name="name" required>
          <label>Username</label>
        </div>
        <div class="input-box">
          <input type="email" name="email" required>
          <label>Email</label>
        </div>
        <div class="input-box">
          <input type="password" name="password" required>
          <label>Password</label>
        </div>
        <div class="input-box">
          <input type="password" name="confirm_password" required>
          <label>Re-Type Password</label>
        </div>
        <button type="submit">Sign Up</button>
        <div class="Login-link">
          <p>Go to <a href="login.php">Login</a> page.</p>
        </div>
      </form>
    </div>
  </section>
</body>
</html>
