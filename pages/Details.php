<?php
session_start();
include_once __DIR__ . '/../db.php'; // adjust only if db.php is elsewhere


if (session_status() === PHP_SESSION_NONE) session_start();
include __DIR__ . '/navbar.php';


// validate id
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo "Invalid ID.";
    exit;
}

// helper: return array of column names for a table or empty if table missing
function get_table_columns(mysqli $conn, string $table): array {
    $cols = [];
    $res = $conn->query("SHOW TABLES LIKE '".$conn->real_escape_string($table)."'");
    if (!$res || $res->num_rows === 0) return [];
    $res->free();

    $q = $conn->query("SHOW COLUMNS FROM `".$conn->real_escape_string($table)."`");
    if (!$q) return [];
    while ($r = $q->fetch_assoc()) $cols[] = $r['Field'];
    $q->free();
    return $cols;
}

// helper: prepare & execute with single integer param, return assoc row or null
function fetch_one_prepared(mysqli $conn, string $sql, int $id) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

// candidate tables with expected id column and typical fields
$candidates = [
    ['table'=>'computer_club_events','id_col'=>'event_id','type'=>'event'],
    ['table'=>'social_service_events','id_col'=>'event_id','type'=>'event'],
    ['table'=>'activities','id_col'=>'id','type'=>'event'], // optional unified activities
    ['table'=>'users','id_col'=>'user_id','type'=>'member'],
];

// desired fields mapping (table column => normalized key)
$desired = [
    'event_id'   => 'id',
    'id'         => 'id',
    'title'      => 'title',
    'name'       => 'title', // users.name -> title
    'description'=> 'description',
    'body'       => 'description',
    'image'      => 'image',
    'meta_image' => 'image',
    'avatar'     => 'image',
    'location'   => 'location',
    'event_date' => 'event_date',
    'scheduled_at' => 'event_date',
    'event_time' => 'event_time',
    'status'     => 'status',
    'created_by' => 'created_by',
    'email'      => 'email',
    'created_at' => 'created_at',
];

// try candidates, build select only with existing columns to avoid unknown column error
$data = null;
$found = null;
foreach ($candidates as $cand) {
    $table = $cand['table'];
    $idcol = $cand['id_col'];
    $type = $cand['type'];

    $cols = get_table_columns($conn, $table);
    if (empty($cols)) continue;

    // ensure id column exists
    if (!in_array($idcol, $cols, true)) continue;

    // build select list: include id column plus any desired fields that exist
    $selectParts = ["`$idcol` AS _id"];
    foreach ($desired as $col => $alias) {
        if (in_array($col, $cols, true) && $col !== $idcol) {
            $selectParts[] = "`$col`";
        }
    }
    $select = implode(", ", $selectParts);

    // safe SQL and fetch
    $sql = "SELECT $select FROM `".$conn->real_escape_string($table)."` WHERE `".$conn->real_escape_string($idcol)."` = ? LIMIT 1";
    $row = fetch_one_prepared($conn, $sql, $id);
    if ($row) {
        // normalize row into common keys
        $normalized = [
            'id' => $row['_id'] ?? $id,
            'type' => $type,
            'title' => $row['title'] ?? $row['name'] ?? '',
            'description' => $row['description'] ?? $row['body'] ?? ($row['email'] ?? ''),
            'image' => $row['image'] ?? $row['meta_image'] ?? $row['avatar'] ?? '',
            'location' => $row['location'] ?? '',
            'event_date' => $row['event_date'] ?? $row['scheduled_at'] ?? $row['created_at'] ?? null,
            'event_time' => $row['event_time'] ?? null,
            'status' => $row['status'] ?? null,
            'created_by' => $row['created_by'] ?? null,
        ];
        $data = $normalized;
        $found = $table;
        break;
    }
}

// nothing found
if (!$data) {
    http_response_code(404);
    echo "<h2>Not found</h2><p>No record found for ID: ".htmlspecialchars($id)."</p>";
    exit;
}

