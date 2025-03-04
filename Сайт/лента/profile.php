<?php
// Подключение к базе данных
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'your_database_name';

$db = mysqli_connect($host, $user, $password, $database);

if (!$db) {
    die("Ошибка подключения к базе данных: " . mysqli_connect_error());
}

// Получаем ID пользователя из запроса
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Запрос для получения информации о пользователе
$query = "SELECT * FROM databas WHERE id = $user_id";
$result = mysqli_query($db, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    die("Пользователь не найден.");
}

$user = mysqli_fetch_assoc($result);

// Запрос для получения публикаций пользователя
$query_posts = "SELECT * FROM posts WHERE user_id = $user_id ORDER BY created_at DESC";
$result_posts = mysqli_query($db, $query_posts);

$posts = [];
while ($row = mysqli_fetch_assoc($result_posts)) {
    $posts[] = $row;
}

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль пользователя</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-header img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
        }
        .post {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        .post img {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="profile-header">
        <!-- Аватарка пользователя -->
        <img src="<?php echo htmlspecialchars($user['avatar'] ?? 'default_avatar.jpg'); ?>" alt="Аватарка">
        <!-- Имя пользователя -->
        <h1><?php echo htmlspecialchars($user['login']); ?></h1>
    </div>

    <h2>Публикации пользователя</h2>

    <?php if (empty($posts)): ?>
        <p>У пользователя пока нет публикаций.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                <?php if (!empty($post['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Изображение публикации">
                <?php endif; ?>
                <p><small>Опубликовано: <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?></small></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>