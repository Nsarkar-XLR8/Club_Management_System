<?php
header("Content-Type: application/json");
require "db.php";

if (session_status() === PHP_SESSION_NONE) session_start();
include __DIR__ . '/navbar.php';


$bloodGroup = $_GET['bloodGroup'] ?? '';
$city       = $_GET['city'] ?? '';
$area       = $_GET['area'] ?? '';

$where = [];
$params = [];
$types = "";

if ($bloodGroup) { $where[] = "blood_group=?"; $params[] = $bloodGroup; $types .= "s"; }
if ($city)       { $where[] = "city LIKE ?";   $params[] = $city."%";   $types .= "s"; }
if ($area)       { $where[] = "area LIKE ?";   $params[] = $area."%";   $types .= "s"; }

$sql = "SELECT name, city, area, email, phone FROM donors";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
echo json_encode($res->fetch_all(MYSQLI_ASSOC));
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Club Search Donor</title>
  <link rel="stylesheet" href="SearchDonor.css">
</head>
<body>

  <!-- Navbar -->
<header>
  <div class="navbar">
    <!-- Left: Toggle -->
    <div class="hamburger" onclick="toggleMenu()">&#9776;</div>

    <!-- Middle: Logo -->
    <div class="logo">
      <img src="logo.png" alt="CMS Logo">
    </div>

    <!-- Center: Links -->
    <nav>
      <ul class="nav-links">
        <li><a href="home.html">Home</a></li>
        <li><a href="#">Social Service Club</a></li>
        <li><a href="#">Members Registration</a></li>
        <li><a href="#">Search Donor</a></li>
      </ul>
    </nav>

    <!-- Right: Profile -->
    <div class="profile">
      <img src="/images/profile1.png" alt="User Profile">
    </div>
  </div>
</header>



  <!-- Search Section -->
  <section class="search-section">

    <div class="search-box">
      <input type="text" placeholder="Blood Group">
      <input type="text" placeholder="City">
      <input type="text" placeholder="Area">
      <button>Search</button>
    </div>
  </section>

  <!-- Donor Table -->
  <section class="donor-table">
    <h2>Donor Details</h2>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Location</th>
            <th>Email</th>
            <th>Phone</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>John Doe</td>
            <td>Dhaka</td>
            <td>john@example.com</td>
            <td>+880123456789</td>
          </tr>
          <tr>
            <td>Jane Smith</td>
            <td>Chittagong</td>
            <td>jane@example.com</td>
            <td>+880987654321</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Club Management System | All Rights Reserved</p>
  </footer>
<script>
  function toggleMenu() {
    document.querySelector(".nav-links").classList.toggle("show");
  }
</script>

<script src="/scripts/DonorSearch.js"></script>



</body>
</html>
