<?php if (session_status() === PHP_SESSION_NONE) session_start();
include __DIR__ . '/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Member Registration</title>
  <link rel="stylesheet" href="MemberRegistration.css">
</head>
<body>

  <!-- Navbar -->
  <header>
    <div class="navbar">
      <div class="hamburger" onclick="toggleMenu()">&#9776;</div>
      <div class="logo">
        <img src="logo.png" alt="CMS Logo">
      </div>
      <nav>
        <ul class="nav-links">
          <li><a href="home.html">Home</a></li>
          <li><a href="#">Social Service Club</a></li>
          <li><a href="#">UIU App Forum</a></li>
          <li><a href="#">UIU Computer Club</a></li>
          <li><a href="#">Search Donor</a></li>
        </ul>
      </nav>
      <div class="profile">
        <img src="./images//profile1.png" alt="User Profile">
      </div>
    </div>
  </header>

  <!-- Main Section -->
  <main class="main-section page-container">
    <div class="form-container">
      <h2>Member Registration Form</h2>
      <form action="#" method="POST">
        <!-- Full Name -->
        <label for="fullName">Full Name:</label>
        <input type="text" id="fullName" name="fullName" required>

        <!-- Gender -->
        <label for="gender">Gender:</label>
        <select id="gender" name="gender" required>
          <option value="">-- Select Gender --</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
        </select>

        <!-- Student ID -->
        <label for="studentId">Student ID:</label>
        <input type="text" id="studentId" name="studentId" required>

        <!-- Email -->
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <!-- Membership Type -->
        <label for="membershipType">Membership Type:</label>
        <select id="membershipType" name="membershipType" required>
          <option value="">-- Select Type --</option>
          <option value="regular">Regular</option>
          <option value="executive">Executive</option>
          <option value="volunteer">Volunteer</option>
        </select>

        <!-- Skills -->
        <label for="skills">Skills:</label>
        <input type="text" id="skills" name="skills" placeholder="e.g., Web Development, Design">

        <!-- Occupation -->
        <label for="occupation">Occupation:</label>
        <input type="text" id="occupation" name="occupation">

        <!-- Additional Information -->
        <label for="info">Additional Information:</label>
        <textarea id="info" name="info" rows="4" placeholder="Any other information you'd like to share"></textarea>

        <!-- Where did you hear us -->
        <label for="source">Where did you hear about us?</label>
        <input type="text" id="source" name="source">

        <!-- Why join -->
        <label for="reason">Why do you want to join us?</label>
        <textarea id="reason" name="reason" rows="4"></textarea>

        <!-- Submit -->
        <button type="submit">Submit</button>
      </form>
    </div>
  </main>

  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Club Management System | All Rights Reserved</p>
  </footer>

  <script>
    function toggleMenu() {
      document.querySelector(".nav-links").classList.toggle("show");
    }
  </script>

</body>
</html>
