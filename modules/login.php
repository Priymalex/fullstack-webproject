<?php
header('Content-Type: text/html; charset=UTF-8');

session_start();

if (!empty($_SESSION['login'])) {
    header('Location: /fullstack-webproject/');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = !empty($_COOKIE['login_error']) ? $_COOKIE['login_error'] : '';
if ($errors) {
    setcookie('login_error', '', time() - 3600, '/'); 
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Авторизация</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .container { max-width: 300px; margin: 0 auto; }
        form { background: #f9f9f9; padding: 20px; border-radius: 10px; }
        input { width: 100%; padding: 8px; margin: 10px 0; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #4CAF50; color: white; border: none; cursor: pointer; border-radius: 5px; }
        .error { color: red; margin: 10px 0; text-align: center; }
        .register-link { text-align: center; margin-top: 15px; }
        .register-link a { color: #008CBA; text-decoration: none; }
    </style>
</head>
<body>

    <?php if ($errors): ?>
            <div class="error-box">
                <?= htmlspecialchars($errors) ?>
            </div>
        <?php endif; ?>

    <div class="container">
        <form action="login.php" method="POST">

            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

            <h2 style="text-align: center;">Вход в систему</h2>
            <input name="login" id="login" type="text" placeholder="Логин" required autocomplete="username">
            <input name="pass" id="pass" type="password" placeholder="Пароль" required autocomplete="current-password">
            <button type="submit">Войти</button>

        </form>
        <div class="register-link">
            <a href="/fullstack-webproject/">Нет заявки? Заполните форму заказа</a>
        </div>
    </div>
</body>
</html>
<?php
} else {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('HTTP/1.1 403 Forbidden');
        exit('Ошибка безопасности: токен невалиден');
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
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password_hash'])) {
            $_SESSION['login'] = $user['login'];
            $_SESSION['user_id'] = $user['user_id']; 
            $_SESSION['user_email'] = $['email'];
            $_SESSION['user_phone'] = $['telephone'];
            
            session_regenerate_id(true); 

            header('Location: /fullstack-webproject/');
            exit;
        } else {
            setcookie('login_error', 'Неверный логин или пароль', 0, '/');
            header('Location: login.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Ошибка авторизации: " . $e->getMessage());
        exit('Ошибка сервера, попробуйте позже.');
    }
}
