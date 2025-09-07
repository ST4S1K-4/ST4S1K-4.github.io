<?php
// Включение отображения ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключение к базе данных
$host = 'localhost';
$dbname = 'itcompany';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

// Создание таблицы для аналитики, если её нет
function createAnalyticsTable($pdo)
{
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS site_analytics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                page_url VARCHAR(255) NOT NULL,
                visitor_ip VARCHAR(45) NOT NULL,
                user_agent TEXT,
                visit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                session_id VARCHAR(255),
                referrer VARCHAR(500),
                INDEX idx_visit_date (visit_date),
                INDEX idx_page_url (page_url),
                INDEX idx_session_id (session_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(255) UNIQUE NOT NULL,
                user_id INT,
                start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                page_views INT DEFAULT 1,
                is_active BOOLEAN DEFAULT TRUE,
                INDEX idx_session_id (session_id),
                INDEX idx_user_id (user_id),
                INDEX idx_start_time (start_time)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
    } catch (PDOException $e) {
        // Таблицы уже существуют или ошибка создания
    }
}

// Функция для записи посещения
function logVisit($pdo, $page_url, $session_id = null)
{
    try {
        $visitor_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';

        $stmt = $pdo->prepare("
            INSERT INTO site_analytics (page_url, visitor_ip, user_agent, session_id, referrer) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$page_url, $visitor_ip, $user_agent, $session_id, $referrer]);

        // Обновляем или создаем сессию
        if ($session_id) {
            $stmt = $pdo->prepare("
                INSERT INTO user_sessions (session_id, user_id, page_views) 
                VALUES (?, ?, 1)
                ON DUPLICATE KEY UPDATE 
                page_views = page_views + 1,
                last_activity = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$session_id, $_SESSION['user_id'] ?? null]);
        }
    } catch (PDOException $e) {
        // Ошибка записи аналитики
    }
}

