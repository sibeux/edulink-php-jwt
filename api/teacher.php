<?php

include '../config.php';

$sql = "";
$method = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['method'] ?? '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $method = $_GET['method'] ?? '';
} else {
    echo "Metode tidak dikenal.";
}

function getTeacherData($teacherId, $db)
{
    $sql = "SELECT
    -- Pilih kolom teacher_id hanya dari tabel utama (teacher)
    teacher.teacher_id,
    
    -- Pilih kolom lain yang Anda butuhkan dari tabel teacher
    teacher.about,
    teacher.skills,
    teacher.price,
    
    -- Pilih kolom yang relevan dari tabel availability (ta)
    ta.id as availability_id, -- Beri alias agar tidak bentrok jika ada nama 'id' di tabel teacher
    ta.available_date,
    ta.start_time,
    ta.end_time
    
FROM
    teacher
LEFT JOIN
    teacher_availability as ta ON teacher.teacher_id = ta.teacher_id
WHERE
    teacher.teacher_id = '$teacherId';";
    executeStatementSql($sql, $db);
}

switch ($method) {
    case 'get_teacher_data':
        getTeacherData($_GET['teacher_id'], $db);
        break;
    default:
        break;
}

function executeStatementSql($sql, $db)
{
    $result = $db->query($sql);

    // Check if the query was successful
    if (!$result) {
        die("Query failed: " . (is_object($db) ? $db->error : 'Database connection error'));
    }

    // Create an array to store the data
    $data = array();

    // Check if there is any data
    if ($result->num_rows > 0) {
        // Loop through each row of data
        while ($row = $result->fetch_assoc()) {
            // Clean up the data to handle special characters
            array_walk_recursive($row, function (&$item) {
                if (is_string($item)) {
                    $item = htmlentities($item, ENT_QUOTES, 'UTF-8');
                }
            });

            // Add each row to the data array
            $data[] = $row;
        }
    }

    // Convert the data array to JSON format
    $json_data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Check if JSON conversion was successful
    if ($json_data === false) {
        die("JSON encoding failed");
    }

    // Output the JSON data
    echo $json_data;
}

// Close the dbection
$db->close();