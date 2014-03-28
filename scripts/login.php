<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.DataHandling.php';

$fromUri = isset($_GET['originating_uri']) ? $_GET['originating_uri'] : '/profile';
if (Authentification::checkCredentials()) {
   header("Location: /profile");
}
if (isset($_POST['submit'])) {
   $bool                 = isset($_SESSION['attempts']);
   $_SESSION['attempts'] = $bool ? $_SESSION['attempts'] + 1 : 1;
   $post                 = GetPOST();
   $email                = $post['mail'];
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
      $data_h = new DataHandling();
      try {
         $data_h->validateEmail($email, ERROR_LOGIN)
                ->validatePassword($pass, ERROR_LOGIN);
      } catch (Exception $e) {
         $errorMsg = $e->getMessage();
         throw new Exception('');
      }
      try {
         AuthorizedUser::Login($email, $pass);
         $_SESSION['attempts'] = null;
         header("Location: $fromUri");
      } catch (Exception $e) {
         $errorMsg = $e->getMessage();
      }
   } catch (Exception $e) {}
      // $errorMsg = $e->getMessage();
}
if (isset($_SESSION['attempts']) && $_SESSION['attempts'] >= NUMBER_OF_LOGIN_ATTEMPTS) {
   $smarty->assign('hasCaptcha', 'true');
}
$smarty->assign('email', isset($email) ? $email : '')
       ->assign('fromUri', $fromUri)
       ->assign('captcha_img_url', '/kcaptcha/captcha.php?' . session_name() . '=' . session_id())
       ->assign('errorMsg', isset($errorMsg) ? $errorMsg : null)
       ->display('login.tpl');
unset($_SESSION['captcha_keystring']);