<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodePrime - Разработка веб-сайтов и приложений</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="main-container">
        <header class="header">
            <img src="img/mainbg.png" class="header-bg" alt="Background">
            <div class="header-content">
                <div class="header-grid">
                    <div class="logo-column">
                        <img src="img/Логотип(1).png" class="logo" alt="CodePrime Logo">
                    </div>
                    <div class="nav-column"> 
                        <nav class="navigation">
                            <a href="#advantages" class="nav-link nav-link-grow">Преимущества</a>
                            <a href="#about" class="nav-link">О нас</a>
                            <a href="#faq" class="nav-link">FAQ</a>
                            <a href="autorization.php" class="nav-link">Войти</a>
                        </nav>
                    </div>
                </div>
            </div>
            <h1 class="main-title">CodePrime</h1>
        </header>

        <section class="services-section">
            <div class="services-container">
                <h2 class="section-title">Услуги</h2>
                <div class="services-grid">
                    <div class="service-column">
                        <article class="service-card">
                            <img src="img/card-img.png" class="service-image" alt="Разработка лендингов">
                            <div class="service-body">
                                <h3 class="service-title">Разработка лендингов</h3>
                                <p class="service-description">
                                    Наша команда занимается разработкой лендингов более 10 лет.Разработка лендингов занимает от 2 до 7 дней.
                                </p>
                            </div>
                            <footer class="service-footer">
                                <div class="service-price-container">
                                    <div class="service-price-content">
                                        <div class="service-avatar">
                                            <img src="img/ruble.png" class="avatar-image" alt="Price indicator">
                                        </div>
                                        <div class="service-price">от 2 000 до 10 000</div>
                                    </div>
                                </div>
                            </footer>
                        </article>
                    </div>
                    <div class="service-column">
                        <article class="service-card">
                            <img src="img/card-img.png" class="service-image" alt="Разработка сайтов">
                            <div class="service-body">
                                <h3 class="service-title">Разработка сайтов</h3>
                                <p class="service-description">
                                    Наша команда занимается разработкой сайтов более 5 лет.Разработка сайтов занимает от 5 до 30 дней.
                                </p>
                            </div>
                            <footer class="service-footer">
                                <div class="service-price-container">
                                    <div class="service-price-content">
                                        <div class="service-avatar">
                                            <img src="img/ruble.png" class="avatar-image" alt="Price indicator">
                                        </div>
                                        <div class="service-price">от 10 000 до 300 000</div>
                                    </div>
                                </div>
                            </footer>
                        </article>
                    </div>
                    <div class="service-column">
                        <article class="service-card">
                            <img src="img/card-img.png" class="service-image service-image-wide" alt="Разработка веб-приложений">
                            <div class="service-body">
                                <h3 class="service-title">Разработка веб-приложений</h3>
                                <p class="service-description">
                                    Наша команда занимается разработкой веб-приложений более 3 лет.Разработка лендингов занимает от 10 до 60 дней.
                                </p>
                            </div>
                            <footer class="service-footer">
                                <div class="service-price-container">
                                    <div class="service-price-content">
                                        <div class="service-avatar">
                                            <img src="img/ruble.png" class="avatar-image" alt="Price indicator">
                                        </div>
                                        <div class="service-price">от 15 000 до 1 000 000</div>
                                    </div>
                                </div>
                            </footer>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="advantages-section" id="advantages">
            <h2 class="advantages-title">Почему стоит выбрать нас?</h2>

            <article class="advantage-item">
                <img src="img/okay.jpg" class="advantage-icon" alt="Fast start icon">
                <div class="advantage-content">
                    <span class="advantage-heading">Быстрый старт и внедрение:</span>
                    <br><br>
                    <span class="advantage-text">
                        Мы создаем свои проекты с 0 за достаточно короткие промежутки
                        времени и внедряем наши технологии быстрее конкурентов.
                    </span>
                </div>
                <img src="img/rocket.png" class="advantage-decoration" alt="Decorative element">
            </article>

            <article class="advantage-item advantage-item-with-decoration">
                <div class="advantage-main">
                    <img src="img/okay.jpg" class="advantage-icon" alt="Team icon">
                    <div class="advantage-content">
                        <span class="advantage-heading">Опытная команда разработчиков:</span>
                        <br><br>
                        <span class="advantage-text">
                            Наши разработчики - сильная и сплоченная команда, это опытные
                            люди, которые имеют опыт коммерческой разработки более 5 лет.
                        </span>
                    </div>
                </div>
                <img src="img/users_group.jpg" class="advantage-decoration" alt="Decorative element">
            </article>

            <article class="advantage-item advantage-item-with-decoration">
                <div class="advantage-main">
                    <img src="img/okay.jpg" class="advantage-icon" alt="Timeline icon">
                    <div class="advantage-content">
                        <span class="advantage-heading">Соблюдение сроков проектов:</span>
                        <br><br>
                        <span class="advantage-text">
                            Мы выполняем 99,9% проектов в назначенные сроки, четкое
                            соблюдение дедлайнов и сдача проектов в назначенное время.
                        </span>
                    </div>
                </div>
                <img src="img/calendar.jpg" class="advantage-decoration" alt="Decorative element">
            </article>
        </section>

        <section class="about-section" id="about">
            <h2 class="about-title">Информация о нас</h2>

            <div class="about-content">
                <div class="about-grid">
                    <div class="founder-column">
                        <div class="founder-info">
                            <img src="img/avatar.png" class="founder-image" alt="Founder Chubkov S.A.">
                            <p class="founder-text">
                                Основатель компании<br>
                                Чубков С.А
                            </p>
                        </div>
                    </div>
                    <div class="story-column">
                        <div class="story-text">
                            <span class="story-quote">"Код, Который Заводит Бизнес"</span>
                            <br> «Всё началось с пустого экрана и идеи: клиенты тонут в
                            шаблонных сайтах, а крутые продукты теряются из-за кривого UX.
                            <br>
                            Мы собрали команду — не просто программистов, а фанатов своего
                            дела. Тех, кто видит код как искусство, а интерфейс — как диалог
                            с клиентом.
                            <br>
                            Сегодня мы не "делаем сайты". Мы запускаем двигатели продаж:
                            <br> Превращаем идеи в работающие веб-приложения
                            <br> Собираем корпоративные сайты, которые хочется листать
                            <br> Создаём лендинги, где каждая кнопка — это "Хочу!"
                            <br>
                        </div>
                    </div>
                </div>
            </div>

            <blockquote class="company-principle">
                Наш принцип: если это не цепляет глаз, не взрывает конверсию и не
                решает проблем — это не CodePrime.
            </blockquote>
        </section>

        <section class="faq-section" id="faq">
            <h2 class="faq-title">FAQ</h2>

            <article class="faq-item faq-item-light">
                <span class="faq-question">
                    1. Как быстро вы сможете начать работу над нашим проектом?
                </span>
                <br><br><br><br>
                <span class="faq-answer">
                    Ответ: Очень быстро! После первичного обсуждения и подписания
                    документов мы готовы приступить к работе в течение 1-3 рабочих дней.
                    Нам не нужны долгие раскачки.
                </span>
            </article>

            <article class="faq-item faq-item-bold">
                2. Насколько опытны ваши разработчики?
                <br><br><br>
                <span class="faq-answer-light">
                    Ответ: У нас работают проверенные профессионалы (от 3+ лет
                    коммерческого опыта). Каждый разработчик прошел строгий отбор и имеет
                    реальный опыт в создании проектов, аналогичных вашему. Мы подбираем
                    команду под ваши конкретные требования.
                </span>
            </article>

            <article class="faq-item faq-item-bold faq-item-wide">
                3. Гарантируете ли вы соблюдение сроков?
                <br><br>
                <span class="faq-answer-light">
                    Ответ: Да, гарантируем. Соблюдение сроков — наш приоритет №1. Мы:
                </span>
                <br>
                <span class="faq-answer-light">
                    Четко фиксируем этапы и дедлайны в договоре.
                </span>
                <br>
                <span class="faq-answer-light">
                    Регулярно (еженедельно) отчитываемся о прогрессе.
                </span>
                <br>
                <span class="faq-answer-light">
                    Оперативно сообщаем о любых возможных задержках и сразу предлагаем
                    решение.
                </span>
            </article>
        </section>

        <footer class="footer">
            <div class="footer-content">
                <div class="footer-links">
                    <div class="footer-column">
                        <h3 class="footer-heading">Product</h3>
                        <nav class="footer-nav">
                            <a href="#top" class="footer-link footer-link-indent">Вверх страницы</a>
                            <a href="#services" class="footer-link">Услуги</a>
                            <a href="#advantages" class="footer-link">Преимущества</a>
                        </nav>
                        <img src="img/Логотип(1).png" class="footer-logo" alt="Footer logo">
                    </div>
                    <div class="footer-column">
                        <h3 class="footer-heading">Information</h3>
                        <nav class="footer-nav">
                            <a href="#faq" class="footer-link">FAQ</a>
                        </nav>
                    </div>
                    <div class="footer-column">
                        <h3 class="footer-heading">Company</h3>
                        <nav class="footer-nav">
                            <a href="#about" class="footer-link">О нас</a>
                        </nav>
                    </div>
                </div>
