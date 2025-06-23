<?php
session_start();

// Проверка авторизации (перенаправление, если пользователь не авторизован)
if (!isset($_SESSION['id_users'])) {
    header("Location: autorization.php"); // Замените на страницу авторизации
    exit();
}

// Настройки подключения к БД
$host = 'localhost';
$dbname = 'itcompany';
$db_username = 'root';
$db_password = '';

$error_message = '';
$success_message = '';

// Получение user_id из сессии
$user_id = $_SESSION['id_users']; // Получаем id_users из сессии

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Проверка CSRF-токена (рекомендуется добавить в реальном проекте)
        // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        //     throw new Exception("Ошибка безопасности");
        // }

        // Получение данных из формы
        $name_project = trim($_POST['name_project'] ?? '');
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $comment_p = trim($_POST['comment_p'] ?? '');

        // Валидация данных
        if (empty($name_project)) {
            throw new Exception("Поле 'Название проекта' обязательно для заполнения.");
        }
        if (empty($start_date)) {
            throw new Exception("Поле 'Дата начала' обязательно для заполнения.");
        }
        if (empty($end_date)) {
            throw new Exception("Поле 'Дата окончания' обязательно для заполнения.");
        }
        if (strtotime($start_date) > strtotime($end_date)) {
            throw new Exception("Дата окончания должна быть позже даты начала");
        }

        // Обработка загружаемого файла
        $file_project = '';
        $file_name = '';

        if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] == UPLOAD_ERR_OK) {
            // Настройки загрузки
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Генерация уникального имени файла
            $file_extension = pathinfo($_FILES['project_file']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('project_', true) . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;

            // Проверка типа файла (можно расширить)
            $allowed_types = ['pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'jpg', 'png'];
            if (!in_array(strtolower($file_extension), $allowed_types)) {
                throw new Exception("Недопустимый тип файла");
            }

            // Перемещение файла
            if (!move_uploaded_file($_FILES['project_file']['tmp_name'], $file_path)) {
                throw new Exception("Ошибка при загрузке файла");
            }

            $file_project = $file_path;
        }

        // Подключение к БД и сохранение проекта
        $conn = new mysqli($host, $db_username, $db_password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Ошибка подключения к базе данных");
        }

        // Подготовленный запрос
        $stmt = $conn->prepare("INSERT INTO project (name_project, start_date, end_date, description, comment_p, file_project, file_name, id_users)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssi", $name_project, $start_date, $end_date, $description, $comment_p, $file_project, $file_name, $user_id);

        if (!$stmt->execute()) {
            throw new Exception("Ошибка при сохранении проекта: " . $stmt->error);
        }

        $success_message = "Проект успешно создан!";

        // Очистка формы после успешного сохранения
        $name_project = $start_date = $end_date = $description = $comment_p = '';

        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
} else {
    // Генерация CSRF-токена (рекомендуется добавить в реальном проекте)
    // $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новый проект</title>
    <link rel="stylesheet" href="new_project.css">
    <style>
        .error-message {
            color: red;
            margin: 10px 0;
            padding: 10px;
            background-color: #ffe6e6;
            border: 1px solid #cc0000;
            border-radius: 4px;
        }
        .success-message {
            color: green;
            margin: 10px 0;
            padding: 10px;
            background-color: #e6ffe6;
            border: 1px solid #00cc00;
            border-radius: 4px;
        }
        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            margin: -1px;
            border: 0;
            padding: 0;
            clip: rect(0 0 0 0);
            overflow: hidden;
        }
        .custom-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff; /* Пример цвета */
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .custom-button:hover {
            background-color: #0056b3; /* Цвет при наведении */
        }
        .file-name {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <header class="head_foot">
        <div class="logo" name="logo">
            <img src="img/Логотип(1).png" alt="">
        </div>
        <nav class="navigation">
            <a href="index.html" class="main">Главная</a>
            <a href="users_profile.php" class="main">Профиль</a>
            <a href="projects_user.php" class="main">Мои проекты</a>
        </nav>
    </header>
    <main class="container">
        <p class="create_text">
            Создать новый проект
        </p>
        <div class="create_project">
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <form action="new_project.php" method="POST" class="create_form" enctype="multipart/form-data">
                <div class="create_input">
                    <div class="input">
                        Название проекта*
                        <input type="text" name="name_project" value="<?php echo htmlspecialchars($name_project); ?>" required>
                    </div>
                    <div class="input">
                        Дата начала*
                        <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required>
                    </div>
                    <div class="input">
                        Дата окончания*
                        <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" required>
                    </div>
                    <div class="input">
                        Описание проекта*
                        <textarea name="description" rows="5" cols="50" required><?php echo htmlspecialchars($description); ?></textarea>
                    </div>
                    <div class="input" id="comm">
                        Комментарий
                        <textarea name="comment_p" rows="5" cols="50"><?php echo htmlspecialchars($comment_p); ?></textarea>
                    </div>
                </div>
                <div class="file-upload">
                    <label for="real-input" class="custom-button">Выбрать файл</label>
                    <input type="file" id="real-input" name="project_file" class="visually-hidden">
                    <span id="file-name" class="file-name">Файл не выбран</span>
                </div>
                <div class="send_form" style="margin-top: 30px;">
                    <button class="send" type="submit">
                        Отправить
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const realInput = document.getElementById('real-input');
        const fileName = document.getElementById('file-name');

        if (realInput && fileName) {
            realInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    if (this.files.length > 1) {
                        fileName.textContent = `${this.files.length} файлов выбрано`;
                    } else {
                        fileName.textContent = this.files[0].name;
                    }
                } else {
                    fileName.textContent = 'Файл не выбран';
                }
            });
        } else {
            console.error('Элементы не найдены! Проверьте ID элементов.');
        }
    });
    </script>
</body>
</html>