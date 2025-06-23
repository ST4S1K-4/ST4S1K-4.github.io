<?php
session_start();

// Настройки БД
$host = 'localhost';
$dbname = 'itcompany';
$db_username = 'root';
$db_password = '';

$show_success = false; // Флаг для отображения успешной авторизации
$error_message = ''; // Переменная для сообщений об ошибках

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auto'])) {
    try {
        $conn = new mysqli($host, $db_username, $db_password, $dbname);
        if ($conn->connect_error) throw new Exception("Ошибка подключения к базе данных");

        $login = trim($_POST['login']);
        $password = $_POST['password'];

        // Ищем пользователя
        $stmt = $conn->prepare("SELECT id_users, name_user, pass_user, role_user FROM users WHERE name_user = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) throw new Exception("Неверный логин или пароль");

        $user = $result->fetch_assoc();

        // Проверка пароля - password_verify САМА сравнивает обычный пароль с хешем
        if (password_verify($password, $user['pass_user'])) {
            throw new Exception("Неверный пароль");
        }

        // Авторизация успешна
        $_SESSION['id_users'] = $user['id_users'];
        $_SESSION['role'] = $user['role_user'];
        $show_success = true;

        

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    } finally {
        if (isset($conn)) $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link rel="stylesheet" href="autorization.css">
    <style>
        .success-message {
            color: green;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #e6ffe6;
            border: 1px solid #00cc00;
            border-radius: 4px;
        }
        .success-message a {
            color: #0066cc;
            text-decoration: underline;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #ffe6e6;
            border: 1px solid #cc0000;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="POST" class="container">
            <div class="logo">
                <img src="img/Логотип(1).png" alt="logo" class="foto">
            </div>
            <div class="form">
                <h1 class="text">
                    Авторизация
                </h1>
                
                <?php if (!empty($error_message)): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($show_success): ?>
                    <div class="success-message">
                        Авторизация успешна! Перейдите в 
                        <a href="<?php echo ($_SESSION['role'] == 1 ? 'dev_profil.php' : 'users_profile.php'); ?>">
                            профиль
                        </a>
                    </div>
                <?php endif; ?>
                
                <input type="text" class="login" placeholder="Введите имя" name="login" required>
                <input type="password" class="pass" placeholder="Введите пароль" name="password" required><br>
                <button class="enterform" type="submit" name="auto">Войти</button>
                <a href="registration.php" class="registr">Еще нет аккаунта?</a>
            </div>
        </form>
    </div>
</body>
</html>