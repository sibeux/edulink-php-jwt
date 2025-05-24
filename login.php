<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;

include 'config.php';

$key = "your_secret_key"; // Ganti dengan kunci rahasia
$issuedAt = time();
$expirationTime = $issuedAt + (3600 * 24 * 7); // token berlaku selama 7 hari
$issuer = "https://sibeux.my.id";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $actor = $_POST['user_actor'];

    // Cek pengguna di database
    if ($stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND user_actor = ?")) {
        
        $stmt->bind_param('ss', $email, $actor);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {
            $payload = [
                'iat' => $issuedAt,
                'exp' => $expirationTime,
                'iss' => $issuer,
                'data' => [
                    'id' => $user['user_id'],
                    'email' => $user['email'],
                    'actor' => $user['user_actor'],
                ],
            ];

            $jwt = JWT::encode($payload, $key, 'HS256');
            echo json_encode(['token' => $jwt]);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid credentials']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Internal server error']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
}