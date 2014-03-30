<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/handlers/handler.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Event.php';

$post = GetPOST();
try {
   $ajaxResult['data'] = $_event->GetMoreListByType($post['user_id'], $post['cur_amount'], $post['event_type']);
} catch (Exception $e) {
   $ajaxResult['result']  = false;
   $ajaxResult['message'] = $e->getMessage();
}

echo json_encode($ajaxResult);
