<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../scripts/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === АВТОРИЗАЦИЯ ===
$auth_success = false;

if (empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
    if (preg_header('/Basic\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
        list($user, $pw) = explode(':', base64_decode($matches[1]), 2);
        $_SERVER['PHP_AUTH_USER'] = $user;
        $_SERVER['PHP_AUTH_PW'] = $pw;
    }
}

if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
    try {
        $stmt = $db->prepare("SELECT password_hash FROM Admin2 WHERE login = ?");
        $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
            $auth_success = true;
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        exit('Ошибка авторизации. Проверьте подключение к БД.');
    }
}

if (!$auth_success) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    print('<h1>401 Требуется авторизация</h1>');
    exit();
}

// === ПОЛУЧЕНИЕ ID ===
$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    die("ID заявки не указан");
}

// === ОБРАБОТКА ФОРМЫ РЕДАКТИРОВАНИЯ ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('HTTP/1.1 403 Forbidden');
        exit('Ошибка безопасности: CSRF-токен невалиден');
    }
    
    try {
       $db->beginTransaction();
        
        // 1. Обновление Ordering
        $stmt = $db->prepare("UPDATE Ordering SET firstName = ?, telephone = ?, email = ?, agreed = ? WHERE order_id = ?");
        $stmt->execute([
            $_POST['first_name'], 
            $_POST['phone'], 
            $_POST['email'],
            isset($_POST['agreed']) ? 1 : 0,
            $order_id
        ]);
        
        // 2. Обновление логина в Users (если изменили)
        if (!empty($_POST['login'])) {
            $loginStmt = $db->prepare("UPDATE Users SET login = ? WHERE order_id = ?");
            $loginStmt->execute([$_POST['login'], $order_id]);
        }
        
        // 3. Обновление выбранных типов помещений (удаляем старые, вставляем новые)
        $deleteStmt = $db->prepare("DELETE FROM Connection2 WHERE order_id = ?");
        $deleteStmt->execute([$order_id]);
        
        if (!empty($_POST['roomType']) && is_array($_POST['roomType'])) {
            $roomStmt = $db->prepare("INSERT INTO Connection2 (order_id, room_id) VALUES (?, ?)");
            foreach ($_POST['roomType'] as $roomName) {
                $roomIdStmt = $db->prepare("SELECT rooms_id FROM Rooms WHERE room_name = ?");
                $roomIdStmt->execute([$roomName]);
                $room = $roomIdStmt->fetch();
                if ($room) {
                    $roomStmt->execute([$order_id, $room['rooms_id']]);
                }
            }
        }
        
        $db->commit();
        
        header('Location: /fullstack-webproject/modules/admin.php?success=edited');
        exit;
        
    }  catch (PDOException $e) {
        error_log($e->getMessage());
        exit('Ошибка при сохранении данных. Попробуйте позже.');
    }
}

// === ЗАГРУЗКА ТЕКУЩИХ ДАННЫХ ===
try {
    // 1. Загружаем данные из Ordering
    $stmt = $db->prepare("SELECT * FROM Ordering WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        die("Заявка не найдена.");
    }
    
    // 2. Загружаем логин из Users
    $userStmt = $db->prepare("SELECT login FROM Users WHERE order_id = ?");
    $userStmt->execute([$order_id]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC


);
    
    // 3. Загружаем выбранные типы помещений
    $roomsStmt = $db->prepare("
        SELECT r.room_name 
        FROM Connection2 c
        JOIN Rooms r ON c.room_id = r.rooms_id
        WHERE c.order_id = ?
    ");
    $roomsStmt->execute([$order_id]);
    $selectedRooms = $roomsStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 4. Загружаем все возможные типы помещений для чекбоксов
    $allRoomsStmt = $db->prepare("SELECT room_name FROM Rooms ORDER BY rooms_id");
    $allRoomsStmt->execute();
    $allRooms = $allRoomsStmt->fetchAll(PDO::FETCH_COLUMN);
    
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Ошибка загрузки данных: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование заявки #<?= (int)$order_id ?></title>
    <style>
        .form-edit { max-width: 600px; margin: 40px auto; font-family: sans-serif; padding: 20px; border: 1px solid #ccc; border-radius: 8px; background: #f9f9f9; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="tel"], select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        select[multiple] { height: 120px; }
        .checkbox-group { margin-top: 5px; }
        .checkbox-group label { display: inline-block; margin-right: 15px; font-weight: normal; }
        .btn-submit { background: #337ab7; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn-submit:hover { background: #286090; }
        .cancel-link { margin-left: 15px; color: #d9534f; text-decoration: none; }
        .cancel-link:hover { text-decoration: underline; }
        .room-section { margin-top: 10px; padding: 10px; background: #fff; border-radius: 4px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="form-edit">
        <h2>Редактирование заявки #<?= (int)$order_id ?></h2>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
            <div class="form-group">
                <label>Имя:</label>
                <input name="first_name" type="text" value="<?= htmlspecialchars($order['firstName'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Телефон:</label>
                <input name="phone" type="tel" value="<?= htmlspecialchars($order['telephone'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input name="email" type="email" value="<?= htmlspecialchars($order['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Авто-Логин:</label>
                <input name="login" type="text" value="<?= htmlspecialchars($user['login'] ?? '') ?>">
                <small style="color: #666;">Оставьте пустым, чтобы не менять</small>
            </div>
            
            <div class="form-group">
                <label>Тип помещения (можно выбрать несколько):</label>
                <div class="room-section">
                    <select name="roomType[]" multiple size="5">
                        <?php foreach ($allRooms as $room): ?>
                            <option value="<?= htmlspecialchars($room) ?>" 
                                <?= in_array($room, $selectedRooms) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($room) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>


<div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="agreed" value="1" <?= $order['agreed'] ? 'checked' : '' ?>>
                    Согласен с политикой обработки персональных данных
                </label>
            </div>
            
            <button type="submit" class="btn-submit">Сохранить изменения</button>
            <a href="/fullstack-webproject/modules/admin.php" class="cancel-link">Отмена</a>
        </form>
    </div>
</body>
</html>