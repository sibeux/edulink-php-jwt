<?php
header("Content-Type: application/json");
include '../config.php';

$sql = "";
$method = '';
$teacherId = '';
$availability = '';
$price = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mendapatkan data JSON dari body request
    $input = file_get_contents('php://input');

    // Mengubah JSON menjadi array PHP
    $data = json_decode($input, true);

    // Cek apakah data berhasil di-decode
    if ($data === null) {
        // Jika gagal decode JSON, tangani error
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'Invalid JSON data']);
        exit;
    }
    
    $method = $data['method'] ?? '';
    $teacherId = $data['teacher_id'] ?? '';
    $availability = $data['availability'] ?? '';
    $price = $data['price'] ?? '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $method = $_GET['method'] ?? '';
} else {
    echo "Metode tidak dikenal.";
}

function getTeacherData($teacherId, $db)
{
    $sql = "SELECT
        t.teacher_id,
        t.about,
        t.skills,
        t.price,
        ta.id as availability_id,
        ta.available_day,
        ta.start_time,
        ta.end_time,
        ta.is_available
    FROM teacher t
    LEFT JOIN teacher_availability ta ON t.teacher_id = ta.teacher_id
    WHERE t.teacher_id = ?";

    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $teacherId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $teacher = null;
    $availabilities = [];

    while ($row = mysqli_fetch_assoc($result)) {
        if ($teacher === null) {
            $teacher = [
                "teacher_id" => $row["teacher_id"],
                "about" => $row["about"],
                "skills" => $row["skills"],
                "price" => $row["price"],
                "availabilities" => []
            ];
        }

        if (!empty($row["availability_id"])) {
            $availabilities[] = [
                "id" => $row["availability_id"],
                "available_day" => $row["available_day"],
                "start_time" => $row["start_time"],
                "end_time" => $row["end_time"],
                'is_available' => $row["is_available"]
            ];
        }
    }

    if ($teacher !== null) {
        $teacher["availabilities"] = $availabilities;
        echo json_encode($teacher);
    } else {
        echo json_encode([]);
    }
}

function updateTeacherProfile($db)
{
    global $teacherId, $availability, $price;

    if (empty($teacherId)) {
        http_response_code(400);
        echo json_encode(["error" => "teacher_id is required", "status" => "error"]);
        return;
    }

    if (!is_numeric($price)) {
        http_response_code(400);
        echo json_encode(["error" => "price must be a number", "status" => "error"]);
        return;
    }

    // 1. Update price in teacher_profile table
    $stmtPrice = $db->prepare("UPDATE teacher SET price = ? WHERE teacher_id = ?");
    $stmtPrice->bind_param("di", $price, $teacherId); // d = double, s = string
    $stmtPrice->execute();
    $stmtPrice->close();

    // 2. Update availability
    if (is_array($availability) && count($availability) > 0) {
        // Hapus data lama (opsional)
        $db->query("DELETE FROM teacher_availability WHERE teacher_id = '$teacherId'");

        $stmtAvail = $db->prepare("INSERT INTO teacher_availability (teacher_id, available_day, start_time, end_time, is_available) VALUES (?, ?, ?, ?, ?)");

        foreach ($availability as $item) {
            $day = $item['availableDay'] ?? '';
            $start_time = $item['startTime'] ?? '';
            $end_time = $item['endTime'] ?? '';
            $is_available = $item['isAvailable'] ?? 0;

            if ($day && $start_time && $end_time && isset($item['isAvailable'])) {
                $stmtAvail->bind_param("ssssi", $teacherId, $day, $start_time, $end_time, $is_available);
                if (!$stmtAvail->execute()) {
                    echo json_encode(["error" => "Failed to insert availability", "details" => $stmtAvail->error]);
                }
            }
        }
        $stmtAvail->close();
    }
    echo json_encode(["message" => "Teacher profile and availability updated successfully", "status" => "success"]);
}

function getExploreMentor($db)
{
    $sql = "SELECT
    u.full_name,
    t.teacher_id,
    t.price,
    GROUP_CONCAT(CONCAT(ta.available_day, ' ', ta.start_time, '-', ta.end_time) SEPARATOR ', ') AS schedule
FROM teacher t
LEFT JOIN teacher_availability ta ON t.teacher_id = ta.teacher_id
LEFT JOIN users u ON t.teacher_id = u.user_id
GROUP BY t.teacher_id, u.full_name, t.price;";

    executeStatementSql($sql, $db);
}   



switch ($method) {
    case 'get_teacher_data':
        getTeacherData($_GET['teacher_id'], $db);
        break;
    case 'update_teacher_profile':
        updateTeacherProfile( $db);
        break;
    case 'get_explore_mentor':
        getExploreMentor($db);
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