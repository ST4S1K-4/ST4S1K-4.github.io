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

// Функция для безопасного получения списка таблиц
function getTables($pdo) {
    try {
        return $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}

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
                            $id = (int)$_POST['record_id'];
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
                            $id = (int)$_POST['record_id'];
                            $primaryKey = getPrimaryKey($pdo, $table);
                            $stmt = $pdo->prepare("DELETE FROM $table WHERE $primaryKey = ?");
                            $stmt->execute([$id]);
                            $success = "Запись успешно удалена";
                        }
                        break;
                        
                    case 'load_edit':
                        if (isset($_POST['record_id'])) {
                            $id = (int)$_POST['record_id'];
                            $primaryKey = getPrimaryKey($pdo, $table);
                            $stmt = $pdo->prepare("SELECT * FROM $table WHERE $primaryKey = ?");
                            $stmt->execute([$id]);
                            $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
                        }
                        break;
                }
            } catch (PDOException $e) {
                $error = "Ошибка: " . $e->getMessage();
            }
        }
    }
}

// Функция для получения первичного ключа таблицы
function getPrimaryKey($pdo, $table) {
    try {
        $stmt = $pdo->query("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'");
        $keyData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $keyData['Column_name'] ?? 'id';
    } catch (PDOException $e) {
        return 'id';
    }
}