// Функции для получения аналитики
function getVisitsData($pdo)
{
    try {
        // Посещения за последние 30 дней
        $stmt = $pdo->prepare("
            SELECT DATE(visit_date) as date, COUNT(*) as visits
            FROM site_analytics 
            WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(visit_date)
            ORDER BY date
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getProjectsData($pdo)
{
    try {
        // Проекты по месяцам
        $stmt = $pdo->prepare("
            SELECT 
                MONTH(start_date) as month,
                COUNT(*) as projects,
                SUM(CASE WHEN end_date IS NOT NULL AND end_date <= CURDATE() THEN 1 ELSE 0 END) as completed
            FROM project 
            WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY YEAR(start_date), MONTH(start_date)
            ORDER BY YEAR(start_date), MONTH(start_date)
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getTrafficSources($pdo)
{
    try {
        $stmt = $pdo->prepare("
            SELECT 
                CASE 
                    WHEN referrer LIKE '%google%' OR referrer LIKE '%yandex%' THEN 'Поиск'
                    WHEN referrer LIKE '%facebook%' OR referrer LIKE '%vk%' OR referrer LIKE '%instagram%' THEN 'Соцсети'
                    WHEN referrer = '' OR referrer IS NULL THEN 'Прямые'
                    ELSE 'Рефералы'
                END as source,
                COUNT(*) as visits
            FROM site_analytics 
            WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY source
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getUsersData($pdo)
{
    try {
        // Активные пользователи
        $stmt = $pdo->query("
            SELECT 
                COUNT(DISTINCT CASE WHEN last_activity >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN session_id END) as daily_active,
                COUNT(DISTINCT CASE WHEN last_activity >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN session_id END) as weekly_active,
                COUNT(DISTINCT CASE WHEN last_activity >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN session_id END) as monthly_active,
                COUNT(DISTINCT visitor_ip) as unique_visitors
            FROM site_analytics 
            WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['daily_active' => 0, 'weekly_active' => 0, 'monthly_active' => 0, 'unique_visitors' => 0];
    }
}

// Функция для безопасного получения списка таблиц
function getTables($pdo)
{
    try {
        return $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}

function getPrimaryKey($pdo, $table)
{
    try {
        $stmt = $pdo->query("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['Column_name'] : 'id';
    } catch (PDOException $e) {
        return 'id';
    }
}

function getFieldType($type)
{
    if (strpos($type, 'int') !== false)
        return 'number';
    if (strpos($type, 'decimal') !== false || strpos($type, 'float') !== false)
        return 'number';
    if (strpos($type, 'date') !== false)
        return 'date';
    if (strpos($type, 'text') !== false)
        return 'textarea';
    return 'text';
}

// Получаем данные для аналитики
$visitsData = getVisitsData($pdo);
$projectsData = getProjectsData($pdo);
$trafficSources = getTrafficSources($pdo);
$usersData = getUsersData($pdo);

$tables = getTables($pdo);
$error = '';
$success = '';
$current_table = '';
$edit_data = [];

// Обработка действий с таблицами
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['table_action'])) {
        $table = $_POST['table_name'] ?? '';
        $current_table = $table;
        $action = $_POST['table_action'] ?? '';

        if (!in_array($table, $tables)) {
            $error = "Таблица не существует";
        } else {
            try {
                switch ($action) {
                    case 'add':
                        $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
                        $values = [];
                        $placeholders = [];

                        if (isset($_POST['fields']) && is_array($_POST['fields'])) {
                            foreach ($columns as $col) {
                                $colName = $col['Field'];
                                if (isset($_POST['fields'][$colName])) {
                                    $values[$colName] = $_POST['fields'][$colName];
                                    $placeholders[] = ":$colName";
                                }
                            }
                        }

                        if (!empty($values)) {
                            $columns_str = implode(', ', array_keys($values));
                            $placeholders_str = implode(', ', $placeholders);

                            $stmt = $pdo->prepare("INSERT INTO $table ($columns_str) VALUES ($placeholders_str)");
                            $stmt->execute($values);
                            $success = "Запись успешно добавлена";
                        }
                        break;

                    case 'edit':
                        if (isset($_POST['record_id'])) {
                            $id = (int) $_POST['record_id'];
                            $primaryKey = getPrimaryKey($pdo, $table);

                            $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
                            $setParts = [];
                            $values = ['id' => $id];

                            if (isset($_POST['fields']) && is_array($_POST['fields'])) {
                                foreach ($columns as $col) {
                                    $colName = $col['Field'];
                                    if ($colName != $primaryKey && isset($_POST['fields'][$colName])) {
                                        $setParts[] = "$colName = :$colName";
                                        $values[$colName] = $_POST['fields'][$colName];
                                    }
                                }
                            }

                            if (!empty($setParts)) {
                                $setStr = implode(', ', $setParts);
                                $stmt = $pdo->prepare("UPDATE $table SET $setStr WHERE $primaryKey = :id");
                                $stmt->execute($values);
                                $success = "Запись успешно обновлена";
                            }
                        }
                        break;

                    case 'delete':
                        if (isset($_POST['record_id'])) {
                            $id = (int) $_POST['record_id'];
                            $primaryKey = getPrimaryKey($pdo, $table);
                            $stmt = $pdo->prepare("DELETE FROM $table WHERE $primaryKey = ?");
                            $stmt->execute([$id]);
                            $success = "Запись успешно удалена";
                        }
                        break;

                    case 'load_edit':
                        if (isset($_POST['record_id'])) {
                            $id = (int) $_POST['record_id'];
                            $primaryKey = getPrimaryKey($pdo, $table);
                            $stmt = $pdo->prepare("SELECT * FROM $table WHERE $primaryKey = ?");
                            $stmt->execute([$id]);
                            $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
                            if (!$edit_data) {
                                $error = "Запись не найдена";
                            }
                        }
                        break;
                }
            } catch (PDOException $e) {
                $error = "Ошибка: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['request'])) {
        try {
            $request = $_POST['request'];
            $result = [];

            switch ($request) {
                case '1': // Сотрудники отдела
                    $department_id = $_POST['department_id'] ?? 1;
                    $stmt = $pdo->prepare("
                        SELECT d.id_developers, d.first_name, d.last_name, p.name_post, dep.name_department
                        FROM developers d
                        JOIN post p ON d.id_post = p.id_post
                        JOIN department dep ON d.id_department = dep.id_department
                        WHERE d.id_department = ?
                    ");
                    $stmt->execute([$department_id]);
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                case '2': // Разработчики проекта
                    $project_id = $_POST['project_id'] ?? 1;
                    $stmt = $pdo->prepare("
                        SELECT d.id_developers, d.first_name, d.last_name, p.name_post, pr.name_project
                        FROM developers d
                        JOIN project_dev pd ON d.id_developers = pd.id_developer
                        JOIN project pr ON pd.id_project = pr.id_project
                        JOIN post p ON d.id_post = p.id_post
                        WHERE pr.id_project = ?
                    ");
                    $stmt->execute([$project_id]);
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                case '3': // Все проекты
                    $stmt = $pdo->query("
                        SELECT id_project, name_project, start_date, end_date, description
                        FROM project
                        ORDER BY start_date DESC
                    ");
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                case '4': // Проекты клиента
                    $client_id = $_POST['client_id'] ?? 1;
                    $stmt = $pdo->prepare("
                        SELECT p.id_project, p.name_project, p.start_date, p.end_date, u.name_user
                        FROM project p
                        JOIN users u ON p.id_users = u.id_users
                        WHERE u.id_users = ?
                        ORDER BY p.start_date DESC
                    ");
                    $stmt->execute([$client_id]);
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                case '5': // Сотрудники по должности
                    $stmt = $pdo->query("
                        SELECT p.name_post, COUNT(d.id_developers) as count
                        FROM post p
                        LEFT JOIN developers d ON p.id_post = d.id_post
                        GROUP BY p.id_post, p.name_post
                        ORDER BY count DESC
                    ");
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                case '6': // Проекты по году завершения
                    $year = $_POST['year'] ?? date('Y');
                    $stmt = $pdo->prepare("
                        SELECT id_project, name_project, start_date, end_date
                        FROM project
                        WHERE YEAR(end_date) = ?
                        ORDER BY end_date DESC
                    ");
                    $stmt->execute([$year]);
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                case '7': // Отделы и количество сотрудников
                    $stmt = $pdo->query("
                        SELECT d.id_department, d.name_department, 
                               COUNT(de.id_developers) AS employee_count
                        FROM department d
                        LEFT JOIN developers de ON d.id_department = de.id_department
                        GROUP BY d.id_department
                        ORDER BY employee_count DESC
                    ");
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                case '8': // Незавершенные проекты
                    $stmt = $pdo->query("
                        SELECT id_project, name_project, start_date, 
                               DATEDIFF(IFNULL(end_date, CURDATE()), start_date) AS days_in_progress
                        FROM project
                        WHERE end_date IS NULL OR end_date > CURDATE()
                        ORDER BY days_in_progress DESC
                    ");
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                case '9': // Проекты по размеру команды
                    $minDevs = $_POST['min_devs'] ?? 1;
                    $stmt = $pdo->prepare("
                        SELECT pr.id_project, pr.name_project, 
                               COUNT(pd.id_developer) AS developer_count
                        FROM project pr
                        LEFT JOIN project_dev pd ON pr.id_project = pd.id_project
                        GROUP BY pr.id_project
                        HAVING developer_count >= ?
                        ORDER BY developer_count DESC
                    ");
                    $stmt->execute([$minDevs]);
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                case '10': // Сотрудники по стажу
                    $experience = $_POST['experience'] ?? 3;
                    $stmt = $pdo->prepare("
                        SELECT d.id_developers, d.first_name, d.last_name, p.name_post AS position, d.experience
                        FROM developers d
                        JOIN post p ON d.id_post = p.id_post
                        WHERE d.experience >= ?
                        ORDER BY d.experience DESC
                    ");
                    $stmt->execute([$experience]);
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                default:
                    $result = [];
                    break;
            }

            if (!empty($result)) {
                echo "<table><thead><tr>";
                foreach (array_keys($result[0]) as $header) {
                    echo "<th>" . htmlspecialchars($header) . "</th>";
                }
                echo "</tr></thead><tbody>";

                foreach ($result as $row) {
                    echo "<tr>";
                    foreach ($row as $cell) {
                        echo "<td>" . htmlspecialchars($cell) . "</td>";
                    }
                    echo "</tr>";
                }

                echo "</tbody></table>";
            } else {
                echo "<p class='error'>Нет данных по запросу</p>";
            }

        } catch (PDOException $e) {
            echo "<p class='error'>Ошибка выполнения запроса: " . $e->getMessage() . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 15px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .tabs {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .tab {
            flex: 1;
            padding: 15px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .tab:hover {
            background: #e9ecef;
            color: #495057;
        }

        .tab.active {
            background: #4a6cf7;
            color: white;
        }

        .tab-content {
            display: none;
            padding: 20px;
        }

        .tab-content.active {
            display: block;
        }

        header {
            background: #4a6cf7;
            color: white;
            padding: 15px;
            text-align: center;
            margin: -20px -20px 20px -20px;
        }

        h1 {
            font-weight: 600;
            font-size: 20px;
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            padding: 20px;
        }

        .chart-container {
            background: #f9fafc;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease;
        }

        .chart-container:hover {
            transform: translateY(-2px);
        }

        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 12px;
            text-align: center;
        }

        .chart-wrapper {
            position: relative;
            height: 200px;
        }

        .info-text {
            text-align: center;
            margin-top: 12px;
            color: #718096;
            font-size: 13px;
        }

        .positive {
            color: #48bb78;
            font-weight: 600;
        }

        .negative {
            color: #f56565;
            font-weight: 600;
        }

        .form-row {
            margin-bottom: 15px;
        }

        .form-row label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #2d3748;
        }

        .form-row input,
        .form-row select,
        .form-row textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-row textarea {
            min-height: 80px;
            resize: vertical;
        }

        .action-btn {
            background: #4a6cf7;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .action-btn:hover {
            background: #3b5ce6;
        }

        .table-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .table-section h3 {
            margin-bottom: 15px;
            color: #2d3748;
        }

        .table-actions {
            margin-bottom: 20px;
        }

        .action-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .action-form h4 {
            margin-bottom: 15px;
            color: #2d3748;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2d3748;
        }

        .error {
            color: #e53e3e;
            background: #fed7d7;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .success {
            color: #38a169;
            background: #c6f6d5;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 15px;
            }

            .chart-wrapper {
                height: 180px;
            }

            .chart-title {
                font-size: 15px;
            }

            .info-text {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="tabs">
            <button class="tab active" onclick="openTab('statistics')">Метрики</button>
            <button class="tab" onclick="openTab('queries')">Запросы</button>
            <button class="tab" onclick="openTab('database')">Управление БД</button>
        </div>

        <!-- Вкладка статистики -->
        <div id="statistics" class="tab-content active">
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <header>
                <h1>Статистика веб-сайта</h1>
            </header>
            <div class="dashboard">
                <!-- График 1: Посещения -->
                <div class="chart-container">
                    <div class="chart-title">Посещения</div>
                    <div class="chart-wrapper">
                        <canvas id="visitsChart"></canvas>
                    </div>
                    <div class="info-text">
                        Уникальных посетителей: <span class="positive"><?= $usersData['unique_visitors'] ?></span>
                    </div>
                </div>

                <!-- График 2: Проекты -->
                <div class="chart-container">
                    <div class="chart-title">Проекты</div>
                    <div class="chart-wrapper">
                        <canvas id="projectsChart"></canvas>
                    </div>
                    <div class="info-text">
                        Активных проектов: <span class="positive"><?= count($projectsData) ?></span>
                    </div>
                </div>

                <!-- График 3: Источники трафика -->
                <div class="chart-container">
                    <div class="chart-title">Источники трафика</div>
                    <div class="chart-wrapper">
                        <canvas id="trafficChart"></canvas>
                    </div>
                    <div class="info-text">
                        Всего визитов: <span
                            class="positive"><?= array_sum(array_column($trafficSources, 'visits')) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Вкладка запросов -->
        <div id="queries" class="tab-content">
            <h2>Запросы к базе данных</h2>
            <form method="post">
                <div class="form-row">
                    <label>Выберите запрос:
                        <select name="request" required>
                            <option value="">-- Выберите запрос --</option>
                            <option value="1">Сотрудники отдела</option>
                            <option value="2">Разработчики проекта</option>
                            <option value="3">Все проекты</option>
                            <option value="4">Проекты клиента</option>
                            <option value="5">Сотрудники по должности</option>
                            <option value="6">Проекты по году завершения</option>
                            <option value="7">Отделы и количество сотрудников</option>
                            <option value="8">Незавершенные проекты</option>
                            <option value="9">Проекты по размеру команды</option>
                            <option value="10">Сотрудники по стажу</option>
                        </select>
                    </label>
                </div>
                <div id="extra-inputs"></div>
                <button type="submit" class="action-btn">Выполнить запрос</button>
            </form>
        </div>

        <!-- Вкладка управления БД -->
        <div id="database" class="tab-content">
            <h2>Управление базой данных</h2>

            <?php if (!empty($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <p class="success"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <?php foreach ($tables as $table): ?>
                <div class="table-section">
                    <h3><?= htmlspecialchars($table) ?></h3>

                    <div class="table-actions">
                        <button class="action-btn" onclick="showForm('add', '<?= $table ?>')">Добавить запись</button>

                        <form method="post" style="display:inline-block; margin-left: 10px;">
                            <input type="hidden" name="table_name" value="<?= $table ?>">
                            <input type="hidden" name="table_action" value="load_edit">
                            <input type="number" name="record_id" placeholder="ID записи" min="1" required>
                            <button type="submit" class="action-btn">Загрузить для редактирования</button>
                        </form>

                        <form method="post" style="display:inline-block; margin-left: 10px;">
                            <input type="hidden" name="table_name" value="<?= $table ?>">
                            <input type="hidden" name="table_action" value="delete">
                            <input type="number" name="record_id" placeholder="ID записи" min="1" required>
                            <button type="submit" class="action-btn" style="background-color: #dc3545;">Удалить
                                запись</button>
                        </form>
                    </div>

                    <!-- Форма добавления -->
                    <?php
                    $showAddForm = false;
                    if ($current_table === $table && isset($_POST['table_action'])) {
                        if ($_POST['table_action'] === 'add') {
                            $showAddForm = true;
                        }
                    }
                    ?>
                    <div id="add-form-<?= $table ?>" class="action-form"
                        style="<?= $showAddForm ? 'display:block;' : 'display:none;' ?>">
                        <h4>Добавить запись в <?= htmlspecialchars($table) ?></h4>
                        <form method="post">
                            <input type="hidden" name="table_name" value="<?= $table ?>">
                            <input type="hidden" name="table_action" value="add">

                            <?php
                            $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
                            $primaryKey = getPrimaryKey($pdo, $table);

                            foreach ($columns as $col) {
                                if ($col['Field'] != $primaryKey) {
                                    echo '<div class="form-row">';
                                    echo '<label>' . htmlspecialchars($col['Field']) . ' (' . htmlspecialchars($col['Type']) . '):';

                                    $fieldType = getFieldType($col['Type']);
                                    $required = ($col['Null'] == 'NO' && $col['Default'] === null) ? 'required' : '';

                                    if ($fieldType == 'textarea') {
                                        echo '<textarea name="fields[' . $col['Field'] . ']" ' . $required . '></textarea>';
                                    } else {
                                        echo '<input type="' . $fieldType . '" name="fields[' . $col['Field'] . ']" ' . $required . '>';
                                    }

                                    echo '</label>';
                                    echo '</div>';
                                }
                            }
                            ?>

                            <button type="submit" class="action-btn">Добавить</button>
                        </form>
                    </div>

                    <!-- Форма редактирования -->
                    <?php if ($current_table === $table && !empty($edit_data)): ?>
                        <div id="edit-form-<?= $table ?>" class="action-form">
                            <h4>Редактировать запись в <?= htmlspecialchars($table) ?> (ID:
                                <?= htmlspecialchars($edit_data[getPrimaryKey($pdo, $table)]) ?>)
                            </h4>
                            <form method="post">
                                <input type="hidden" name="table_name" value="<?= $table ?>">
                                <input type="hidden" name="table_action" value="edit">
                                <input type="hidden" name="record_id"
                                    value="<?= htmlspecialchars($edit_data[getPrimaryKey($pdo, $table)]) ?>">

                                <?php
                                $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
                                $primaryKey = getPrimaryKey($pdo, $table);

                                foreach ($columns as $col) {
                                    if ($col['Field'] != $primaryKey) {
                                        echo '<div class="form-row">';
                                        echo '<label>' . htmlspecialchars($col['Field']) . ' (' . htmlspecialchars($col['Type']) . '):';

                                        $fieldType = getFieldType($col['Type']);
                                        $required = ($col['Null'] == 'NO' && $col['Default'] === null) ? 'required' : '';
                                        $value = htmlspecialchars($edit_data[$col['Field']] ?? '');

                                        if ($fieldType == 'textarea') {
                                            echo '<textarea name="fields[' . $col['Field'] . ']" ' . $required . '>' . $value . '</textarea>';
                                        } else {
                                            echo '<input type="' . $fieldType . '" name="fields[' . $col['Field'] . ']" value="' . $value . '" ' . $required . '>';
                                        }

                                        echo '</label>';
                                        echo '</div>';
                                    }
                                }
                                ?>

                                <button type="submit" class="action-btn">Сохранить изменения</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- Просмотр данных таблицы -->
                    <?php
                    try {
                        $data = $pdo->query("SELECT * FROM $table LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);

                        if (!empty($data)) {
                            echo "<div style='overflow-x:auto;'>";
                            echo "<table><thead><tr>";
                            foreach (array_keys($data[0]) as $header) {
                                echo "<th>" . htmlspecialchars($header) . "</th>";
                            }
                            echo "</tr></thead><tbody>";

                            foreach ($data as $row) {
                                echo "<tr>";
                                foreach ($row as $cell) {
                                    echo "<td>" . htmlspecialchars($cell ?? '') . "</td>";
                                }
                                echo "</tr>";
                            }

                            echo "</tbody></table>";
                            echo "</div>";
                        } else {
                            echo "<p>Таблица пуста</p>";
                        }
                    } catch (PDOException $e) {
                        echo "<p class='error'>Ошибка загрузки таблицы: " . $e->getMessage() . "</p>";
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function openTab(tabId) {
            // Скрыть все вкладки
            var tabContents = document.querySelectorAll('.tab-content');
            for (var i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }

            // Показать выбранную вкладку
            document.getElementById(tabId).classList.add('active');

            // Обновить активные табы
            var tabs = document.querySelectorAll('.tab');
            for (var i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
            }
            event.currentTarget.classList.add('active');
        }

        function showForm(action, table) {
            // Скрыть все формы
            var forms = document.querySelectorAll('.action-form');
            for (var i = 0; i < forms.length; i++) {
                forms[i].style.display = 'none';
            }

            // Показать нужную форму
            var formId = action + '-form-' + table;
            var form = document.getElementById(formId);
            if (form) {
                form.style.display = 'block';
                form.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        }

        // Динамическое обновление дополнительных полей для запросов
        document.querySelector('select[name="request"]').addEventListener('change', function () {
            var requestId = this.value;
            var extraInputs = document.getElementById('extra-inputs');
            extraInputs.innerHTML = '';

            if (requestId == '1') {
                extraInputs.innerHTML =
                    '<div class="form-row"><label>ID отдела: <input type="number" name="department_id" value="1" min="1"></label></div>';
            } else if (requestId == '2') {
                extraInputs.innerHTML =
                    '<div class="form-row"><label>ID проекта: <input type="number" name="project_id" value="1" min="1"></label></div>';
            } else if (requestId == '4') {
                extraInputs.innerHTML =
                    '<div class="form-row"><label>ID клиента: <input type="number" name="client_id" value="1" min="1"></label></div>';
            } else if (requestId == '6') {
                extraInputs.innerHTML =
                    '<div class="form-row"><label>Год завершения: <input type="number" name="year" value="<?= date('Y') ?>" min="2000"></label></div>';
            } else if (requestId == '9') {
                extraInputs.innerHTML =
                    '<div class="form-row"><label>Мин. разработчиков: <input type="number" name="min_devs" value="1" min="0"></label></div>';
            } else if (requestId == '10') {
                extraInputs.innerHTML =
                    '<div class="form-row"><label>Минимальный стаж (лет): <input type="number" name="experience" value="3" min="0"></label></div>';
            }
        });

        // Инициализация графиков с реальными данными
        document.addEventListener('DOMContentLoaded', function () {
            // Данные для графиков из PHP
            const visitsData = <?= json_encode($visitsData) ?>;
            const projectsData = <?= json_encode($projectsData) ?>;
            const trafficSources = <?= json_encode($trafficSources) ?>;

            // Подготовка данных для графиков
            const visitsLabels = visitsData.map(item => item.date);
            const visitsValues = visitsData.map(item => parseInt(item.visits));

            const projectsLabels = projectsData.map(item => {
                const months = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
                return months[item.month - 1];
            });
            const projectsValues = projectsData.map(item => parseInt(item.projects));
            const completedValues = projectsData.map(item => parseInt(item.completed));

            const trafficLabels = trafficSources.map(item => item.source);
            const trafficValues = trafficSources.map(item => parseInt(item.visits));

            // 1. График посещений
            const visitsCtx = document.getElementById('visitsChart').getContext('2d');
            const visitsChart = new Chart(visitsCtx, {
                type: 'line',
                data: {
                    labels: visitsLabels.length > 0 ? visitsLabels : ['Нет данных'],
                    datasets: [{
                        label: 'Посещения',
                        data: visitsValues.length > 0 ? visitsValues : [0],
                        fill: true,
                        backgroundColor: 'rgba(74, 108, 247, 0.1)',
                        borderColor: 'rgba(74, 108, 247, 1)',
                        tension: 0.3,
                        pointBackgroundColor: 'rgba(74, 108, 247, 1)',
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // 2. График проектов
            const projectsCtx = document.getElementById('projectsChart').getContext('2d');
            const projectsChart = new Chart(projectsCtx, {
                type: 'bar',
                data: {
                    labels: projectsLabels.length > 0 ? projectsLabels : ['Нет данных'],
                    datasets: [{
                        label: 'Всего проектов',
                        data: projectsValues.length > 0 ? projectsValues : [0],
                        backgroundColor: 'rgba(74, 108, 247, 0.8)',
                        borderColor: 'rgba(74, 108, 247, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Завершенные',
                        data: completedValues.length > 0 ? completedValues : [0],
                        backgroundColor: 'rgba(72, 187, 120, 0.8)',
                        borderColor: 'rgba(72, 187, 120, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 10
                                },
                                padding: 6,
                                boxWidth: 8
                            }
                        }
                    }
                }
            });

            // 3. График источников трафика
            const trafficCtx = document.getElementById('trafficChart').getContext('2d');
            const trafficChart = new Chart(trafficCtx, {
                type: 'doughnut',
                data: {
                    labels: trafficLabels.length > 0 ? trafficLabels : ['Нет данных'],
                    datasets: [{
                        data: trafficValues.length > 0 ? trafficValues : [1],
                        backgroundColor: [
                            'rgba(74, 108, 247, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)'
                        ],
                        borderColor: [
                            'rgba(74, 108, 247, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1,
                        hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 10
                                },
                                padding: 6,
                                boxWidth: 8
                            }
                        },
                        tooltip: {
                            bodyFont: {
                                size: 11
                            },
                            callbacks: {
                                label: function (context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.raw / total) * 100).toFixed(1);
                                    return `${context.label}: ${context.raw} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        });
    </script>
</body>

</html>