<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
    <link rel="stylesheet" href="dev_profil.css">
</head>
<body>
    <?php
    session_start();
    
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "itcompany";
    
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $user_id = $_SESSION['id_users'];
        
        // Основные данные разработчика
        $sql_dev = "SELECT first_name, last_name, middle_name, series_number, 
                    birth_date, salary, experience 
                    FROM developers 
                    WHERE id_user = ?";
        $stmt_dev = $conn->prepare($sql_dev);
        $stmt_dev->execute([$user_id]);
        $developer = $stmt_dev->fetch(PDO::FETCH_ASSOC);
        
        // Должность
        $sql_post = "SELECT p.name_post 
                    FROM developers d
                    JOIN post p ON d.id_post = p.id_post
                    WHERE d.id_user = ?";
        $stmt_post = $conn->prepare($sql_post);
        $stmt_post->execute([$user_id]);
        $post = $stmt_post->fetch(PDO::FETCH_ASSOC);
        
        // Отдел
        $sql_dep = "SELECT dep.name_department 
                   FROM developers d
                   JOIN department dep ON d.id_department = dep.id_department
                   WHERE d.id_user = ?";
        $stmt_dep = $conn->prepare($sql_dep);
        $stmt_dep->execute([$user_id]);
        $department = $stmt_dep->fetch(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        die("Ошибка: " . $e->getMessage());
    }
    ?>

    <header class="head_foot">
        <div class="logo" name="logo">
            <img src="img/Логотип(1).png" alt="">
        </div>
        <nav class="navigation">
            <a href="main_dev.php" class="main">Главная</a>
            <a href="#logo" class="main">Профиль</a>
            <a href="project_dev.php" class="main">Мои проекты</a>
        </nav>
    </header>
    <main>
        <div class="container">
            <div class="info_dev">
                <div class="login">
                    <p class="login_p">
                        ФИО
                    </p>
                    <p class="name_dev">
                        <?php 
                        // Формируем ФИО только если данные существуют
                        $full_name = '';
                        if (!empty($developer['last_name'])) $full_name .= $developer['last_name'] . ' ';
                        if (!empty($developer['first_name'])) $full_name .= $developer['first_name'] . ' ';
                        if (!empty($developer['middle_name'])) $full_name .= $developer['middle_name'];
                        
                        echo htmlspecialchars(trim($full_name) ?: 'Не указано');
                        ?>
                    </p>
                </div>
                <div class="passport">
                    <p class="passport_p">
                        Паспортные данные
                    </p>
                    <p class="passport_dev">
                        <?php echo htmlspecialchars($developer['series_number'] ?? 'Не указаны'); ?>
                    </p>
                </div>
                <div class="birth_date">
                    <p class="birth_date_p">
                        Дата рождения                        
                    </p>
                    <p class="date_dev">
                        <?php 
                        if (!empty($developer['birth_date'])) {
                            // Форматируем дату для лучшего отображения
                            $date = DateTime::createFromFormat('Y-m-d', $developer['birth_date']);
                            echo htmlspecialchars($date->format('d.m.Y'));
                        } else {
                            echo 'Не указана';
                        }
                        ?>
                    </p>
                </div>
            </div>
            <div class="position">
                <p class="pos_dev">
                    Должность
                </p>
                <p class="pos_name">
                    <?php echo htmlspecialchars($post['name_post'] ?? 'Не указана'); ?>
                </p>
                <p class="dop_info">
                    Отдел: <?php echo htmlspecialchars($department['name_department'] ?? 'Не указан'); ?><br>
                    Зарплата: <?php 
                        if (isset($developer['salary'])) {
                            echo number_format($developer['salary'], 0, '', ' ') . ' руб.';
                        } else {
                            echo '0 руб.';
                        }
                    ?><br>
                    Опыт работы: <?php 
                        if (isset($developer['experience'])) {
                            echo htmlspecialchars($developer['experience']) . ' ' . 
                                getYearWord($developer['experience']);
                        } else {
                            echo '0 лет';
                        }
                    ?>
                </p>
            </div>
        </div>
        <form action="exit.php" method="POST" style="opacity: 0;">
            <button>Выйти</button>
        </form>
    </main>
    
    <?php
    // Вспомогательная функция для склонения слова "год"
    function getYearWord($number) {
        $lastDigit = $number % 10;
        $lastTwoDigits = $number % 100;
        
        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 19) {
            return 'лет';
        } elseif ($lastDigit == 1) {
            return 'год';
        } elseif ($lastDigit >= 2 && $lastDigit <= 4) {
            return 'года';
        } else {
            return 'лет';
        }
    }
    ?>
</body>
</html>