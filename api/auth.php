<?php

include '././auth.php';

function getEmailCheck($db)
{
    if ($stmt = $db->prepare('SELECT users.email FROM users WHERE users.email = ?')) {
        // Bind parameters (s = string, i = int, b = blob, etc)
        $stmt->bind_param('s', $_POST['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $response = ["email_exists" => "true"];
            // Output the JSON data
            echo json_encode($response);
        } else {
            $response = ["email_exists" => "false"];
            echo json_encode($response);
        }
        $stmt->close();
    } else {
        echo 'Could not prepare statement!';
    }
}

function createUser($db)
{
    if (
        $stmt = $db->prepare('INSERT INTO `users` (`user_id`, `email`, `full_name`, `password_hash`, `phone_number`, `user_photo`, `user_actor`) 
        VALUES (NULL, ?, ?, ?, ? NULL, ?);')
    ) {
        // encrypt the password
        $email = $_POST['email'];
        $name = $_POST['full_name'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $phone = $_POST['phone_number'];
        $actor = $_POST['user_actor'];

        // hati-hati sama koma di bind_param terakhir, njir.
        $stmt->bind_param('sssss', $email, $name, $password, $phone, $actor);
        $stmt->execute();

        // registration successful
        $response = ["status" => "success"];
        echo json_encode($response);
    } else {
        $response = ["status" => "failed"];
        echo json_encode($response);
        echo 'Could not prepare statement!';
    }
}

switch ($_POST['method']) {
    case 'email_check':
        getEmailCheck($db);
        break;
    case 'create_user':
        createUser($db);
        break;
    default:
        break;
}

$db->close();