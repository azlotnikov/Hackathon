<?php
//numeric and string constants
define('CONFIRMATION_TERM', 'PT1440M'); //день - 1440 minute
define('COOKIE_LIFETIME', 86400000);
define('COOKIE_SITE', 'camerapeople.local');
define('NUMBER_OF_LOGIN_ATTEMPTS', 3);
define('LOGIN_LEN', 6);
define('PASS_LEN', 6);
define('ADMIN_ID', 1);
define('ADMIN_START_PAGE', 'users');
define('GENERAL_DATE_FORMAT', 'Y-m-d H:i:s');

//errors messages
define('INCORRECT_MAIL', 'Введен неверный e-mail.');
define('SEND_INCORRECT_MAIL', 'Неправильный e-mail.');
define('ERROR_CHANGE_MAIL', 'Невозможно изменить e-mail.');
define('ERROR_MAIL', 'Подтверждение e-mail невозможно.');
define('ERROR_MAIL_ALREADY_REGISTERED', 'Этот e-mail уже зарегистрирован, используйте другой.');
define('ERROR_MAIL_CONFIRM', 'Не истекло время подтверждения e-mail.');
define('ERROR_MAIL_CONFIRM_EXPIRED', 'Истекло время подтверждения e-mail.');
define('ERROR_QUERY', 'В данный момент невозможно подключение к базе данных.');
define('ERROR_LOGIN_ALREADY_REGISTERED', 'Этот логин уже зарегистрирован, используйте другой.');
define('ERROR_LOGIN', 'Неверное имя пользователя или пароль.');
define('ERROR_PASS', 'Неверный пароль.');
define('ERROR_OLD_NEW_PASS', 'Введенные пароли не совпадают.');
define('ERROR_FORM_FILL', 'Некорректно заполнена форма.');
define('ERROR_PASS_LEN', 'Пароль должен быть длиннее ' . (PASS_LEN - 1) . '-ти символов.');
define('ERROR_LOGIN_LEN', 'Логин должен быть длиннее ' . (LOGIN_LEN - 1) . '-ти символов.');
define('ERROR_CAPTCHA', 'Ошибка при вводе символов с картинки.');
define('ERROR_CONTACT_PHONE', 'Введен неверный номер телефона.');
define('ERROR_FORGOTTEN_PASS', 'Невозможно активировать пароль.');

//database consts
define('OT_ASC', 'ASC');
define('OT_DESC', 'DESC');
define('OT_RAND', 'RAND()');
define('MYSQL_NOW', 'NOW()');

define('SMARTY_APP_NAME', 'camera_people');

define('PHOTOSESSIONS', 'photosessions');
define('VIDEOSESSIONS', 'videosessions');
define('PHOTOGRAPHS', 'photographs');
define('VIDEOGRAPHS', 'videographs');

define('ABOUT1_ID', 1);
define('ABOUT2_ID', 2);
define('MAIN_TOP_BANNER_ID', 3);
define('MAIN_BOTTOM_BANNER_ID', 4);

$psCats = Array(
   1 => 'Свадьба и праздники',
   2 => 'Дети и семья',
   3 => 'Натюрморт',
   4 => 'Студия и портфолио',
   5 => 'Репортаж',
   6 => 'Другое'
);

$vsCats = Array(
   1 => 'Свадьба и праздники',
   2 => 'Натюрморт',
   3 => 'Студия и портфолио',
   4 => 'Репортаж',
   5 => 'Другое'
);