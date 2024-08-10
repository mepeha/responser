<?php
if (isset($_POST['content'])) {
    $content = $_POST['content'];
    file_put_contents('urls.txt', $content);
    echo 'Список сохранен';
} else {
    echo 'Ошибка сохранения';
}
?>
