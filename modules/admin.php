<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once DIR . '/../scripts/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Генерация CSRF токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// === АВТОРИЗАЦИЯ ЧЕРЕЗ Admin2 ===
$auth_success = false;

// Поддержка авторизации через Basic Auth
if (empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
    if (preg_match('/Basic\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
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
            $_SESSION['admin_login'] = $_SERVER['PHP_AUTH_USER'];
        }
    } catch (PDOException $e) {
        error_log('Admin auth error: ' . $e->getMessage());
        exit('Ошибка авторизации. Проверьте подключение к БД.');
    }
}

if (!$auth_success) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    print('<h1>401 Требуется авторизация</h1>');
    print('<p>Доступ разрешен только администраторам</p>');
    exit();
}

// === ОБРАБОТКА УДАЛЕНИЯ ЗАЯВКИ ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('HTTP/1.1 403 Forbidden');
        exit('Ошибка безопасности: CSRF-токен невалиден');
    }

    $order_id = (int)$_POST['delete_id'];
    
    try {
        $db->beginTransaction();
        
        // Удаляем связи с комнатами
        $stmt = $db->prepare("DELETE FROM Connection2 WHERE order_id = ?");
        $stmt->execute([$order_id]);
        
        // Удаляем запись из Users
        $stmt = $db->prepare("DELETE FROM Users WHERE order_id = ?");
        $stmt->execute([$order_id]);
        
        // Удаляем заявку из Ordering
        $stmt = $db->prepare("DELETE FROM Ordering WHERE order_id = ?");
        $stmt->execute([$order_id]);
        
        $db->commit();
        
        header('Location: /fullstack-webproject/modules/admin.php?success=deleted');
        exit();
    } catch (PDOException $e) {
        $db->rollBack();
        error_log($e->getMessage());
        $error = 'Ошибка удаления: ' . $e->getMessage();
    }
}

// === ПОЛУЧЕНИЕ ДАННЫХ ДЛЯ ТАБЛИЦЫ ===
try {
    $stmt = $db->prepare("
        SELECT 
            o.order_id,
            o.firstName,
            o.telephone,
            o.email,
            o.agreed,
            u.login,
            GROUP_CONCAT(DISTINCT r.room_name SEPARATOR ', ') as rooms
        FROM Ordering o
        LEFT JOIN Users u ON o.order_id = u.order_id
        LEFT JOIN Connection2 c ON o.order_id = c.order_id
        LEFT JOIN Rooms r ON c.room_id = r.rooms_id
        GROUP BY o.order_id, o.firstName, o.telephone, o.email, o.agreed, u.login
        ORDER BY o.order_id DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'Ошибка загрузки данных: ' . $e->getMessage();
    $orders = [];
}

// === СТАТИСТИКА ПО КОМНАТАМ ===
try {
    $stmt = $db->prepare("
        SELECT 
            r.room_name,
            COUNT(c.order_id) as order_count FROM Rooms r
        LEFT JOIN Connection2 c ON r.rooms_id = c.room_id
        GROUP BY r.rooms_id, r.room_name
        ORDER BY order_count DESC
    ");
    $stmt->execute();
    $room_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    $room_stats = [];
}

$total_orders = count($orders);

// Сообщение об успехе
$message = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'deleted') $message = 'Заявка успешно удалена';
    if ($_GET['success'] == 'edited') $message = 'Заявка успешно обновлена';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель - Управление заявками</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .stats-box {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }
        .stat-card {
            background: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card strong {
            font-size: 24px;
            color: #4CAF50;
            display: block;
        }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }
        .btn {
            display: inline-block;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
            transition: opacity 0.3s;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .btn-edit {
            background: #2196F3;
            color: white;
        }
        .btn-delete {
            background: #f44336;
            color: white;
        }
        .btn-link {
            background: none;
            border: none;
            color: #f44336;
            text-decoration: underline;
            cursor: pointer;
            padding: 0;
            font-size: 12px;
        }
        .btn-link:hover {
            color: #c9302c;
        }
        .edit-link {
            color: #2196F3;
            text-decoration: none;
        }
        .edit-link:hover {
            text-decoration: underline;
        }
        .badge {
            background: #4CAF50;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            display: inline-block;
            margin: 2px;
        }
        .agree-yes {
            color: green;
            font-weight: bold;
        }
        .agree-no {
            color: red;
        }
        @media (max-width: 768px) {
            th, td {
                font-size: 12px;
                padding: 8px;
            }
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👑 Административная панель</h1>
        <p>Вы авторизованы как <strong><?= htmlspecialchars($_SESSION['admin_login'] ?? $_SERVER['PHP_AUTH_USER']) ?></strong></p>
        
        <?php if ($message): ?>
            <div class="message">✅ <?= $message ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error">❌ <?= $error ?></div>
        <?php endif; ?>
        
        <!-- Блок статистики -->
        <div class="stats-box">
            <h2>📊 Статистика</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <strong><?= $total_orders ?></strong>
                    <span>Всего заявок</span>
                </div>
            </div>
            
            <h3>Типы помещений:</h3>
            <div class="stats-grid">
                <?php foreach ($room_stats as $stat): ?>
                    <div class="stat-card">
                        <strong><?= $stat['order_count'] ?></strong>
                        <span><?= htmlspecialchars($stat['room_name']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Таблица заявок -->
        <h2>📋 Все заявки</h2>
        
        <?php if (empty($orders)): ?>
            <p>Нет зарегистрированных заявок.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th>Тип помещения</th>
                        <th>Авто-Логин</th>
                        <th>Согласие</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['order_id'] ?></td>
                            <td><?= htmlspecialchars($order['firstName'] ?? '') ?></td>
                            <td><?= htmlspecialchars($order['telephone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($order['email'] ?? '') ?></td>
                            <td>
                                <?php 
                                $rooms = explode(', ', $order['rooms'] ?? '');
                                foreach ($rooms as $room):
                                    if ($room):
                                ?>
                                    <span class="badge"><?= htmlspecialchars($room) ?></span>
                                <?php 
                                    endif;
                                endforeach;
                                ?>
                            </td>
                            <td><?= htmlspecialchars($order['login'] ?? '') ?></td>
                            <td class="<?= $order['agreed'] ? 'agree-yes' : 'agree-no' ?>">
                                <?= $order['agreed'] ? '✅ Да' : '❌ Нет' ?>
                            </td>
                            <td class="actions">
                                <a href="/fullstack-webproject/modules/edit.php?id=<?= $order['order_id'] ?>" class="btn-edit btn">✏️ Редактировать</a>
                                <form action="/fullstack-webproject/modules/admin.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="delete_id" value="<?= $order['order_id'] ?>">
                                    <button type="submit" class="btn-delete btn" onclick="return confirm('Вы уверены, что хотите удалить заявку «<?= htmlspecialchars($order['firstName'] ?? '') ?>»?')">
                                        🗑️ Удалить
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <p style="margin-top: 30px; text-align: center;">
            <a href="/fullstack-webproject/" style="color: #4CAF50;">← Вернуться на главную</a>
        </p>
    </div>
</body>
</html>