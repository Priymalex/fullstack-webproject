<?php
function validate_form_array($data) {
    $errors = array();

    // Получаем данные из формы
    $firstName = $data['firstName'] ?? '';
    $phone = $data['telephone'] ?? '';
    $email = $data['mail'] ?? '';
    $rooms = $data['roomType'] ?? [];
    $agreement = isset($data['agreement']) && ($data['agreement'] === true  $data['agreement'] === 'on'  $data['agreement'] === '1');

    // Валидация имени (буквы, пробелы, дефис)
    if (empty($firstName) || !preg_match('/^[a-zA-Zа-яёА-ЯЁ\s\-]+$/u', $firstName)) {
        $errors['firstName'] = "Имя может содержать только буквы, пробелы и дефис";
    }

    // Валидация телефона (11 цифр, опционально + в начале)
    if (empty($phone) || !preg_match('/^\+?[0-9]{11}$/', $phone)) {
        $errors['telephone'] = "Введите 11 цифр вашего номера (РФ), например: 79991234567";
    }

    // Валидация email
    if (empty($email) || !preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $errors['mail'] = "Введите корректный email, например: name@example.com";
    }

    // Валидация выбора комнат (можно выбрать несколько)
    if (empty($rooms)  !is_array($rooms)  count($rooms) === 0) {
        $errors['roomType'] = "Выберите хотя бы один тип помещения";
    }

    // Дополнительная валидация: проверяем, что выбранные комнаты есть в списке допустимых
    $allowedRooms = ['livingroom', 'bedroom', 'kitchen', 'office', 'bathroom', 'hallway'];
    if (!empty($rooms) && is_array($rooms)) {
        foreach ($rooms as $room) {
            if (!in_array($room, $allowedRooms)) {
                $errors['roomType'] = "Выбрано некорректное значение помещения";
                break;
            }
        }
    }

    // Валидация согласия с политикой
    if (!$agreement) {
        $errors['agreement'] = "Необходимо согласиться с политикой обработки персональных данных";
    }

    return $errors;
}
?>