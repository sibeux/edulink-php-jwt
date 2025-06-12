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

function createBooking($db)
{
    if (
        $stmt = $db->prepare('INSERT INTO `bookings` 
            (`student_id`, `teacher_id`, `booking_status`, `booking_day`, `booking_time`, `booking_duration`, `booking_price`) 
            VALUES (?, ?, ?, ?, ?, ?, ?)')
    ) {
        $student_id = $_POST['student_id'];
        $teacher_id = $_POST['teacher_id'];
        $booking_status = 'ongoing'; // default status
        $booking_day = $_POST['booking_day'];
        $booking_time = $_POST['booking_time'];
        $booking_duration = $_POST['booking_duration'];
        $booking_price = $_POST['booking_price'];

        $stmt->bind_param(
            'iisssii',
            $student_id,
            $teacher_id,
            $booking_status,
            $booking_day,
            $booking_time,
            $booking_duration,
            $booking_price
        );

        if ($stmt->execute()) {
            $response = [
                "status" => "success",
                "booking_id" => $stmt->insert_id
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "Failed to execute the query.",
                "error" => $stmt->error
            ];
        }

        $stmt->close();
        echo json_encode($response);
    } else {
        $response = [
            "status" => "failed",
            "message" => "Could not prepare statement!"
        ];
        echo json_encode($response);
    }
}

function getBookingsByStudent($db, $student_id)
{
    if ($stmt = $db->prepare("SELECT bookings.*, 
users.full_name as teacher_name, users.user_photo as teacher_photo
FROM bookings 
LEFT JOIN users on users.user_id = bookings.teacher_id
WHERE student_id = ? ORDER BY id_booking DESC;")) {
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $bookings = [];

        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }

        if (count($bookings) > 0) {
            $response = [
                "status" => "success",
                "data" => $bookings
            ];
        } else {
            $response = [
                "status" => "success",
                "data" => [],
                "message" => "No bookings found for this student."
            ];
        }

        $stmt->close();
    } else {
        $response = [
            "status" => "error",
            "message" => "Failed to prepare statement.",
            "error" => $db->error
        ];
    }

    echo json_encode($response);
}

switch ($method) {
    case 'create_booking':
        createBooking($db);
        break;
    case 'get_bookings_by_student':
        getBookingsByStudent($db, $_GET['student_id'] ?? 0);
    default:
        break;
}

function executeStatementSql($sql, $db) {
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

$db->close();