<?php

include '../config.php';

function getEmailPhoneCheck($db)
{
    if (
        $stmt = $db->prepare('SELECT users.email, users.phone_number 
    FROM users 
    WHERE users.email = ? OR users.phone_number = ?;')
    ) {
        // Bind parameters (s = string, i = int, b = blob, etc)
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $stmt->bind_param('s', $_POST['email']);
        $stmt->bind_param('s', $_POST['phone']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $emailDuplicate = false;
            $phoneDuplicate = false;

            while($row = $stmt->fetch_assoc()) {
                if ($row['email'] === $email) {
                    $emailDuplicate = true;
                }
                if ($row['phone_number'] === $phone) {
                    $phoneDuplicate = true;
                }
            }

            $response = ["email_exists" => $emailDuplicate ? 'true' : 'false', "phone_exists" => $phoneDuplicate ? 'true' : 'false'];
            // Output the JSON data
            echo json_encode($response);
        } else {
            $response = ["email_exists" => "false", "phone_exists" => "false"];
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
        $stmt = $db->prepare('INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `phone_number`, `user_photo`, `user_actor`, `gender`, `birth_date`, `city`, `country`, `address`, `courses`) 
        VALUES (NULL, ?, ?, ?, ?, NULL, ?, NULL, NULL, NULL, NULL, NULL, NULL);')
    ) {
        // encrypt the password
        $email = $_POST['email'];
        $name = $_POST['full_name'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $phone = $_POST['phone_number'];
        $actor = $_POST['user_actor'];

        // hati-hati sama koma di bind_param terakhir, njir.
        $stmt->bind_param('sssss', $name, $email, $password, $phone, $actor);
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
    case 'email_phone_check':
        getEmailPhoneCheck($db);
        break;
    case 'create_user':
        createUser($db);
        break;
    default:
        break;
}

$db->close();