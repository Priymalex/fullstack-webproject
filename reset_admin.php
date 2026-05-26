<?php
require_once __DIR__ . '/scripts/db.php';

// Новый пароль (придумайте свой)
$new_password = 'admin123';
$hash = password_hash($new_password, PASSWORD_DEFAULT);

// Обновляем пароль администратора
$stmt = $db->prepare("UPDATE Admin2 SET password_hash = ? WHERE login = 'admin'");
$stmt->execute([$hash]);

echo "Пароль для админа обновлен!
";
echo "Логин: admin
";
echo "Новый пароль: " . $new_password . "
";
echo "Хэш: " . $hash;
