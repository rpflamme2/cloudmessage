<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'avtorizac';
$db = mysqli_connect($host, $user, $password, $database);
$redirect_url = '../Вход/vhod.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
    <style>
        /* Стили для фона */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #4e54c8;
            background: -webkit-linear-gradient(to left, #8f94fb, #4e54c8);
            background: linear-gradient(to left, #8f94fb, #4e54c8);
            overflow: hidden;
            position: relative;
        }
        /* Стили для анимации фона */
        .area {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none; /* Это предотвратит взаимодействие с квадратиками */
            z-index: 0; /* Отправляем фоновую анимацию за форму */
        }

        .circles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .circles li {
            position: absolute;
            display: block;
            list-style: none;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.2);
            animation: animate 25s linear infinite;
            bottom: -150px;
        }

        .circles li:nth-child(1) { left: 25%; width: 80px; height: 80px; animation-delay: 0s; }
        .circles li:nth-child(2) { left: 10%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 12s; }
        .circles li:nth-child(3) { left: 70%; width: 20px; height: 20px; animation-delay: 4s; }
        .circles li:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s; }
        .circles li:nth-child(5) { left: 65%; width: 20px; height: 20px; animation-delay: 0s; }
        .circles li:nth-child(6) { left: 75%; width: 110px; height: 110px; animation-delay: 3s; }
        .circles li:nth-child(7) { left: 35%; width: 150px; height: 150px; animation-delay: 7s; }
        .circles li:nth-child(8) { left: 50%; width: 25px; height: 25px; animation-delay: 15s; animation-duration: 45s; }
        .circles li:nth-child(9) { left: 20%; width: 15px; height: 15px; animation-delay: 2s; animation-duration: 35s; }
        .circles li:nth-child(10) { left: 85%; width: 150px; height: 150px; animation-delay: 0s; animation-duration: 11s; }

        @keyframes animate {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; border-radius: 0; }
            100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; border-radius: 50%; }
        }
        a{
            color: black;
            position: relative;

            font-size: 20px;
        }
        .container {
            position: relative;
            z-index: 1;
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
    </style>
</head>
<body>
</body>
</html>

<?php
// Проверяем, была ли отправлена форма
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $log = trim(mysqli_real_escape_string($db, $_POST['login']));
    $par = trim(mysqli_real_escape_string($db, $_POST['parol']));
    $poch = trim(mysqli_real_escape_string($db, $_POST['pochta']));
    // Проверяем, что все данные были получены корректно
    if (!empty($log) && !empty($par) && !empty($poch)) {
        // Выполняем запрос
        $result = mysqli_query($db, "INSERT INTO databas (id, email, login, password) VALUES (NULL, '$poch', '$log', '$par')");

        if ($result) {
            echo '<div class="container"><h2>Данные успешно сохранены!</h2></div>';
            echo '<meta http-equiv="refresh" content="0.0001;url=' . $redirect_url . '" />';
        } else {
            echo "<h2>Ошибка при выполнении запроса:</h2> " . mysqli_error($db);
        }
    } else {
        echo '<div class="container"><h2>Ошибка: Не удалось получить все необходимые данные.</h2><br>
        <a href="reg.html">Вернуться обратно</a></div>';

    }
}
?>
