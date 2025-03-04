<?php
session_start();

// Проверяем, залогинен ли пользователь
if (!isset($_SESSION['id'])) {
    echo "Пользователь не аутентифицирован.";
    exit;
}

// Проверяем, были ли отправлены данные формы для создания альбома
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["album_name"]) && isset($_POST["description"]) && isset($_POST["access_level"])) {
    // Получаем данные из формы
    $albumName = $_POST["album_name"];
    $description = $_POST["description"];
    $accessLevel = $_POST["access_level"];
    $allowedUsers = $_POST["allowed_users"] ?? ''; // Это строка с пользователями, разделенными запятыми

    // Получаем идентификатор текущего пользователя
    $userId = (int)$_SESSION['id']; // Преобразуем в число

    // Параметры подключения к базе данных
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $database = 'avtorizac';
    $redirect_url = 'get_album.php';

    // Подключение к базе данных
    $db = mysqli_connect($host, $user, $password, $database);

    // Проверяем подключение к базе данных
    if (!$db) {
        echo "Ошибка подключения к базе данных: " . mysqli_connect_error();
        exit;
    }

    // Путь для сохранения файлов
    $upload_dir = "../uploads/";

    // Проверяем, существует ли директория для загрузки
    if (!is_dir($upload_dir)) {
        die("Директория для загрузки файлов не существует.");
    }

    // Проверяем, доступна ли директория для записи
    if (!is_writable($upload_dir)) {
        die("Директория для загрузки файлов недоступна для записи.");
    }

    // Проверяем, были ли отправлены файлы
    if (isset($_FILES['photos']['name'])) {
        $files = $_FILES['photos'];

        // Переменная для хранения пути к изображению обложки альбома
        $albumCoverPath = '';

        // Сохраняем обложку альбома
        if (!empty($_FILES['album_cover']['name'])) {
            $albumCoverName = $_FILES['album_cover']['name'];
            $albumCoverTmp = $_FILES['album_cover']['tmp_name'];
            $albumCoverPath = $upload_dir . basename($albumCoverName);

            // Перемещаем файл в указанную директорию
            if (!move_uploaded_file($albumCoverTmp, $albumCoverPath)) {
                echo "Ошибка при загрузке обложки альбома.";
                exit;
            }
        }

        // Подготовка SQL-запроса для добавления нового альбома
        $sql_album = "INSERT INTO albums (album_name, description, id_user, photo_path, access_level, allowed_users) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_album = mysqli_prepare($db, $sql_album);

        if (!$stmt_album) {
            echo "Ошибка подготовки запроса: " . mysqli_error($db);
            exit;
        }

        // Привязываем параметры к подготовленному запросу
        $success_album = mysqli_stmt_bind_param($stmt_album, "ssisss", $albumName, $description, $userId, $albumCoverPath, $accessLevel, $allowedUsers);

        if (!$success_album) {
            echo "Ошибка привязки параметров: " . mysqli_stmt_error($stmt_album);
            exit;
        }

        // Выполнение запроса
        $success_album = mysqli_stmt_execute($stmt_album);

        if (!$success_album) {
            echo "Ошибка выполнения запроса: " . mysqli_stmt_error($stmt_album);
            exit;
        }

        echo "Альбом успешно добавлен.";

        // Получаем идентификатор добавленного альбома
        $albumId = mysqli_insert_id($db);

        // Сохраняем остальные фотографии в таблицу photos
        foreach ($files['tmp_name'] as $key => $tmp_name) {
            $file_name = $files['name'][$key];
            $file_tmp = $files['tmp_name'][$key];

            // Путь для сохранения файла
            $target_file = $upload_dir . basename($file_name);

            // Перемещаем файл в указанную директорию
            if (!move_uploaded_file($file_tmp, $target_file)) {
                echo "Ошибка при загрузке файла.";
                exit;
            }

            // Подготовка SQL-запроса для добавления фотографии в таблицу photos
            $sql_photo = "INSERT INTO photos (photo_path, id_user, album_id) VALUES (?, ?, ?)";
            $stmt_photo = mysqli_prepare($db, $sql_photo);

            if (!$stmt_photo) {
                echo "Ошибка подготовки запроса: " . mysqli_error($db);
                exit;
            }

            // Привязываем параметры к подготовленному запросу
            $success_photo = mysqli_stmt_bind_param($stmt_photo, "sii", $target_file, $userId, $albumId);

            if (!$success_photo) {
                echo "Ошибка привязки параметров: " . mysqli_stmt_error($stmt_photo);
                exit;
            }

            // Выполнение запроса
            $success_photo = mysqli_stmt_execute($stmt_photo);

            if (!$success_photo) {
                echo "Ошибка выполнения запроса: " . mysqli_stmt_error($stmt_photo);
                exit;
            }

            echo '<div class="container"><h2>Данные успешно загружены.</h2></div>';
            echo '<meta http-equiv="refresh" content="0.001;url=' . $redirect_url . '" />';
        }
    }

    // Закрываем подключение к базе данных
    mysqli_close($db);
}