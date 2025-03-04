<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['id'])) {
    // Если пользователь не авторизован, перенаправляем его на страницу входа
    header("Location: login.php");
    exit;
}

// Проверяем, передан ли идентификатор альбома через GET-запрос
if (!isset($_GET['id'])) {
    echo "Ошибка: Идентификатор альбома не указан.";
    exit;
}

// Получаем идентификатор альбома из GET-запроса
$albumId = $_GET['id'];

// Подключение к базе данных
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'avtorizac';
$db = mysqli_connect($host, $user, $password, $database);

if (!$db) {
    die("Ошибка подключения к базе данных: " . mysqli_connect_error());
}

// Получаем данные об альбоме по его идентификатору
$queryAlbum = "SELECT album_name, description, access_level, allowed_users FROM albums WHERE id_album = $albumId";
$resultAlbum = mysqli_query($db, $queryAlbum);

if ($resultAlbum && mysqli_num_rows($resultAlbum) > 0) {
    // Получаем данные об альбоме
    $rowAlbum = mysqli_fetch_assoc($resultAlbum);
    $albumName = $rowAlbum['album_name'];
    $description = $rowAlbum['description'];
    $accessLevel = $rowAlbum['access_level'];
    $allowedUsers = $rowAlbum['allowed_users'];

    // Проверяем доступ к альбому
    if ($accessLevel == 'public' || $allowedUsers == $_SESSION['id']) {
        // Пользователь имеет доступ к альбому, продолжаем отображение содержимого
    } else {
        // Пользователь не имеет доступа к альбому, выводим сообщение или перенаправляем его на другую страницу
        echo "Ошибка: У вас нет доступа к данному альбому.";
        exit;
    }

    // Освобождаем память от результата запроса
    mysqli_free_result($resultAlbum);
} else {
    echo "Ошибка: Альбом не найден.";
    exit;
}

// Получаем все изображения в альбоме
$queryImages = "SELECT photo_path FROM photos WHERE album_id = $albumId";
$resultImages = mysqli_query($db, $queryImages);

$images = array();
if ($resultImages && mysqli_num_rows($resultImages) > 0) {
    while ($rowImage = mysqli_fetch_assoc($resultImages)) {
        $images[] = $rowImage['photo_path'];
    }
    // Освобождаем память от результата запроса
    mysqli_free_result($resultImages);
}

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <!-- Мета-теги, заголовок и стили -->
     <title>Альбомы</title>
     <style>
/* Основные стили для body (ваши стили с небольшими изменениями) */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 20px;

    min-height: 100vh;
    background: #4e54c8;
    background: -webkit-linear-gradient(to left, #8f94fb, #4e54c8);
    background: linear-gradient(to left, #8f94fb, #4e54c8);
    position: relative;
    box-sizing: border-box;
}
.return{
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: self-end;
            color: white;
        }
.container{
    display: flex;
    justify-content: center;
    align-items: flex-start; /* Изменено на flex-start, чтобы контент начинался сверху */
}
/* Контейнер для альбома */
.album {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 10px;
    padding: 20px;
    max-width: 800px;
    width: 100%;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

/* Заголовок альбома */
.album h1 {
    font-size: 2rem;
    margin: 0 0 10px 0;
    color: #333;
    text-align: center;
}

/* Описание альбома */
.album p {
    font-size: 1rem;
    color: #555;
    text-align: center;
    margin: 0 0 20px 0;
}

/* Контейнер для изображений */
.images {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); /* Автоматическое заполнение */
    gap: 10px; /* Расстояние между изображениями */
    max-height: 400px; /* Максимальная высота контейнера */
    overflow-y: auto; /* Прокрутка внутри контейнера */
    padding: 10px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 8px;
}

/* Изображения */
.images img {
    width: 100%; /* Ширина изображения */
    height: 200px; /* Фиксированная высота */
    object-fit: cover; /* Обрезка изображения для сохранения пропорций */
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Эффект при наведении на изображение */
.images img:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}
.images::-webkit-scrollbar {
    width: 8px;
}
.images::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 4px;
}
.images::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.3);
    border-radius: 4px;
}
/* Стили для модального окна */
.modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7); /* Прозрачный цвет фона */
            overflow: auto;
        }

        .modal-content {
            margin: 10% auto;
            display: block;
            width: auto;
            max-width: 100%;
            max-height: 80vh;
            background-color: #fefefe;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center; /* Выравниваем содержимое по центру */
        }

        .modal-content img {
            max-width: 100%; /* Максимальная ширина изображения равна ширине контейнера */
            max-height: 80vh; /* Максимальная высота изображения равна 80% высоты экрана */
            object-fit: contain; /* Подгоняем изображение в контейнер без искажений */
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
     </style>
</head>
<body>
    <div class="container">
    <div class="album">
        <!-- Заголовок и описание альбома -->
        <h1><?php echo $albumName; ?></h1>
        <p><?php echo $description; ?></p>

        <!-- Галерея изображений -->
        <div class="images">
            <?php foreach ($images as $image): ?>
                <img src="<?php echo $image; ?>" alt="">
           <?php endforeach; ?>
        </div>
     </div></div>
     <a href="get_album.php" class="return">Вернуться обратно</a>  

         <!-- Модальное окно для увеличенного изображения -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('myModal').style.display='none'">&times;</span>
            <img id="modalImg" src="" alt="Увеличенное изображение">
        </div>
    </div>
    <!-- JavaScript для модального окна и дополнительных функций -->
    <script>
        // Добавляем обработчик события для увеличения изображения при клике
document.querySelectorAll('.images img').forEach(img => {
    img.addEventListener("click", function() {
        var modal = document.getElementById("myModal");
        var modalImg = document.getElementById("modalImg");
        modal.style.display = "block"; // Показываем модальное окно
        modalImg.src = this.src; // Устанавливаем src для увеличенного изображения
    });
});

// Закрытие модального окна при клике на крестик
document.querySelector('.close').addEventListener('click', function() {
    document.getElementById("myModal").style.display = "none";
});

// Закрытие модального окна при клике вне изображения
window.addEventListener('click', function(event) {
    var modal = document.getElementById("myModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
});
window.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
        document.getElementById("myModal").style.display = "none";
    }
});
    </script>
</body>
</html>
