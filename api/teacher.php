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