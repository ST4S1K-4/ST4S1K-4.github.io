<?php
session_start();

// Подключение к базе данных
$db = new mysqli('127.0.0.1', 'root', '', 'itcompany');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Обработка сдачи проекта
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['project_id'])) {
    $userId = $_SESSION['id_users'];
    $projectId = $_POST['project_id'];

    // Обновляем проект, устанавливая id_users в NULL
    $updateQuery = "UPDATE project SET id_users = NULL WHERE id_project = ? AND id_users = ?";
    
    $stmt = $db->prepare($updateQuery);
    $stmt->bind_param("ii", $projectId, $userId);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $successMessage = "Проект успешно сдан!";
    } else {
        $errorMessage = "Ошибка при сдаче проекта: проект не найден или вы не являетесь его исполнителем";
    }
    $stmt->close();
}

// Получаем ID текущего пользователя
$userId = $_SESSION['id_users'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои проекты</title>
    <link rel="stylesheet" href="project_dev.css">
    <style>
        .projects {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .project-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-radius: 20px;
            margin: 10px 40px;
            background-color: white;
            color: black;
        }
        .project-info {
            flex-grow: 1;
        }
        .project-actions {
            margin-left: 15px;
        }
    </style>
</head>
<body>
    <header class="head_foot">
        <div class="logo" name="logo">
            <img src="img/Логотип(1).png" alt="Логотип компании">
        </div>
        <nav class="navigation">
            <a href="main_dev.php" class="main">Главная</a>
            <a href="dev_profil.php" class="main">Профиль</a>
            <a href="#logo" class="main">Мои проекты</a>
        </nav>
    </header>
    <div class="container">
        <div class="text">Мои проекты</div>
        
        <?php if (isset($successMessage)): ?>
            <div class="message success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        
        <?php if (isset($errorMessage)): ?>
            <div class="message error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <div class="projects">
            <?php
            // Получаем проекты разработчика
            $query = "SELECT 
                id_project, 
                name_project, 
                start_date
            FROM project
            WHERE id_users = ?";
            
            if ($stmt = $db->prepare($query)) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="project-item">';
                        echo '<div class="project-info">';
                        echo '<p class="project-title">' . htmlspecialchars($row['name_project']) . '</p>';
                        echo '<p class="project-date">Начало: ' . htmlspecialchars($row['start_date']) . '</p>';
                        echo '</div>';
                        
                        echo '<div class="project-actions">';
                        echo '<form method="POST">';
                        echo '<input type="hidden" name="project_id" value="' . $row['id_project'] . '">';
                        echo '<button type="submit" class="send_project">Сдать проект</button>';
                        echo '</form>';
                        echo '</div>';
                        
                        echo '</div>'; // закрываем .project-item
                    }
                } else {
                    echo '<p class="no-projects">У вас нет текущих проектов</p>';
                }
                
                $stmt->close();
            } else {
                echo '<p class="error">Ошибка подготовки запроса</p>';
            }
            
            $db->close();
            ?>
        </div>
    </div>
    <form action="exit.php" method="POST" style="opacity: 0;">
        <button>Выйти</button>
    </form>
</body>
</html>