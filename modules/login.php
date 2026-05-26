<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!empty($_SESSION['login'])) {
    header('Location: /fullstack-webproject/');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // ... показ формы (оставьте как есть)
} else {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        setcookie('login_error', 'Ошибка безопасности', 0, '/');
        header('Location: login.php');
        exit;
    }

    $login = trim($_POST['login'] ?? '');
    $pass = $_POST['pass'] ?? '';

    if (empty($login) || empty($pass)) {
        setcookie('login_error', 'Заполните все поля', 0, '/');
        header('Location: login.php');
        exit;
    }

    require_once __DIR__ . '/../scripts/db.php';

    try {
        $stmt = $db->prepare("SELECT u.order_id, u.login, u.password_hash, o.firstName, o.email, o.telephone 
                              FROM Users u 
                              JOIN Ordering o ON u.order_id = o.order_id 
                              WHERE u.login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['order_id'];
            $_SESSION['login'] = $user['login'];
            $_SESSION['user_name'] = $user['firstName'];
            $_SESSION['user_email'] = $user['email'];   // ← сохраняем email
            $_SESSION['user_phone'] = $user['telephone']; // ← сохраняем телефон (опционально)
            
            session_regenerate_id(true);
            
            header('Location: /fullstack-webproject/');
            exit;
        } else {
            setcookie('login_error', 'Неверный логин или пароль', 0, '/');
            header('Location: login.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        setcookie('login_error', 'Ошибка сервера. Попробуйте позже.', 0, '/');
        header('Location: login.php');
        exit;
    }
}
?>
