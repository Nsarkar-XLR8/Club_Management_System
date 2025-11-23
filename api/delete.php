<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'Unauthorized']);
  exit;
}
include_once __DIR__ . '/../db.php';

// basic CSRF check
$payload = json_decode(file_get_contents('php://input'), true);
$token = $payload['csrf'] ?? '';
if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
  echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
  exit;
}

$id = isset($payload['id']) ? intval($payload['id']) : 0;
$table = $payload['table'] ?? 'activities';
if ($id <= 0) { echo json_encode(['success' => false, 'error' => 'Invalid ID']); exit; }

// whitelist tables and id columns your admin may delete from
$whitelist = [
  'computer_club_events' => 'event_id',
  'social_service_events' => 'event_id',
  'users' => 'user_id',
  // add other tables if needed
];
if (!array_key_exists($table, $whitelist)) {
  echo json_encode(['success' => false, 'error' => 'Table not allowed']);
  exit;
}
$idcol = $whitelist[$table];

// check record exists
$stmt = $conn->prepare("SELECT 1 FROM `$table` WHERE `$idcol` = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
  echo json_encode(['success' => false, 'error' => 'Record not found']);
  exit;
}
$stmt->close();

// perform delete (use transaction)
$conn->begin_transaction();
$stmt = $conn->prepare("DELETE FROM `$table` WHERE `$idcol` = ? LIMIT 1");
$stmt->bind_param('i', $id);
$ok = $stmt->execute();
if ($ok) {
  $conn->commit();
  echo json_encode(['success' => true]);
} else {
  $conn->rollback();
  echo json_encode(['success' => false, 'error' => 'Delete failed']);
}
$stmt->close();
