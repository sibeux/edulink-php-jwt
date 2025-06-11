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
    // 1. Siapkan query
    $sql = "SELECT
                t.teacher_id, t.about, t.skills, t.price,
                ta.id as availability_id, ta.available_date, ta.start_time, ta.end_time
            FROM teacher t
            LEFT JOIN teacher_availability ta ON t.teacher_id = ta.teacher_id
            WHERE t.teacher_id = ?";

    // 2. Buat prepared statement
    $stmt = mysqli_prepare($db, $sql);

    // 3. Ikat parameter
    mysqli_stmt_bind_param($stmt, "i", $teacherId);

    // 4. Eksekusi statement
    mysqli_stmt_execute($stmt);

    // 5. Ikat variabel hasil (bind result variables)
    mysqli_stmt_bind_result(
        $stmt,
        $res_teacher_id,
        $res_about,
        $res_skills,
        $res_price,
        $res_availability_id,
        $res_available_date,
        $res_start_time,
        $res_end_time
    );

    $teacher = null;
    $availabilities = [];

    // 6. Fetch hasilnya satu per satu
    while (mysqli_stmt_fetch($stmt)) {
        if ($teacher === null) {
            $teacher = [
                "teacher_id" => $res_teacher_id,
                "about" => $res_about,
                "skills" => $res_skills,
                "price" => $res_price,
                "availabilities" => [] // Initialize here
            ];
        }

        if (!empty($res_availability_id)) {
            $availabilities[] = [
                "id" => $res_availability_id,
                "available_date" => $res_available_date,
                "start_time" => $res_start_time,
                "end_time" => $res_end_time
            ];
        }
    }

    if ($teacher !== null) {
        $teacher["availabilities"] = $availabilities;
        echo json_encode($teacher);
    } else {
        // Jika tidak ada guru yang ditemukan sama sekali
        echo json_encode([]);
    }

    // 7. Tutup statement
    mysqli_stmt_close($stmt);
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