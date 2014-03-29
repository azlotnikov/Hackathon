<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/handlers/handler.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Event.php';

$post = GetPOST();
$post['action'] = !empty($post['action']) ? $post['action'] : null;
try {
   switch ($post['action']) {
      case 'getInitInfo':
         require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Place.php';
         require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/lib/reg_auth.inc';
         $ajaxResult['data'] = [
            'events' =>
               $_event->SetSamplingScheme(Event::INIT_SCHEME)->GetAll(),
            'places' =>
               true//Authentification::CheckCredentials()
               ? $_place->SetFieldByName(Place::FLOOR_FLD, $post['floor'])->SetSamplingScheme(Place::INIT_SCHEME)->GetAll()
               : []
         ];
         break;

      case 'addEvent':
         if (Authentification::CheckCredentials()) {
            $_event->addEvent($post['data']);
         } else {
            throw new Exception('No access, sry! :(');
         }
         break;

      default:
         throw new Exception('pizdec');
   }
} catch (Exception $e) {
   $ajaxResult['result'] = false;
   $ajaxResult['message'] = $e->getMessage();
}

echo json_encode($ajaxResult);
