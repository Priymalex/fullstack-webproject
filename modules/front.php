<?php
require_once(__DIR__ . '/../scripts/validator.php');

function front_get($request) {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $errors = [];
    if (!empty($_COOKIE['errors'])) {
        $errors = json_decode($_COOKIE['errors'], true);
        setcookie('errors', '', time() - 3600, '/');
    }

    $values = [];
    if (!empty($_COOKIE['values'])) {
        $values = json_decode($_COOKIE['values'], true);
        setcookie('values', '', time() - 3600, '/');
    } elseif (!empty($_SESSION['login']) && !empty($_SESSION['user_id'])) {
        require_once(__DIR__ . '/../scripts/db.php');
        try {
            $stmt = $db->prepare("SELECT firstName, telephone, email, agreed FROM Ordering WHERE order_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $dbUser = $stmt->fetch();
            
            if ($dbUser) {
                $values['firstName'] = $dbUser['firstName'];
                $values['phone'] = $dbUser['telephone'];
                $values['email'] = $dbUser['email'];
                $values['agreed'] = $dbUser['agreed'];

                $roomsStmt = $db->prepare("SELECT r.room_name FROM Connection2 c JOIN Rooms r ON c.room_id = r.rooms_id WHERE c.order_id=?");
                $roomsStmt->execute([$_SESSION['user_id']]);
                $selectedRooms = $roomsStmt->fetchAll(PDO::FETCH_COLUMN);

                if(!empty($selectedRooms)){$values['rooms'] = $selectedRooms;
                }
            }
        } catch (PDOException $e) {
            error_log("Ошибка автозаполнения: " . $e->getMessage());
        }
    }

    $success = $_COOKIE['success'] ?? false;
    if ($success) {
        setcookie('success', '', time() - 3600, '/');
    }

    $template_data = array(
        'errors'    => $errors,
        'values'    => $values,
        'success'   => $success,
        'gen_login' => $_COOKIE['login'] ?? '',
        'gen_pass'  => $_COOKIE['pass'] ?? ''
    );

    if ($template_data['gen_login']) setcookie('login', '', time() - 3600, '/');
    if ($template_data['gen_pass']) setcookie('pass', '', time() - 3600, '/');

    return array(
        'headers' => array('Content-Type' => 'text/html; charset=utf-8'),
        'entity'  => theme('page', $template_data)
    );
}

function front_post($request) {
    return process_form_submission($request, 'register');
}

function front_put($request) {
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        die(json_encode(['status' => 'error', 'errors' => ['auth' => 'Требуется авторизация']]));
    }
    return process_form_submission($request, 'update', $request['user_id']);
}

function process_form_submission($request, $mode, $targetUserId = null) {
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $data = !empty($request['put']) ? $request['put'] : $request['post'];

    if (!isset($data['csrf_token']) || $data['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        die(json_encode(['status' => 'error', 'errors' => ['csrf' => 'Ошибка безопасности']]));
    }

    $errors = validate_form_array($data);
    if (!empty($errors)) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            die(json_encode(['status' => 'error', 'errors' => $errors]));
        }

        setcookie('errors', json_encode($errors), 0, '/');
        return redirect('/fullstack-webproject/');
    }

    require(__DIR__ . '/../scripts/db.php');
    
    $generatedLogin = '';
    $generatedPass = '';

    try {
        $db->beginTransaction();
        if ($mode === 'register') {
            $generatedLogin = 'u' . substr(bin2hex(random_bytes(4)), 0, 6);
            $generatedPass = substr(bin2hex(random_bytes(4)), 0, 8);
            $hash = password_hash($generatedPass, PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO Ordering 
                (firstName, telephone, email, agreed) 
                VALUES (?, ?, ?, ?)");
                
            $stmt->execute([
                $data['firstName'], 
                $data['telephone'], 
                $data['email'], 
                isset($data['agreement']) ? 1 : 0    
            ]);
            
            $orderId=$db->lastInsertId();

            if(!empty($data['roomType']) && is_array($data['roomType'])){
                $roomStmt = $db->prepare("INSERT INTO Connection2 (order_id, room_id) VALUES(?,?)");
                foreach($data['roomType'] as $roomName){
                    $roomIdStmt=$db->prepare("SELECT rooms_id FROM Rooms WHERE room_name = ?");
                    $roomIdStmt->execute([$roomName]);
                    $room = $roomIdStmt->fetch();
                    if($room){
                        $roomStmt->execute([$orderId,$room['rooms_id']]);
                    }
                }
               
            }
            $userStmt = $db->prepare("INSERT INTO Users(order_id,login,password_hash) VALUES(?,?,?)");
            $userStmt->execute([$orderId,$generatedLogin,$hash]);

            $_SESSION['user_id'] = $orderId;
            $_SESSION['login'] = $generatedLogin;
        } elseif ($mode === 'update' && $targetUserId) {
	    $stmt = $db->prepare("UPDATE Ordering SET firstName = ?, telephone = ?, email = ? WHERE order_id = ?");
            $stmt->execute([
                $data['firstName'], 
                $data['telephone'], 
                $data['email'], 
                $targetUserId
            ]);

            $deleteStmt = $db->prepare("DELETE FROM Connection2 WHERE order_id = ?");
            $deleteStmt->execute([$targetUserId]);

            if (!empty($data['roomType']) && is_array($data['roomType'])) {
                $roomStmt = $db->prepare("INSERT INTO Connection2 (order_id, room_id) VALUES (?, ?)");
                foreach ($data['roomType'] as $roomName) {
                    $roomIdStmt = $db->prepare("SELECT rooms_id FROM Rooms WHERE room_name = ?");
                    $roomIdStmt->execute([$roomName]);
                    $room = $roomIdStmt->fetch();
                    if ($room) {
                        $roomStmt->execute([$targetUserId, $room['rooms_id']]);
             }
         }
        }
        }

        $db->commit();
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        if ($is_ajax) {
            die(json_encode(['status' => 'error', 'errors' => ['db' => $e->getMessage()]]));
        }

        return redirect('/fullstack-webproject/');
    }

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success', 
            'mode' => $mode,
            'login' => $generatedLogin,
            'password' => $generatedPass
        ]);
        exit; 
    }
    
    setcookie('success', '1', 0, '/');
    return redirect('/fullstack-webproject/');
}
