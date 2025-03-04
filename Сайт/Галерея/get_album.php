<?php
session_start();

// Подключение к базе данных
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'avtorizac';
$db = mysqli_connect($host, $user, $password, $database);

if (!$db) {
    die("Ошибка подключения к базе данных: " . mysqli_connect_error());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="albumstyle.css">
    <title>Document</title>
    <style>
                /* Стили для фона */
                h1 {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .return{
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: self-end;
            color: white ;
        }
    </style>
</head>
<body>
    <h1>Альбомы</h1>
    <?php

if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id'];

    // Получаем данные об альбомах пользователя
    $albums = array();
    $query = "SELECT id_album, album_name, description FROM albums WHERE id_user = $userId";
    $result = mysqli_query($db, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $albumId = $row['id_album'];
            $albumName = $row['album_name'];
            $description = $row['description'];
            // Создаем ссылку на страницу просмотра альбома
            $albumLink = "view_album.php?id=$albumId";
            // Формируем HTML для отображения ссылок на альбомы
            echo "<div class='album'>";
            echo "<h2><a href='$albumLink'>$albumName</a></h2>";
            echo "<p>$description</p>";
            echo "</div>";
        }
        mysqli_free_result($result);
    } else {
        echo "Ошибка выполнения запроса: " . mysqli_error($db);
    }
} else {
    echo "Пользователь не аутентифицирован.";
}

mysqli_close($db);
?>

    <a href="..\Профиль\profile.html" class="return">Вернуться в Профиль</a>
</body>
</html>
