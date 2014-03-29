<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.DataHandling.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/lib/reg_auth.inc';

if (isset($_POST['submit'])) {
   $post     = GetPOST();
   $smarty->assign(
      $fields = [
         User::NAME_FLD     => $post['name'],
         User::LOGIN_FLD    => $post['login'],
         User::SURNAME_FLD  => $post['surname']
      ]
   );
   $pass   = $post['pass'];
   $repass = $post['repass'];
   try {
      (new DataHandling())->ValidateLogin($post['login'])
                          ->ValidatePassword($pass)
                          ->ValidateRepeatPasswords($pass, $repass);
      Registration::Register($fields, $pass);
      // Redirect('/success_register');
   } catch (Exception $e) {
      $smarty->assign('db_error', $e->getMessage());
   }
}
$smarty->display('registration.tpl');