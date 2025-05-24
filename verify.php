<?php
require 'vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// Kunci rahasia untuk memverifikasi JWT
$secretKey = 'your_secret_key';

// Ambil token dari header Authorization
$headers = apache_request_headers();

if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);  // Hapus 'Bearer ' dari header

    try {
        // Dekode token JWT
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

        // Cek apakah token sudah kedaluwarsa
        $now = time();
        $exp = $decoded->exp;

        if ($exp < $now) {
            echo json_encode(['login' => 'sukses', 'exp' => 'yes']);  // Token kadaluwarsa
        } else {
            echo json_encode(['login' => 'sukses', 'exp' => 'no']);   // Token valid
        }
    } catch (Exception $e) {
        // Jika terjadi error (token tidak valid)
        echo json_encode(['login' => 'gagal', 'exp' => 'yes']);
    }
} else {
    // Jika tidak ada header Authorization
    echo json_encode(['login' => 'gagal', 'message' => 'Authorization header missing']);
}