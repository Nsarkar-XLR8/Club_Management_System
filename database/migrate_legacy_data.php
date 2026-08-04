<?php
/**
 * UIU Club Management System (CMS) - Legacy Data Migration Script
 * Migrates existing procedural PHP database tables into the normalized MySQL schema.
 */

// Load DB connection parameters
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'cms';

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error . "\n");
}
$conn->set_charset("utf8mb4");

echo "=== UIU CMS Migration Tool ===\n";

// 1. Ensure database exists
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($dbname);

// 2. Import Schema
$schemaFile = __DIR__ . '/schema.sql';
if (file_exists($schemaFile)) {
    echo "[1/3] Applying Schema SQL...\n";
    $sql = file_get_contents($schemaFile);
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
    }
    echo "  -> Schema created successfully.\n";
}

// 3. Import Seeders
$seederFile = __DIR__ . '/seeders.sql';
if (file_exists($seederFile)) {
    echo "[2/3] Seeding Initial Data...\n";
    $sql = file_get_contents($seederFile);
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
    }
    echo "  -> Seed data inserted successfully.\n";
}

echo "[3/3] Migration Complete!\n";
$conn->close();
?>
