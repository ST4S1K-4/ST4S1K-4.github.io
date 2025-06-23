<?php
session_start();

// Подключение к базе данных
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "itcompany";

// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Обработка взятия проекта
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['project_id'])) {
    $project_id = intval($_POST['project_id']);
    $user_id = $_SESSION['id_users'];

    // Обновляем проект, устанавливая id пользователя
    $update_sql = "UPDATE project SET id_users = ? WHERE id_project = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $user_id, $project_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Проект успешно взят в работу!');</script>";
        // Обновляем страницу, чтобы показать изменения
        echo "<script>setTimeout(function(){ location.reload(); }, 1000);</script>";
    } else {
        echo "<script>alert('Ошибка при взятии проекта: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Запрос для получения проектов с id_users = NULL
$sql = "SELECT id_project, name_project, start_date, end_date, description, file_project, file_name FROM project WHERE id_users IS NULL";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная</title>
    <link rel="stylesheet" href="main_dev.css">
</head>
<body>
    <header class="head_foot">
        <div class="logo" name="logo">
            <img src="img/Логотип(1).png" alt="">
        </div>
        <nav class="navigation">
            <a href="#logo" class="main">Главная</a>
            <a href="dev_profil.php" class="main">Профиль</a>
            <a href="project_dev.php" class="main">Мои проекты</a>
        </nav>
    </header>
    <main class="container">
        <div class="head">
            Новые проекты
        </div>
        
        <?php
        if ($result->num_rows > 0) {
            // Вывод данных каждого проекта
            while($row = $result->fetch_assoc()) {
                echo '<div class="content">';
                echo '<div class="num">';
                echo 'Проект: ' . htmlspecialchars($row["name_project"]) . '<br>';
                if (!empty($row["file_project"])) {
                    echo '<embed src="' . htmlspecialchars($row["file_project"]) . '" type="application/pdf" width="300" height="200">';
                }
                echo '</div>';
                echo '<div class="date">';
                echo '<p class="start">';
                echo 'Начало работ: ' . htmlspecialchars($row["start_date"]);
                echo '</p>';
                echo '<p class="finish">';
                echo 'Конец работ: ' . htmlspecialchars($row["end_date"]);
                echo '</p>';
                echo '<br>';
                echo '<p class="deskription">';
                echo 'Описание:<br>' . htmlspecialchars($row["description"]);
                echo '</p>';
                echo '</div>';
                // Форма для взятия проекта
                echo '<form method="POST" style="display:inline;">';
                echo '<input type="hidden" name="project_id" value="' . $row["id_project"] . '">';
                echo '<button type="submit" class="take">Взять в работу</button>';
                echo '</form>';
                echo '</div>';
            }
        } else {
            echo '<div class="content">Нет доступных проектов</div>';
        }
        
        $conn->close();
        ?>

        <form action="exit.php" method="POST" style="opacity: 0;">
            <button>Выйти</button>
        </form>
    </main>
</body>
</html>