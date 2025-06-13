<?php
// recent_chat.php
include '../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

$input = json_decode(file_get_contents("php://input"), true);

$user_id = $input['user_id'];
$peer_id = $input['peer_id'];
$last_message = $input['last_message'];

// Insert or update recent chat
$sql = "INSERT INTO recent_chats (user_id, peer_id, last_message, updated_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE last_message = VALUES(last_message), updated_at = NOW()";

$stmt = $db->prepare($sql);
$stmt->bind_param("iis", $user_id, $peer_id, $last_message);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $stmt->error]);
}

$db->close();