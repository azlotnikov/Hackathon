<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/handlers/handler.php';

$post = GetPOST();

try {
   switch ($post['action']) {
      case 'getInitInfo':

         break;

   }
} catch (Exception $e) {
   $ajaxResult['result'] = false;
   $ajaxResult['message'] = $e->getMessage();
}