<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/handlers/handler.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Place.php';

$post = GetPOST();

try {
   switch ($post['action']) {
      case 'getInitInfo':
         $places = $_place->SetSamplingScheme(INIT_SCHEME)->GetAll();
         break;
   }
} catch (Exception $e) {
   $ajaxResult['result'] = false;
   $ajaxResult['message'] = $e->getMessage();
}