<style>
        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .popup-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 300px;
        }

        .close-button {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 20px;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 10px;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 3px;
        }

        input[type="email"],
        textarea {
            width: 100%;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .message {
            margin-top: 10px;
            font-size: 14px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }
    </style>
                <div class="footer-social">
                    <a href="https://web.telegram.org/k/#@ST4S1K_VIP_001" aria-label="Social media link">
                        <img src="img/tg.jpg" class="social-icon" alt="Social media icon">
                    </a>
                    <button onclick="showEmailPopup()" style="border: none; background-color: white;" class="btn_mail">
                        <a href="#social-icon"><img src="img/msg.jpg" class="social-icon" alt="Social media icon"></a>
                    </button>
                    <a href="tel: +79680114801">
                        <img src="img/phone.jpg" class="social-icon" alt="Social media icon">
                    </a>
<div id="emailPopup" class="popup">
    <div class="popup-content">
        <span class="close-button" onclick="hideEmailPopup()">×</span>
        <h2>Напишите нам</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="email">Ваш Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="subject">Тема письма:</label>
                <input type="text" id="subject" name="subject" required>
                <label for="message">Сообщение:</label>
                <textarea id="message" name="message" rows="3" required></textarea>
            </div>
            <button type="submit" name="submit">Отправить</button>
        </form>
        <div id="form-message" class="message">
            <?php //чекаем письмо по адресу http://localhost:8025/ обязательно запускаем mailhog
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {

                $to = "st4s1k@internet.ru";
                $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
                $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
                $fromEmail = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

                if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                    echo "<p class='error'>Некорректный email адрес</p>";
                    exit;
                }

                $headers = "From: $fromEmail\r\n";
                $headers .= "Reply-To: $fromEmail\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                ini_set("SMTP", "localhost");
                ini_set("smtp_port", "1025");

                try {
                    $result = mail($to, $subject, $message, $headers);
                } catch (Exception $e) {
                    echo "<p class='error'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
            ?>
        </div>
    </div>
</div>

<style>
    .error {
        color: red;
        margin-top: 10px;
    }
    .success {
        color: green;
        margin-top: 10px;
    }
</style>


    <script>
        function showEmailPopup() {
            document.getElementById('emailPopup').style.display = 'block';
        }

        function hideEmailPopup() {
            document.getElementById('emailPopup').style.display = 'none';
        }

        window.onclick = function(event) {
            var popup = document.getElementById('emailPopup');
            if (event.target == popup) {
                popup.style.display = "none";
            }
        }
    </script>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>