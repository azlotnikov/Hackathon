<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.DataHandling.php';

$fromUri = isset($_GET['originating_uri']) ? $_GET['originating_uri'] : '/profile';

if (Authentification::CheckCredentials()) Redirect('/profile');

if (isset($_POST['submit'])) {
   $bool                 = isset($_SESSION['attempts']);
   $_SESSION['attempts'] = $bool ? $_SESSION['attempts'] + 1 : 1;
   $post                 = GetPOST();
   $login                = $post['login'];
   $pass                 = $post['pass'];
   try {
      //captcha checking
      if (isset($_SESSION['attempts']) && $_SESSION['attempts'] >= NUMBER_OF_LOGIN_ATTEMPTS) {
         $key_string = isset($_POST['keystring']) ? $_POST['keystring'] : '';
         $bool       = isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] === $key_string;
         if ($bool) {
            $_SESSION['attempts'] = NULL;
         } elseif ($_SESSION['attempts'] > NUMBER_OF_LOGIN_ATTEMPTS) {
            $smarty->assign('errorCaptcha', ERROR_CAPTCHA);
            throw new Exception('');
         }
      }
      try {
         (new DataHandling())->validatePassword($pass, ERROR_LOGIN);
         AuthorizedUser::Login($login, $pass);
         $_SESSION['attempts'] = null;
         Redirect('/profile');
      } catch (Exception $e) {
         $errorMsg = $e->getMessage();
      }
   } catch (Exception $e) {}
}
$smarty->assign('fromUri', $fromUri)
       ->assign('login', isset($login) ? $login : '')
       ->assign('captcha_img_url', sprintf('/kcaptcha/captcha.php?%s=%s', session_name(), session_id()))
       ->assign('hasCaptcha', isset($_SESSION['attempts']) && $_SESSION['attempts'] >= NUMBER_OF_LOGIN_ATTEMPTS)
       ->assign('errorMsg', isset($errorMsg) ? $errorMsg : null)
       ->display('login.tpl');

unset($_SESSION['captcha_keystring']);