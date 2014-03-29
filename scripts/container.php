<?php
if(!isset($_SESSION)) {
   @session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/settings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/utils.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/lib/reg_auth.inc';

$request = explode('/', substr($_SERVER['REQUEST_URI'], 1));

$smarty->assign('isLogin', Authentification::CheckCredentials());