// Функция для определения типа поля
function getFieldType($type) {
    $type = strtolower($type);
    if (strpos($type, 'int') !== false) return 'number';
    if (strpos($type, 'date') !== false || strpos($type, 'time') !== false) return 'datetime-local';
    if (strpos($type, 'text') !== false) return 'textarea';
    return 'text';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель CodePrime</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
        .tabs { display: flex; background: #4361ee; }
        .tab { padding: 15px 25px; cursor: pointer; color: white; }
        .tab.active { background: #3a56d4; font-weight: bold; }
        .tab-content { display: none; padding: 20px; background: white; margin: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .tab-content.active { display: block; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; position: sticky; top: 0; }
        .action-btn { margin: 5px; padding: 8px 15px; background: #4361ee; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .action-btn:hover { background: #3a56d4; }
        select, input, button, textarea { padding: 10px; margin: 5px 0; width: 100%; box-sizing: border-box; }
        .form-container { background: #f9f9f9; padding: 20px; border-radius: 5px; }
        .error { color: #dc3545; }
        .success { color: #28a745; }
        .action-form { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .form-row { margin-bottom: 10px; }
        .form-row label { display: block; margin-bottom: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="tabs">
        <div class="tab active" onclick="openTab('queries')">Запросы</div>
        <div class="tab" onclick="openTab('database')">Управление БД</div>
    </div>

    <!-- Вкладка запросов -->
    <div id="queries" class="tab-content active">
        <div class="form-container">
            <form method="post">
                <select name="request" required>
                    <option value="" disabled selected>Выберите запрос</option>
                    <option value="1">1. Сотрудники отдела</option>
                    <option value="2">2. Сотрудники проекта</option>
                    <option value="3">3. Клиенты и их проекты</option>
                    <option value="4">4. Проекты по клиенту</option>
                    <option value="5">5. Сотрудники по зарплате</option>
                    <option value="6">6. Проекты по году завершения</option>
                    <option value="7">7. Отделы и количество сотрудников</option>
                    <option value="8">8. Незавершенные проекты</option>
                    <option value="9">9. Проекты по размеру команды</option>
                    <option value="10">10. Сотрудники по стажу</option>
                </select>

                <div id="extra-inputs">
                    <?php if (isset($_POST['request'])): ?>
                        <?php $request = $_POST['request']; ?>
                        <?php if ($request == '1'): ?>
                            <div class="form-row">
                                <label>ID отдела: <input type="number" name="department_id" value="1" min="1"></label>
                            </div>
                        <?php elseif ($request == '2'): ?>
                            <div class="form-row">
                                <label>ID проекта: <input type="number" name="project_id" value="1" min="1"></label>
                            </div>
                        <?php elseif ($request == '4'): ?>
                            <div class="form-row">
                                <label>ID клиента: <input type="number" name="client_id" value="1" min="1"></label>
                            </div>
                        <?php elseif ($request == '6'): ?>
                            <div class="form-row">
                                <label>Год завершения: <input type="number" name="year" value="<?= date('Y') ?>" min="2000"></label>
                            </div>
                        <?php elseif ($request == '9'): ?>
                            <div class="form-row">
                                <label>Мин. разработчиков: <input type="number" name="min_devs" value="1" min="0"></label>
                            </div>
                        <?php elseif ($request == '10'): ?>
                            <div class="form-row">
                                <label>Минимальный стаж (лет): <input type="number" name="experience" value="3" min="0"></label>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <button type="submit" name="do_request" class="action-btn">Выполнить</button>
            </form>
        </div>

        <div class="result">
            <?php
            if (isset($_POST['do_request'])) {
                $requestId = $_POST['request'];
                
                try {
                    switch ($requestId) {
                        case '1': // Сотрудники отдела
                            $departmentId = $_POST['department_id'] ?? 1;
                            $stmt = $pdo->prepare("
                                SELECT d.id_developers, d.first_name, d.last_name, p.name_post AS position, d.salary, dept.name_department
                                FROM developers d
                                JOIN department dept ON d.id_department = dept.id_department
                                JOIN post p ON d.id_post = p.id_post
                                WHERE dept.id_department = ?
                            ");
                            $stmt->execute([$departmentId]);
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            break;
                            
                        case '2': // Сотрудники проекта
                            $projectId = $_POST['project_id'] ?? 1;
                            $stmt = $pdo->prepare("
                                SELECT d.id_developers, d.first_name, d.last_name, p.name_post AS position
                                FROM developers d
                                JOIN project_dev pd ON d.id_developers = pd.id_developer
                                JOIN post p ON d.id_post = p.id_post
                                WHERE pd.id_project = ?
                            ");
                            $stmt->execute([$projectId]);
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            break;
                            
                        case '3': // Клиенты и их проекты
                            $stmt = $pdo->query("
                                SELECT u.id_users, u.name_user AS client, 
                                       pr.id_project, pr.name_project, pr.start_date, pr.end_date
                                FROM users u
                                JOIN project pr ON u.id_users = pr.id_users
                                ORDER BY u.name_user
                            ");
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            break;
                            
                        case '4': // Проекты по клиенту
                            $clientId = $_POST['client_id'] ?? 1;
                            $stmt = $pdo->prepare("
                                SELECT id_project, name_project, start_date, end_date
                                FROM project
                                WHERE id_users = ?
                                ORDER BY start_date
                            ");
                            $stmt->execute([$clientId]);
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            break;
                            
                        case '5': // Сотрудники по зарплате
                            $stmt = $pdo->query("
                                SELECT d.id_developers, d.first_name, d.last_name, p.name_post AS position, d.salary
                                FROM developers d
                                JOIN post p ON d.id_post = p.id_post
                                ORDER BY d.salary DESC
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
            ?>
        </div>
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
                        <button type="submit" class="action-btn" style="background-color: #dc3545;">Удалить запись</button>
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
                <div id="add-form-<?= $table ?>" class="action-form" style="<?= $showAddForm ? 'display:block;' : 'display:none;' ?>">
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
                        <h4>Редактировать запись в <?= htmlspecialchars($table) ?> (ID: <?= htmlspecialchars($edit_data[getPrimaryKey($pdo, $table)]) ?>)</h4>
                        <form method="post">
                            <input type="hidden" name="table_name" value="<?= $table ?>">
                            <input type="hidden" name="table_action" value="edit">
                            <input type="hidden" name="record_id" value="<?= htmlspecialchars($edit_data[getPrimaryKey($pdo, $table)]) ?>">
                            
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
                form.scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        // Динамическое обновление дополнительных полей для запросов
        document.querySelector('select[name="request"]').addEventListener('change', function() {
            var requestId = this.value;
            var extraInputs = document.getElementById('extra-inputs');
            extraInputs.innerHTML = '';
            
            if (requestId == '1') {
                extraInputs.innerHTML = '<div class="form-row"><label>ID отдела: <input type="number" name="department_id" value="1" min="1"></label></div>';
            } else if (requestId == '2') {
                extraInputs.innerHTML = '<div class="form-row"><label>ID проекта: <input type="number" name="project_id" value="1" min="1"></label></div>';
            } else if (requestId == '4') {
                extraInputs.innerHTML = '<div class="form-row"><label>ID клиента: <input type="number" name="client_id" value="1" min="1"></label></div>';
            } else if (requestId == '6') {
                extraInputs.innerHTML = '<div class="form-row"><label>Год завершения: <input type="number" name="year" value="<?= date('Y') ?>" min="2000"></label></div>';
            } else if (requestId == '9') {
                extraInputs.innerHTML = '<div class="form-row"><label>Мин. разработчиков: <input type="number" name="min_devs" value="1" min="0"></label></div>';
            } else if (requestId == '10') {
                extraInputs.innerHTML = '<div class="form-row"><label>Минимальный стаж (лет): <input type="number" name="experience" value="3" min="0"></label></div>';
            }
        });
    </script>
</body>
</html>