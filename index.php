<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/container.php';


// $_POST = [
//    'action' => 'addEvent',
//    'data' => [
//       'header' => 'Продаю стаф',
//       'event_type' => 1,
//       'description' => 'Продам утюг, девушку, жизнь!',
//       'place_id'    => 1
//    ]
// ];

// require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/handlers/handler.Map.php';

// exit;
switch ($request[0]) {
   case '': case null: case false:
      SetActiveItem();
      $smarty->display('index.tpl');
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