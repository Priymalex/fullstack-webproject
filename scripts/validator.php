<?php
function validate_form_array($data) {
    $errors = array();

    // Получаем данные из формы
    $firstName = trim($data['firstName'] ?? '');
    $phone = trim($data['telephone'] ?? '');
    $email = trim($data['mail'] ?? '');
    $rooms = $data['roomType'] ?? [];
    $agreement = isset($data['agreement']) && ($data['agreement'] === true || $data['agreement'] === 'on' || $data['agreement'] === '1');

    // Валидация имени
    if (empty($firstName)) {
        $errors['firstName'] = "Имя обязательно для заполнения";
    } elseif (!preg_match('/^[a-zA-Zа-яёА-ЯЁ\s\-]+$/u', $firstName)) {
        $errors['firstName'] = "Имя может содержать только буквы, пробелы и дефис";
    }

    // Валидация телефона
    if (empty($phone)) {
        $errors['telephone'] = "Телефон обязателен для заполнения";
    } elseif (!preg_match('/^\+?[0-9]{11}$/', $phone)) {
        $errors['telephone'] = "Введите 11 цифр номера (РФ), например: 79132323223";
    }

    // Валидация email
    if (empty($email)) {
        $errors['mail'] = "Email обязателен для заполнения";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['mail'] = "Введите корректный email";
    }

    // Валидация комнат
    if (empty($rooms) || !is_array($rooms) || count($rooms) === 0) {
        $errors['roomType'] = "Выберите хотя бы один тип помещения";
    }

    // Валидация согласия
    if (!$agreement) {
        $errors['agreement'] = "Необходимо согласиться с политикой обработки данных";
    }

    return $errors;
}
?>
