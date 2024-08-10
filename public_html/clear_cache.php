<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site = $_POST['site'];
    $site_dir = $site;

    // Удаляем все файлы в папке сайта
    if (is_dir($site_dir)) {
        $files = glob("$site_dir/*");
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    echo 'Кеш очищен.';
}
?>
