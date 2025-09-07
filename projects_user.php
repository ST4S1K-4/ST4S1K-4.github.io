<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои проекты</title>
    <link rel="stylesheet" href="projects_user.css">
</head>

<body>
    <header class="head_foot">
        <div class="logo" name="logo">
            <img src="img/Логотип(1).png" alt="">
        </div>
        <nav class="navigation">
            <a href="index.php" class="main">Главная</a>
            <a href="users_profile.php" class="main">Профиль</a>
            <a href="#logo" class="main">Мои проекты</a>
        </nav>
    </header>

    <div class="text">
        <p class="management">
            Управление вашими проектами
        </p>
    </div>

    <div class="projects_btn">
        <button class="new_project" type="button" id="myButton">
            Новый проект
        </button>
        <button class="new_project" type="button" id="archiv">
            Архив проектов
        </button>
    </div>

    <script>
        document.getElementById("myButton").addEventListener("click", () => {
            window.location.href = "new_project.php";
        });
        document.getElementById("archiv").addEventListener("click", () => {
            window.location.href = "archiv_project.php";
        });
    </script>
</body>

</html>