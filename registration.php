<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="registration.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="img/Логотип(1).png" alt="logo" class="foto">
        </div>
        <form class="form" action="registration.php" method="POST">
        <h1 class="text">
            Регистрация
        </h1>

            <input type="text" class="login" placeholder="Введите имя" name="login">
            <input type="password" class="pass" placeholder="Введите пароль" name="password">
            <input type="email" class="email" placeholder="Введите почту" name="email"><br>
            <button class="enterform" type="submit">Зарегистрироваться</button>
            <a href="autorization.php" class="registr">Уже есть аккаунт?</a>
        </form>
    </div>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = 'localhost';
$dbname = 'itcompany';
$db_username = 'root';
$db_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $errors = [];

    // 1. Проверка на заполнение полей
    if (empty($login)) {
        $errors[] = "Пожалуйста, введите имя.";
    }
    if (empty($pass)) {
        $errors[] = "Пожалуйста, введите пароль.";
    }
    if (empty($email)) {
        $errors[] = "Пожалуйста, введите почту.";
    }  // 2. Валидация email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
        $errors[] = "Неверный формат почты.";
    }

    if (empty($errors)) {
        try {
            $conn = new mysqli($host, $db_username, $db_password, $dbname);

            // Проверка соединения
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Хеширование пароля
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

            // Подготовленный запрос
            $stmt = $conn->prepare("INSERT INTO users (name_user, pass_user, email_user) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $login, $hashed_password, $email);

            if ($stmt->execute()) {
                echo '<div style="color: white;">Вы успешно зарегистрированы!  Перейдите на страницу авторизации.</div>';
            } else {
                echo "" . $stmt->error;
            }

            $stmt->close(); // Закрываем подготовленный запрос
            $conn->close(); // Закрываем соединение с БД

        } catch (Exception $e) {
            die("Ошибка: " . $e->getMessage());
        }
    } else {
        // Отображение ошибок
        foreach ($errors as $error) {
            echo "<div style='color: red;'>$error</div>";
        }
    }
} else {
    echo "";
}
?>
</body>
</html>