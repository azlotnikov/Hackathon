<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.DataHandling.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/lib/reg_auth.inc';

if (isset($_POST['submit'])) {
   $post     = GetPOST();
   $smarty->assign(
      $fields = [
         User::ROOM_FLD     => $post['room'],
         User::NAME_FLD     => $post['name'],
         User::LOGIN_FLD    => $post['login'],
         User::SURNAME_FLD  => $post['surname']
      ]
   );
   try {
      (new DataHandling())->ValidateLogin($post['login'])
                          ->ValidateRoom($post['room'])
                          ->ValidatePassword($post['pass'])
                          ->ValidateRepeatPasswords($post['pass'], $post['repass']);
      Registration::Register($fields, $post['pass']);
      // Redirect('/success_register');
   } catch (Exception $e) {
      $smarty->assign('db_error', $e->getMessage());
   }
}
$smarty->display('registration.tpl');