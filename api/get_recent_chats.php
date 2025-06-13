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

$sql = "SELECT 
    recent_chats.peer_id, 
    recent_chats.last_message, 
    recent_chats.updated_at, 
    users.full_name, 
    users.user_photo
FROM recent_chats
LEFT JOIN users ON users.user_id = recent_chats.peer_id
WHERE recent_chats.user_id = ?
ORDER BY recent_chats.updated_at DESC;";

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