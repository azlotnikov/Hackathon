<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/container.php';

switch ($request[0]) {
   case '': case null: case false:
      SetActiveItem();
      break;

   case 'login':
      SetActiveItem('login');
      require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/login.php';
      break;

   case 'registration':
      SetActiveItem('registration');
      require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/registration.php';
      break;

   case 'profile':
      SetActiveItem('profile');
      // require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/profile.php';
      break;

   case 'map':
      SetActiveItem('map');
      require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/map.php';
      break;

   default:
      #error page
}