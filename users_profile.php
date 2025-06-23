<?php
// Включение отображения всех ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Всегда запускаем сессию в самом начале
session_start();

// Параметры подключения к базе данных
$host = 'localhost';
$dbname = 'itcompany';
$username = 'root';
$password = ''; // Ваш пароль, если есть

try {
    // Создаем подключение к базе данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Получаем данные пользователя
    $user_id = $_SESSION['id_users'];
    $stmt = $pdo->prepare("SELECT name_user, email_user FROM users WHERE id_users = :id_user");
    $stmt->bindParam(':id_user', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("Пользователь не найден");
    }

} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
    <link rel="stylesheet" href="user_profil.css">
</head>
<body>
    <header class="head_foot">
        <div class="logo" name="logo">
            <img src="img/Логотип(1).png" alt="">
        </div>
        <nav class="navigation">
            <a href="index.html" class="main">Главная</a>
            <a href="#logo" class="main">Профиль</a>
            <a href="projects_user.php" class="main">Мои проекты</a>
        </nav>
    </header>
    <main>
        <div class="container">
            <div class="info_user">
                <div class="login">
                    <p class="login_p">
                        Имя пользователя
                    </p>
                    <p class="name_user">
                        <?= htmlspecialchars($user['name_user'] ?? 'Не указано') ?>
                    </p>
                    <div class="foto_user">
                        <img src="img/User_Icon.png" alt="User">
                    </div>
                </div>
                <div class="pass">
                    <p class="pass_p">
                        Пароль<br>
                    </p>
                    <div class="foto_pass">
                        <img src="img/More_Horizontal.png" alt="Pass">
                    </div>
                </div>
                <div class="mail">
                    <p class="mail_p">
                        Эл. почта
                    </p>
                    <p class="mail_user">
                        <?= htmlspecialchars($user['email_user'] ?? 'Не указана') ?>
                    </p>
                    <div class="foto_mail">
                        <img src="img/Mail.png" alt="E-Mail">
                    </div>
                </div>
            </div>
            <div class="in_project">
                <button class="projects" type="button" id="myButton">
                    Перейти в проекты
                </button>
            </div>
        </div>
        <form action="exit.php" method="POST" style="opacity: 0;">
            <button>Выйти</button>
        </form>
    </main>

    <script>
        document.getElementById("myButton").addEventListener("click", () => {
            window.location.href = "projects_user.php";
        });
    </script>
</body>
</html>