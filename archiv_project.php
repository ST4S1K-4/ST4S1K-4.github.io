<?php
session_start();

// Настройки подключения к БД
$host = 'localhost';
$dbname = 'itcompany';
$db_username = 'root';
$db_password = '';

// Получаем проекты пользователя
$projects = [];
try {
    $conn = new mysqli($host, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения к базе данных");
    }
    
    // Предположим, что в таблице project есть поле user_id, связывающее проект с пользователем
    $stmt = $conn->prepare("SELECT name_project, start_date FROM project WHERE id_users = ?");
    $stmt->bind_param("i", $_SESSION['id_users']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
} finally {
    if (isset($conn)) $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Архив проектов</title>
    <link rel="stylesheet" href="archiv_project.css">
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
    <div class="container">
        <div class="text"> 
            Архив ваших проектов
        </div>
        <div class="projects">
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (empty($projects)): ?>
                <p style="margin-left: 20px;">У вас пока нет проектов</p>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <div class="project">
                        <h3><?php echo htmlspecialchars($project['name_project']); ?></h3>
                        <p>Дата начала: <?php echo htmlspecialchars($project['start_date']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>