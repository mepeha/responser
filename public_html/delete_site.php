<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site = $_POST['site'];
    // Имя папки и файла остается в формате site_ru
    $site_dir = $site;
    // Преобразуем имя сайта для удаления из urls.txt
    $site_for_deletion = str_replace('_', '.', $site);

    // Удаляем все файлы и папку сайта
    if (is_dir($site_dir)) {
        // Удаляем все файлы в папке
        $files = glob("$site_dir/*");
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        // Удаляем саму папку
        rmdir($site_dir);
    }

    // Обновляем urls.txt
    $urls_file = 'urls.txt';
    if (file_exists($urls_file)) {
        $urls = file($urls_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Отображаем исходное содержимое
        echo '<script>alert("Исходный массив: \\n' . implode("\\n", $urls) . '");</script>';

        // Фильтруем строки
        $new_urls = array_filter($urls, function($line) use ($site_for_deletion) {
            return strpos(trim($line), $site_for_deletion) === false;
        });

        // Отображаем удаляемый сайт и обновленный массив
        echo '<script>alert("Удаляем сайт: ' . $site_for_deletion . '");</script>';
        echo '<script>alert("Массив после удаления: \\n' . implode("\\n", $new_urls) . '");</script>';

        // Записываем обновленный список в файл
        file_put_contents($urls_file, implode("\n", $new_urls) . "\n");

        // Отображаем содержимое urls.txt после обновления
        $updated_urls = file($urls_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        echo '<script>alert("Содержимое urls.txt после обновления: \\n' . implode("\\n", $updated_urls) . '");</script>';
    }
}
?>
