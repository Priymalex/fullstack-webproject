<?php
$config = include('db_config.php');

try {
    $db = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['user'], 
        $config['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
            PDO::ATTR_EMULATE_PREPARES => false, 
        ]
    );
} catch (PDOException $e) {
    error_log("Ошибка подключения к БД: " . $e->getMessage());
    throw $e; 
}