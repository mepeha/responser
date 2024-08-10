<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Логи сайтов</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <style>
        body {
            background-color: #373737;
            color: #f1c10e !important;
        }
        .log-output {
            height: 70vh;
            overflow: auto;
            background: #252525;
        }
        table {
            font-weight: 500;
        }
        table.dataTable {
            background: #252525;
            color: #f1c10e;
        }
        table.dataTable thead th {
            background-color: #373737;
            color: #f1c10e;
        }
        table.dataTable tbody tr {
            background-color: #2e2e2e;
        }
        table.dataTable tbody tr:nth-child(even) {
            background-color: #252525;
        }
        table.dataTable td, table.dataTable th {
            padding: 10px;
            vertical-align: middle;
        }
        table.dataTable td a {
            color: #f1c10e;
            text-decoration: none;
        }
        table.dataTable td a:hover {
            text-decoration: underline;
        }
        table.dataTable td, table.dataTable th {
            white-space: nowrap; /* Предотвращает сокращение текста */
            overflow: hidden;
            text-overflow: ellipsis; /* Добавляет многоточие, если текст слишком длинный */
        }

        .area {
            margin: 15px 15px;
            position: relative;
        }
        .control-buttons {
            display: flex;
            gap: 10px;
            padding: 15px;
        }
        .site-management {
            margin-top: 20px;
        }
        .btn, .nav-link {
            background-color: #252525;
            color: #f1c10e;
            font-weight: 700;
            border: 0;
        }
        button.active {
            background-color: #f1c10e !important;
            color: #373737 !important;
            font-weight: 700;
            border: 0;
        }
        .btn:hover {
            background-color: #f1c10e;
            color: #373737;
            font-weight: 700;
            border: 0;
        }
        .nav-link:hover {
            background-color: #f1c10e;
            color: #373737;
            font-weight: 700;
            border: 0;
        }
        .nav-tabs {
            border-bottom: 2px solid #f1c10e;
        }
        .nav-link:hover {
            border: 0 !important;
        }
        .nav-link.active {
            border: 0 !important;
        }
        /* Стили для Webkit браузеров */
        ::-webkit-scrollbar {
            width: 12px;
            height: 12px;
            background: #191919;
        }
        ::-webkit-scrollbar-track {
            background: #191919;
        }
        ::-webkit-scrollbar-thumb {
            background: #f1c10e;
        }
        ::-webkit-scrollbar-corner {
            background: #373737;
        }
        .modal-content {
            background: #2e2e2e !important;
        }
        .modal-dialog {
            max-width: 90vw !important;
        }
    </style>
</head>
<body>

<div class="area">
    <!-- Кнопки управления -->

    <!-- Первый уровень вкладок (сайты) -->
    <ul class="nav nav-tabs" id="siteTabs" role="tablist">
        <?php
        $log_directories = glob('*', GLOB_ONLYDIR);
        $firstSite = true;
        foreach ($log_directories as $directory) {
            $site_name = str_replace('_', '.', $directory);
            $active_class = $firstSite ? 'active' : '';
            echo "<li class='nav-item' role='presentation'>
                    <button class='nav-link $active_class' id='{$directory}-tab' data-bs-toggle='tab' data-bs-target='#{$directory}' type='button' role='tab' aria-controls='{$directory}' aria-selected='true'>{$site_name}</button>
                  </li>";
            $firstSite = false;
        }
        ?>
    </ul>

    <div class="tab-content mt-3" id="siteTabsContent">
        <?php
        $firstSite = true;
        foreach ($log_directories as $directory) {
            $active_class = $firstSite ? 'show active' : '';
            echo "<div class='tab-pane fade $active_class' id='{$directory}' role='tabpanel' aria-labelledby='{$directory}-tab'>";

            // Второй уровень вкладок (даты логов)
            echo "<ul class='nav nav-pills mb-3' id='dateTabs-{$directory}' role='tablist'>";
            $log_files = glob("$directory/*.txt");
            $firstDate = true;
            foreach ($log_files as $log_file) {
                $date = basename($log_file, '.txt');
                $active_date_class = $firstDate ? 'active' : '';
                echo "<li class='nav-item' role='presentation'>
                        <button class='nav-link $active_date_class' id='{$date}-tab' data-bs-toggle='tab' data-bs-target='#log-{$directory}-{$date}' type='button' role='tab' aria-controls='log-{$directory}-{$date}' aria-selected='true'>{$date}</button>
                      </li>";
                $firstDate = false;
            }
            echo "</ul>";

            // Содержимое второго уровня вкладок (логи за выбранные даты)
            echo "<div class='tab-content log-output' id='dateTabsContent-{$directory}'>";
            $firstDate = true;
            foreach ($log_files as $log_file) {
                $date = basename($log_file, '.txt');
                $active_date_class = $firstDate ? 'show active' : '';
                echo "<div class='tab-pane fade $active_date_class' id='log-{$directory}-{$date}' role='tabpanel' aria-labelledby='{$date}-tab'>
                        <table id='logTable-{$directory}-{$date}' class='display'>
                            <thead>
                                <tr>
                                    <th>Время запроса</th>
                                    <th>Код ответа</th>
                                    <th>Время ответа в мс</th>
                                    <th>URL</th>
                                    <th>Общее время запроса</th>
                                    <th>Время соединения</th>
                                    <th>Вес в байтах</th>
                                    <th>Заголовки ответа</th>
                                </tr>
                            </thead>
                            <tbody>";

                $log_content = file_get_contents($log_file);
                $log_lines = explode("\n", $log_content);
                foreach ($log_lines as $line) {
                    // Пример парсинга строки лога
                    preg_match('/^(?<time>\d{2}:\d{2}:\d{2}) - Код ответа: (?<response_code>\d+) - Время ответа: (?<response_time>\d+)мс - (?<url>https?:\/\/[^\s]+) - Дополнительно: Общее время запроса: (?<total_time>\d+(\.\d+)?)мс, Время соединения: (?<connection_time>\d+(\.\d+)?)мс, Размер данных: (?<data_size>\d+) байт - Заголовки ответа: (?<headers>.+)$/', $line, $matches);
                    if ($matches) {
                        echo "<tr>
                                <td>{$matches['time']}</td>
                                <td>{$matches['response_code']}</td>
                                <td>{$matches['response_time']}</td>
                                <td><a href='{$matches['url']}' target='_blank'>{$matches['url']}</a></td>
                                <td>{$matches['total_time']}</td>
                                <td>{$matches['connection_time']}</td>
                                <td>{$matches['data_size']}</td>
                                <td><pre>" . htmlspecialchars($matches['headers']) . "</pre></td>
                              </tr>";
                    }
                }

                echo "        </tbody>
                        </table>
                      </div>";
                $firstDate = false;
            }
            echo "</div>";

            echo "</div>";
            $firstSite = false;
        }
        ?>
    </div>
