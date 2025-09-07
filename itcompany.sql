-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Июн 27 2025 г., 09:16
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `itcompany`
--

-- --------------------------------------------------------

--
-- Структура таблицы `department`
--

CREATE TABLE `department` (
  `id_department` int(11) NOT NULL,
  `name_department` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `department`
--

INSERT INTO `department` (`id_department`, `name_department`) VALUES
(1, 'FrontEnd'),
(2, 'BackEnd'),
(3, 'FullStack');

-- --------------------------------------------------------

--
-- Структура таблицы `developers`
--

CREATE TABLE `developers` (
  `id_developers` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `series_number` int(11) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `experience` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `id_post` int(11) NOT NULL,
  `id_department` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `developers`
--

INSERT INTO `developers` (`id_developers`, `first_name`, `last_name`, `middle_name`, `birth_date`, `series_number`, `salary`, `experience`, `id_user`, `id_post`, `id_department`) VALUES
(1, 'Michail', 'Chernyavsky', 'Alexeevich', '2006-06-04', 113456789, 50000.00, 3, 4, 1, 1),
(2, 'Eva', 'Kavinsky', 'Michalovna', '0000-00-00', 220964, 60000.00, 3, 2, 1, 3),
(3, 'Ivan', 'Ivanov', 'Ivanovich', '1995-06-12', 73142877, 150000.00, 3, 1, 2, 3);

-- --------------------------------------------------------

--
-- Структура таблицы `post`
--

CREATE TABLE `post` (
  `id_post` int(11) NOT NULL,
  `name_post` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `post`
--

INSERT INTO `post` (`id_post`, `name_post`) VALUES
(1, 'Junior'),
(2, 'Middle'),
(3, 'Senior');

-- --------------------------------------------------------

--
-- Структура таблицы `project`
--

CREATE TABLE `project` (
  `id_project` int(11) NOT NULL,
  `name_project` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `comment_p` text DEFAULT NULL,
  `file_project` varchar(100) DEFAULT NULL,
  `file_name` varchar(100) DEFAULT NULL,
  `id_users` int(11) DEFAULT NULL,
  `id_developers` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `project`
--

INSERT INTO `project` (`id_project`, `name_project`, `start_date`, `end_date`, `description`, `comment_p`, `file_project`, `file_name`, `id_users`, `id_developers`) VALUES
(1, 'Landing №1', '2025-06-03', '2025-06-13', 'Создать лендин, ТЗ прикрепляю', NULL, 'C:\\Users\\PC\\Desktop\\Практика\\6 семестр\\Проект\\fonts\\Roboto.ttf', 'Техническое задание', 1, 0),
(2, 'Web-Source', '2025-06-03', '2025-07-31', 'Нужен веб ресурс, описание дано в ТЗ.', NULL, 'C:\\Users\\PC\\Desktop\\Практика\\6 семестр\\Проект\\fonts\\Roboto.ttf', 'WEB-SOURCE TT', 4, 0),
(3, 'Веб ресурс', '2025-06-18', '2025-06-25', 'Простенький веб-ресурс, по типу википедии на тему телефонов', '---', 'C:\\Users\\PC\\Desktop\\Практика\\6 семестр\\Проект\\fonts\\Roboto.ttf', 'Википедия', NULL, 0),
(4, 'ssgff', '2000-08-12', '2322-03-12', '213', '123213', 'uploads/project_68459353b68072.93759341.pdf', 'project_68459353b68072.93759341.pdf', NULL, 0),
(5, 'отлиз', '2025-06-07', '2025-06-08', 'пизды', '', 'uploads/project_684593feb98901.81405776.pdf', 'project_684593feb98901.81405776.pdf', NULL, 0),
(6, '1', '1111-11-11', '1111-11-11', '111111', '1111', 'uploads/project_68464b6c584c73.06578595.pdf', 'project_68464b6c584c73.06578595.pdf', NULL, 0),
(7, 'Проект 1', '2025-06-13', '2025-06-30', 'уапвп', 'вапвап', '', '', NULL, 0),
(8, 'fdfdf', '2025-06-13', '2025-06-27', 'dfdfd', 'dfdfdf', 'uploads/project_684bfdae7ba3a8.97947561.pdf', 'project_684bfdae7ba3a8.97947561.pdf', 22, 0),
(9, 'Я люблю когда Ксюня мне готовит кушать', '2024-10-19', '2025-06-13', 'Она вкусненько готовит яишенку, супчики, мяско, чаек', '', '', '', 23, 0),
(10, 'Я люблю когда Ксюня мне готовит кушать', '2024-10-19', '2025-06-13', 'Она вкусненько готовит яишенку, супчики, мяско, чаек', '', '', '', NULL, 0),
(11, 'Я люблю когда Ксюня мне готовит кушать', '2024-10-19', '2025-06-13', 'Она очень вкусненько готовит мне яишенку, супчики, котлетки, мяско, чаечки', '', 'uploads/project_684c33b3ce0122.73995459.docx', 'project_684c33b3ce0122.73995459.docx', 23, 0),
(12, 'ваыва', '2344-04-23', '3333-04-12', 'ываываыв', '', '', '', 22, 0),
(13, 'ваыва', '2344-04-23', '3333-04-12', 'ываываыв', '', '', '', 22, 0),
(14, 'рррр', '1000-01-12', '2000-01-12', 'описаие', '', '', '', 22, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `project_dev`
--

CREATE TABLE `project_dev` (
  `id_project_dev` int(11) NOT NULL,
  `id_developer` int(11) NOT NULL,
  `id_project` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `project_dev`
--

INSERT INTO `project_dev` (`id_project_dev`, `id_developer`, `id_project`) VALUES
(2, 2, 2),
(20, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id_users` int(11) NOT NULL,
  `name_user` varchar(20) DEFAULT NULL,
  `pass_user` varchar(20) DEFAULT NULL,
  `email_user` varchar(100) DEFAULT NULL,
  `role_user` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id_users`, `name_user`, `pass_user`, `email_user`, `role_user`) VALUES
(1, 'Ivan', '123', 'exemple_1@mpt.ru', 1),
(2, 'Eva', '456', 'exemple@internet.ru', 1),
(3, 'Sanya', '789', 'sanya228@mpt.ru', 0),
(4, 'Michail', '1234', 'isip_m.a.chernyavsky@mpt.ru', 1),
(19, 'Умная пизда', '$2y$10$nc2fgWkEx97mx', NULL, 0),
(20, 'выьдпль', '$2y$10$O.f4kWsHQS5KD', NULL, 0),
(21, 'Anton', '$2y$10$E//y/IRl/F.VJ', NULL, 0),
(22, 'Ksu', '$2y$10$ECwPYqFc4TTp9', 'st4s1k@internet.ru', 0),
(23, 'ксюнечка любимая', '$2y$10$UFlm92BFCMqiG', 'stasikstusik@gmail.com', 0),
(24, 'Стас', '$2y$10$EwNpm.VmYq7.L', 'exemple@mpt.ru', 0);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`id_department`);

--
-- Индексы таблицы `developers`
--
ALTER TABLE `developers`
  ADD PRIMARY KEY (`id_developers`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_post` (`id_post`),
  ADD KEY `id_department` (`id_department`);

--
-- Индексы таблицы `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id_post`);

--
-- Индексы таблицы `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`id_project`),
  ADD KEY `idx_project_id_users` (`id_users`);

--
-- Индексы таблицы `project_dev`
--
ALTER TABLE `project_dev`
  ADD PRIMARY KEY (`id_project_dev`),
  ADD KEY `id_developer` (`id_developer`),
  ADD KEY `id_project` (`id_project`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_users`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `department`
--
ALTER TABLE `department`
  MODIFY `id_department` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `developers`
--
ALTER TABLE `developers`
  MODIFY `id_developers` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `post`
--
ALTER TABLE `post`
  MODIFY `id_post` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `project`
--
ALTER TABLE `project`
  MODIFY `id_project` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `project_dev`
--
ALTER TABLE `project_dev`
  MODIFY `id_project_dev` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id_users` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `developers`
--
ALTER TABLE `developers`
  ADD CONSTRAINT `developers_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_users`),
  ADD CONSTRAINT `developers_ibfk_2` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`),
  ADD CONSTRAINT `developers_ibfk_3` FOREIGN KEY (`id_department`) REFERENCES `department` (`id_department`);

--
-- Ограничения внешнего ключа таблицы `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `fk_project_to_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `project_dev`
--
ALTER TABLE `project_dev`
  ADD CONSTRAINT `project_dev_ibfk_1` FOREIGN KEY (`id_developer`) REFERENCES `developers` (`id_developers`),
  ADD CONSTRAINT `project_dev_ibfk_2` FOREIGN KEY (`id_project`) REFERENCES `project` (`id_project`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
