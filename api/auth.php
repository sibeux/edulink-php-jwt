<?php

include '../config.php';

function getEmailPhoneCheck($db)
{
    if ($stmt = $db->prepare('SELECT email, phone_number FROM users WHERE email = ? OR phone_number = ?')) {
        $stmt->bind_param('ss', $_POST['email'], $_POST['phone']);
        $stmt->execute();
        
        $result = $stmt->get_result(); // ✅ ini bisa dipakai fetch_assoc()
    
        $emailDuplicate = false;
        $phoneDuplicate = false;
    
        while ($row = $result->fetch_assoc()) {
            if ($row['email'] === $_POST['email']) {
                $emailDuplicate = true;
            }
            if ($row['phone_number'] === $_POST['phone']) {
                $phoneDuplicate = true;
            }
        }
    
        echo json_encode([
            "email_exists" => $emailDuplicate ? 'true' : 'false',
            "phone_exists" => $phoneDuplicate ? 'true' : 'false'
        ]);
        
        $stmt->close();
    } else {
        echo 'Could not prepare statement!';
    }
}

function createUser($db)
{
    if (
        $stmt = $db->prepare('INSERT INTO `users` 
        (`user_id`, `full_name`, `email`, `password_hash`, `phone_number`, `user_photo`, `user_actor`, `gender`, `birth_date`, `city`, `country`, `address`, `courses`) 
        VALUES (NULL, ?, ?, ?, ?, NULL, ?, NULL, NULL, NULL, NULL, NULL, NULL);')
    ) {
        // Ambil data dari form
        $email = $_POST['email'];
        $name = $_POST['full_name'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $phone = $_POST['phone_number'];
        $actor = $_POST['user_actor'];

        // Bind dan eksekusi query
        $stmt->bind_param('sssss', $name, $email, $password, $phone, $actor);
        $stmt->execute();

        // Ambil user_id terbaru
        $new_user_id = $db->insert_id;

        // Kalau actor = tutor, insert ke table teacher
        if ($actor === 'tutor') {
            $teacher_stmt = $db->prepare('INSERT INTO `teacher` (`teacher_id`) VALUES (?)');
            if ($teacher_stmt) {
                $teacher_stmt->bind_param('i', $new_user_id);
                $teacher_stmt->execute();
            }
        }

        // Kirim respons
        $response = ["status" => "success", "user_id" => $new_user_id];
        echo json_encode($response);

    } else {
        $response = ["status" => "failed", "error" => $db->error];
        echo json_encode($response);
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