</div>

<!-- Модальное окно для редактирования urls.txt -->
<div class="modal fade" id="editUrlsModal" tabindex="-1" aria-labelledby="editUrlsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUrlsModalLabel">Редактирование списка сайтов</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <textarea id="urlsContent" class="form-control" rows="10"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveUrlsButton">Сохранить изменения</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для управления сайтами -->
<div class="modal fade" id="manageSitesModal" tabindex="-1" aria-labelledby="manageSitesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageSitesModalLabel">Управление сайтами</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body site-management">
                <?php
                foreach ($log_directories as $directory) {
                    $site_name = str_replace('_', '.', $directory);
                    echo "<div class='mb-2'>
                            <h6>{$site_name}</h6>
                            <button type='button' class='btn btn-warning btn-sm' data-site='{$directory}' onclick='clearCache(this)'>Очистить кеш</button>
                            <button type='button' class='btn btn-danger btn-sm' data-site='{$directory}' onclick='deleteSite(this)'>Удалить сайт</button>
                          </div>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div class="control-buttons">
    <button class="btn btn-primary" id="refreshButton">Запустить скрипт</button>
    <button class="btn btn-secondary" id="editUrlsButton" data-bs-toggle="modal" data-bs-target="#editUrlsModal">Список сайтов</button>
    <button class="btn btn-info" id="manageSitesButton" data-bs-toggle="modal" data-bs-target="#manageSitesModal">Управление сайтами</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        // Инициализация DataTables для всех таблиц
        $('table.display').each(function() {
            $(this).DataTable({
                "paging": false,
                "searching": false,  // Поиск отключен
                "info": false,
                "ordering": true
            });
        });

        // Обработчик нажатия кнопки "Запустить скрипт"
        $('#refreshButton').click(function() {
            $.ajax({
                url: 'https://kot.real-site.ru/time-script.php',
                type: 'GET',
                success: function(response) {
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка при выполнении скрипта:', error);
                }
            });
        });

        // Открытие модального окна и загрузка содержимого urls.txt
        $('#editUrlsModal').on('show.bs.modal', function() {
            $.ajax({
                url: 'load_urls.php',
                type: 'GET',
                success: function(data) {
                    $('#urlsContent').val(data);
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка при загрузке списка сайтов:', error);
                }
            });
        });

        // Сохранение изменений в urls.txt
        $('#saveUrlsButton').click(function() {
            const urlsContent = $('#urlsContent').val();
            $.ajax({
                url: 'save_urls.php',
                type: 'POST',
                data: { content: urlsContent },
                success: function(response) {
                    alert('Список сайтов успешно сохранен');
                    $('#editUrlsModal').modal('hide');
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка при сохранении списка сайтов:', error);
                }
            });
        });
    });

    function clearCache(button) {
        const site = button.getAttribute('data-site');
        $.ajax({
            url: 'clear_cache.php',
            type: 'POST',
            data: { site: site },
            success: function(response) {
                alert('Кеш сайта очищен');
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error('Ошибка при очистке кеша:', error);
            }
        });
    }

    function deleteSite(button) {
        const site = button.getAttribute('data-site');
        if (confirm('Вы уверены, что хотите удалить сайт?')) {
            $.ajax({
                url: 'delete_site.php',
                type: 'POST',
                data: { site: site },
                success: function(response) {
                    alert('Сайт удален: ' + site);
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Ошибка при удалении сайта:', error);
                }
            });
        }
    }
</script>
</body>
</html>
