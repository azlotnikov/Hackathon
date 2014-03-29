<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.DataHandling.php';
// require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/lib/reg_auth.inc';

echo "reg";
if (isset($_POST['submit'])) {
   $post     = GetPOST();
   $smarty->assign(
      $fields = [
         User::NAME_FLD     => $post['name'],
         User::LOGIN_FLD    => $post['login'],
         User::SURNAME_FLD  => $post['surname'],
         User::NICKNAME_FLD => $post['nickname']
      ]
   );
   $pass   = $post['pass'];
   $repass = $post['repass'];
   try {
      (new DataHandling())->validatePassword($pass)
                          ->validateRepeatPasswords($pass, $repass);
      Registration::Register($fields);
      Redirect('/success_register');
   } catch (Exception $e) {
      $smarty->assign('db_error', $e->getMessage());
   }
}
$smarty->display('registration.tpl');