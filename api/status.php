<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'Unauthorized']);
  exit;
}
include_once __DIR__ . '/../db.php';

$payload = json_decode(file_get_contents('php://input'), true);
$token = $payload['csrf'] ?? '';
if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
  echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
  exit;
}

$id = isset($payload['id']) ? intval($payload['id']) : 0;
$table = $payload['table'] ?? '';
$status = $payload['status'] ?? '';
if ($id <= 0 || !$table || !$status) { echo json_encode(['success' => false, 'error' => 'Invalid input']); exit; }

// whitelist
$whitelist = [
  'computer_club_events' => ['idcol'=>'event_id','status_col'=>'status'],
  'social_service_events' => ['idcol'=>'event_id','status_col'=>'status'],
  'users' => ['idcol'=>'user_id','status_col'=>'status'],
];

if (!isset($whitelist[$table])) { echo json_encode(['success' => false, 'error' => 'Table not allowed']); exit; }
$idcol = $whitelist[$table]['idcol'];
$status_col = $whitelist[$table]['status_col'];

// allowed statuses
$allowed = ['published','scheduled','draft','active','inactive'];
if (!in_array($status, $allowed, true)) { echo json_encode(['success' => false, 'error' => 'Invalid status']); exit; }

// ensure record exists
$stmt = $conn->prepare("SELECT 1 FROM `$table` WHERE `$idcol` = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) { echo json_encode(['success' => false, 'error' => 'Record not found']); exit; }
$stmt->close();

// update
$stmt = $conn->prepare("UPDATE `$table` SET `$status_col` = ? WHERE `$idcol` = ?");
$stmt->bind_param('si', $status, $id);
$ok = $stmt->execute();
$stmt->close();
echo json_encode(['success' => $ok]);


?>