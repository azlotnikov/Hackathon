<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/handlers/handler.php';

$post = GetPOST();
try {
   switch ($post['action']) {
      case 'getInitInfo':
         require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Place.php';
         require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Event.php';
         $ajaxResult['data'] = [
            'events' => $_event->SetSamplingScheme(Event::INIT_SCHEME)->GetAll(),
            'places' => $_place->SetSamplingScheme(INIT_SCHEME)->GetAll()
         ];
         break;
   }
} catch (Exception $e) {
   $ajaxResult['result'] = false;
   $ajaxResult['message'] = $e->getMessage();
}

echo json_encode($ajaxResult);
