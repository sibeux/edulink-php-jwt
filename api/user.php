<?php

include '../config.php';

$sql = "";
$method = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $method = $_POST['method'] ?? '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $method = $_GET['method'] ?? '';
} else{
    echo "Metode tidak dikenal.";
}

function getUserData($email, $db)
{
    $sql = "SELECT * FROM users WHERE email = '" . $email . "'";
    executeStatementSql($sql, $db);
}

function changeUserData($db)
{
    $name = $_POST['name']; 
    $photo = $_POST['photo']; 
    $email =  $_POST['email'];
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $address = $_POST['address'];

    if (
        $stmt = $db->prepare('UPDATE users 
            SET full_name = ?, user_photo = ?, birth_date = ?, gender = ?, city = ?, country = ?, address = ?
            WHERE email = ?;')
    ) {
        $stmt->bind_param(
            'ssssssss',
            $name,
            $photo,
            $birthday,
            $gender,
            $city,
            $country,
            $address,
            $email
        );

    // Attempt to execute the statement
    if ($stmt->execute()) {
        $response = ["status" => "success"];
        echo json_encode($response);
    } else {
        // stmt->execute() failed
        $response = [
            "status" => "failed",
            "error_message" => $stmt->error,
            "error_code" => $stmt->errno
        ];
        echo json_encode($response);
    }

    $stmt->close(); // Close the statement after execution (whether successful or not)
    } else {
        $response = ["status" => "failed"];
        echo json_encode($response);
    }
}

switch ($method) {
    case 'get_user_data':
        getUserData($_GET['email'], $db);
        break;
    case 'change_user_data':
        changeUserData($db);
        break;
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

// Close the dbection
$db->close();