// build display fields
$title = $data['title'] ?: 'Untitled';
$desc = $data['description'] ?: 'No description available.';
$image = $data['image'];
$location = $data['location'];
$datetime = $data['event_date'] ? ($data['event_date'] . ($data['event_time'] ? ' '.$data['event_time'] : '')) : '';
$statusRaw = $data['status'] ?? '';
$statusLabel = $statusRaw ? ucfirst($statusRaw) : 'Unknown';
$typeLabel = $data['type'] === 'event' ? 'Event' : 'Member';
$author = $data['created_by'] ?? 'System';


if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
$CSRF = $_SESSION['csrf_token'];


// Render UI (consistent with Admin_Dashboard.css)
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Details • <?php echo htmlspecialchars($title); ?></title>
  <link rel="stylesheet" href="../Admin_Dashboard.css">
  <style>
    body { background:#f6f7fb; font-family: Inter, Roboto, 'Segoe UI', Arial, sans-serif; color:#0f172a; }
    .details { max-width:980px; margin:32px auto; padding:22px; background:#fff; border-radius:12px; box-shadow:0 12px 36px rgba(12,15,20,.06); }
    .meta { color:#64748b; margin-bottom:12px; }
    .grid { display:grid; grid-template-columns:280px 1fr; gap:18px; align-items:start; }
    .thumb{ width:100%; height:220px; border-radius:10px; object-fit:cover; border:1px solid #e6eef8; }
    .placeholder{ background:#f8fafc;border:1px dashed #e6eef8;border-radius:10px;height:220px;display:grid;place-items:center;color:#94a3b8; }
    .tag{ padding:6px 10px;border-radius:999px;font-weight:700;font-size:13px;display:inline-block;margin-right:8px; }
    .tag-event{background:#e0f2fe;color:#1e3a8a}
    .tag-member{background:#fee2e2;color:#7f1d1d}
    .badge{padding:6px 10px;border-radius:10px;font-weight:700}
    .success{background:#dcfce7;color:#166534}
    .info{background:#e0f2fe;color:#075985}
    .warning{background:#fef9c3;color:#78350f}
    a.back{display:inline-block;margin-top:18px;color:#2563eb;text-decoration:none;font-weight:600}
    a.back:hover{text-decoration:underline}
    pre.desc{ white-space:pre-wrap; line-height:1.7; color:#374151; }
  </style>
</head>
<body>
  <div class="details">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <h2 style="margin:0;"><?php echo htmlspecialchars($title); ?></h2>
      <div>
        <span class="tag <?php echo $data['type']==='event' ? 'tag-event' : 'tag-member'; ?>"><?php echo $typeLabel; ?></span>
        <span class="badge <?php echo ($statusRaw==='published') ? 'success' : (($statusRaw==='scheduled') ? 'warning' : 'info'); ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
      </div>
    </div>

    <div class="meta">
      <?php if ($data['type'] === 'event'): ?>
        By: <?php echo htmlspecialchars($author); ?><?php if ($location) echo " • Location: ".htmlspecialchars($location); ?><?php if ($datetime) echo " • When: ".htmlspecialchars($datetime); ?>
      <?php else: ?>
        Member ID: <?php echo (int)$data['id']; ?><?php if ($datetime) echo " • Info: ".htmlspecialchars($datetime); ?>
      <?php endif; ?>
    </div>

    <div class="grid">
      <?php if ($image): ?>
        <img src="<?php echo htmlspecialchars($image); ?>" alt="Image" class="thumb">
      <?php else: ?>
        <div class="placeholder">No image available</div>
      <?php endif; ?>

      <div>
        <pre class="desc"><?php echo htmlspecialchars($desc); ?></pre>

        <?php if ($data['type'] === 'event' && $location): ?>
          <h4 style="margin-top:18px">Location</h4>
          <p style="margin-top:6px;"><?php echo htmlspecialchars($location); ?></p>
        <?php endif; ?>

        <a class="back" href="javascript:history.back()">← Back to dashboard</a>
      </div>
    </div>
  </div>
</body>
</html>
