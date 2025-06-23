<?php
$host = 'localhost';
$dbname = 'phpprakt10';
$username = 'root';
$password = '';

include 'index.html';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $login = $_POST['login'];
    $password = $_POST['password'];

    if ($login == "name_user" && $password == "pass_user") {
        echo "Вход успешен!";
    } else {
        echo "Неверный логин или пароль.";
    }
} else {
    echo "Ошибка: неверный метод запроса.";
}