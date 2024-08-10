<?php

function get_urls_from_file($filename) {
    if (!file_exists($filename)) {
        error_log('Файл с URL не найден: ' . $filename);
        return [];
    }

    $file_contents = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($file_contents === false) {
        error_log('Не удалось прочитать файл: ' . $filename);
        return [];
    }

    return array_map('trim', $file_contents);
}

function get_log_filename($url) {
    $safe_url = preg_replace('/[^a-zA-Z0-9_-]/', '_', parse_url($url, PHP_URL_HOST));
    return $safe_url . '/' . date("Y_m_d") . '.txt'; // Используем дату в формате "год_месяц_день"
}

function log_response($url, $status, $response_time, $additional_info = '') {
    $log_filename = get_log_filename($url);

    $log_dir = dirname($log_filename);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }

    // Формируем новую часть сообщения
    $formatted_message = date("H:i:s") . " - Код ответа: $status - Время ответа: ${response_time}мс";
    $formatted_message .= " - $url";

    if ($additional_info) {
        // Убираем переносы строк из дополнительной информации
        $additional_info = str_replace(["\r", "\n"], '', $additional_info);
        $formatted_message .= " - Дополнительно: $additional_info";
    }

    $formatted_message .= PHP_EOL;

    file_put_contents($log_filename, $formatted_message, FILE_APPEND | LOCK_EX);
}

function get_http_response_info($url) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Таймаут в 60 секунд
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_CERTINFO, true);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);

    $start_time = microtime(true);
    $response = curl_exec($ch);
    $response_time = round((microtime(true) - $start_time) * 1000); // время в миллисекундах

    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000; // Общее время выполнения запроса в миллисекундах
    $connect_time = curl_getinfo($ch, CURLINFO_CONNECT_TIME) * 1000; // Время соединения в миллисекундах
    $download_size = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD); // Размер загруженных данных в байтах

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        if (strpos($error_msg, 'timed out') !== false) {
            return [0, $response_time, 'Время ожидания превысило минуту'];
        }
        error_log('cURL ошибка: ' . $error_msg);
        return [0, $response_time, $error_msg];
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Формируем дополнительную информацию для логирования
    $additional_info = "Общее время запроса: ${total_time}мс, Время соединения: ${connect_time}мс, Размер данных: ${download_size} байт";

    // Добавляем исходный заголовок ответа
    $additional_info .= " - Заголовки ответа: " . substr($response, 0, 500); // Ограничим длину заголовков до 500 символов

    return [$http_code, $response_time, $additional_info];
}

// Основной скрипт
$urls = get_urls_from_file('urls.txt');

foreach ($urls as $url) {
    list($status, $response_time, $additional_info) = get_http_response_info($url);
    log_response($url, $status, $response_time, $additional_info);
}

?>
