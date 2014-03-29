<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/handlers/handler.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Event.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/lib/reg_auth.inc';

$post           = GetPOST();
$modeTypes      = ['dlt' => 0, 'upd' => 1, 'ins' => 2];
$post['action'] = !empty($post['action']) ? $post['action'] : null;
try {
   switch ($post['action']) {
      case 'getInitInfo':
         require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Place.php';
         $ajaxResult['data'] = [
            'events' =>
               $_event->SetSamplingScheme(Event::INIT_SCHEME)->GetAll(),
            'places' =>
               Authentification::CheckCredentials()
                  ? $_place->SetFieldByName(Place::FLOOR_FLD, $post['floor'])->SetSamplingScheme(Place::INIT_SCHEME)->GetAll()
                  : []
         ];
         break;

      case 'processEvent':
         //в зависимости от типа операции необходимо по разному обработать результат ProcessEvent
         //для всех операций, кроме вставки хорошим результатом считается число 1 (для вставки айди последней вставленной записи)
         if (Authentification::CheckCredentials()) {
            $md = $post['md'];
            if (!isset($modeTypes[$md]) || !($last_id = $_event->ProcessEvent($modeTypes[$md], $post['data']))) {
               throw new Exception("Pizdec ne srabotalo! process event");
            }
            $ajaxResult['result'] = intval($ajaxResult['last_id']) = $last_id;
         } else {
            throw new Exception('No access, sry! :(');
         }
         break;

      case 'getEventInfo':
         $ajaxResult['data'] = $_event->GetEventInfo($post['data']);
         break;

      default:
         throw new Exception('pizdec');
   }
} catch (Exception $e) {
   $ajaxResult['result']  = false;
   $ajaxResult['message'] = $e->getMessage();
}

echo json_encode($ajaxResult);
