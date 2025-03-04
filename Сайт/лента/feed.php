<?php
// Подключение к базе данных
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'avtorizac';

$db = mysqli_connect($host, $user, $password, $database);

if (!$db) {
    die("Ошибка подключения к базе данных: " . mysqli_connect_error());
}

// Запрос для получения публикаций с информацией о пользователях
$query = "
    SELECT posts.*, databas.login, databas.avatar
    FROM posts
    JOIN databas ON posts.user_id = databas.id
    ORDER BY posts.created_at DESC
";

$result = mysqli_query($db, $query);

if (!$result) {
    die("Ошибка выполнения запроса: " . mysqli_error($db));
}

$posts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $posts[] = $row;
}

mysqli_close($db);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лента публикаций</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .post {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .post-header img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .post-header a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
        .post-header a:hover {
            text-decoration: underline;
        }
        .post-content {
            margin-bottom: 15px;
        }
        .post-content img {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 10px;
            cursor: pointer;
        }
        .post-footer {
            color: #777;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <h1>Лента публикаций</h1>

    <?php if (empty($posts)): ?>
        <p>Публикаций пока нет.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <div class="post-header">
                    <!-- Аватарка пользователя -->
                    <img src="<?php echo htmlspecialchars($post['avatar'] ?? 'default_avatar.jpg'); ?>" alt="Аватарка">
                    <!-- Имя пользователя с ссылкой на его страницу -->
                    <a href="user_profile.php?id=<?php echo $post['user_id']; ?>">
                        <?php echo htmlspecialchars($post['login']); ?>
                    </a>
                </div>
                <!-- Текст публикации -->
                <div class="post-content">
                    <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    <!-- Изображение публикации (если есть) -->
                    <?php if (!empty($post['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Изображение публикации" class="post-image">
                    <?php endif; ?>
                </div>
                <!-- Дата публикации -->
                <div class="post-footer">
                    Опубликовано: <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Модальное окно для увеличения изображений -->
    <div id="myModal" class="modal">
        <span class="close">&times;</span>
        <img id="modalImg" class="modal-content" alt="Увеличенное изображение">
    </div>

    <script>
        // Открытие модального окна при клике на изображение
        document.querySelectorAll('.post-image').forEach(img => {
            img.addEventListener('click', function() {
                var modal = document.getElementById('myModal');
                var modalImg = document.getElementById('modalImg');
                modal.style.display = 'block';
                modalImg.src = this.src;
            });
        });

        // Закрытие модального окна при клике на крестик
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('myModal').style.display = 'none';
        });

        // Закрытие модального окна при клике вне изображения
        window.addEventListener('click', function(event) {
            var modal = document.getElementById('myModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>