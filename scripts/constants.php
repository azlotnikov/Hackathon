<?php
//numeric and string constants
define('CONFIRMATION_TERM', 'PT1440M'); //день - 1440 minute
define('COOKIE_LIFETIME', 86400000);
define('COOKIE_SITE', 'hack.local');
define('NUMBER_OF_LOGIN_ATTEMPTS', 3);
define('LOGIN_LEN', 5);
define('PASS_LEN', 6);
define('ADMIN_ID', 1);
define('ADMIN_START_PAGE', 'map');
define('GENERAL_DATE_FORMAT', 'Y-m-d H:i:s');

//errors messages
define('ERROR_QUERY', 'В данный момент невозможно подключение к базе данных.');
define('ERROR_LOGIN_ALREADY_REGISTERED', 'Этот логин уже зарегистрирован, используйте другой.');
define('ERROR_LOGIN', 'Неверное имя пользователя или пароль.');
define('ERROR_ROOM', 'Неправильно указана комната.');
define('ERROR_PASS', 'Неверный пароль.');
define('ERROR_OLD_NEW_PASS', 'Введенные пароли не совпадают.');
define('ERROR_FORM_FILL', 'Некорректно заполнена форма.');
define('ERROR_PASS_LEN', 'Пароль должен быть длиннее ' . (PASS_LEN - 1) . '-ти символов.');
define('ERROR_LOGIN_LEN', 'Логин должен быть длиннее ' . (LOGIN_LEN - 1) . '-ти символов.');
define('ERROR_CAPTCHA', 'Ошибка при вводе символов с картинки.');
define('ERROR_CONTACT_PHONE', 'Введен неверный номер телефона.');
define('ERROR_FORGOTTEN_PASS', 'Невозможно активировать пароль.');

//database consts
define('opEQ', '=');
define('opNE', '<>');
define('opGT', '>');
define('opGE', '>=');
define('opLT', '<');
define('opLE', '<=');
define('cOR', 'OR');
define('cAND', 'AND');
define('cNONE', '');
define('OT_ASC', 'ASC');
define('OT_DESC', 'DESC');
define('OT_RAND', 'RAND()');
define('MYSQL_NOW', 'NOW()');

define('SMARTY_APP_NAME', 'dormitory');