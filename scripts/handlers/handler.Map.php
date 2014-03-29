<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/handlers/handler.php';

$post = GetPOST();

try {
   switch ($post['action']) {
      case 'getInitInfo':
         require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Event.php';
         $data = [
            'events' => $_event->SetSamplingScheme(Event::INIT_SCHEME)->GetAll()
            // 'places' => //blblbl
         ];
         break;

   }
} catch (Exception $e) {
   $ajaxResult['result'] = false;
   $ajaxResult['message'] = $e->getMessage();
}

echo json_encode($ajaxResult);