<?php
// get_recent_chats.php
include '../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['error' => 'Missing user_id']);
    exit;
}

$sql = "WITH ranked_chats AS (
  SELECT 
    rc.*,
    ROW_NUMBER() OVER (PARTITION BY rc.peer_id ORDER BY rc.updated_at DESC) as rn
  FROM recent_chats rc
  WHERE rc.user_id = ?
)
SELECT 
  rc.peer_id,
  rc.last_message,
  rc.updated_at,
  u.full_name,
  u.user_photo
FROM ranked_chats rc
JOIN users u ON u.user_id = rc.peer_id
WHERE rc.rn = 1
ORDER BY rc.updated_at DESC;;";

$stmt = $db->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$chats = [];

while ($row = $result->fetch_assoc()) {
    $chats[] = $row;
}

echo json_encode($chats);

$db->close();