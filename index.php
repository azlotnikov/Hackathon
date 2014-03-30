<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/container.php';

// $_POST = [
//    'action' => 'processEvent',
//    'md'   => 'ins',
//    'data' => [
//       'eid' => 1,
//       'header' => 'Продаю стафчег',
//       'event_type' => 1,
//       'description' => 'Продам утюг, девушку, жизнь!',
//       'place_id'    => 1
//    ]
// ];

// require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/handlers/handler.Map.php';

// exit;


// $_POST = [
//    'action' => 'getEventInfo',
//    'data' => [2]
// ];

// require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/handlers/handler.Map.php';

// exit;

switch ($request[0]) {
   case '': case null: case false:
      SetActiveItem();
      $smarty->display('index.tpl');
      break;

   case 'events';
      SetActiveItem('events');
      require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/events.php';
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
      require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/profile.php';
      break;

   case 'map':
      SetActiveItem('map');
      require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/map.php';
      break;

   case 'change_data':
      require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/change_data.php';
      break;

   case 'uploadphoto':
      require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/upload_photo.php';
      break;

   case 'admin':
      require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Admin.php';
      $isLoginPage = empty($request[1]) || $request[1] == 'login';
      if ($_admin->IsAdmin()) {
         if ($isLoginPage) {
            Redirect("/admin/" . ADMIN_START_PAGE);
         }
      } elseif (!$isLoginPage) {
         Redirect('/admin/');
      }
      $request[1] = !empty($request[1]) ? $request[1] : null;
      switch ($request[1]) {
         case '': case 'login': case null: case false:
            require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/admin/admin.login.php';
            break;

         case 'map':
            require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/admin/admin.map.php';
            break;

         case 'change_pass':
            require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/admin/admin.change_pass.php';
            break;

         case 'logout':
            unset($_SESSION['admin_login']);
            unset($_SESSION['admin_pass']);
            header('Location: /admin');
            break;

         default:
            header("Location: /admin/" . ADMIN_START_PAGE);
            break;
      }
      break;

   default:
      #error page
}