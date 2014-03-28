<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.DataHandling.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Mail.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/lib/reg_auth.inc';

if (isset($_POST['submit'])) {
   $post    = GetPOST();
   $email   = $post['mail'];
   $name    = $post['name'];
   $surname = $post['surname'];
   $pass    = $post['pass'];
   $repass  = $post['repass'];
   $photo   = intval(!empty($post['photo']));
   $video   = intval(!empty($post['video']));
   try {
      $data_h = new DataHandling();
      try {
         $data_h->validateEmail($email);
      } catch (Exception $e) {
         $smarty->assign('errorEmail', $e->getMessage());
         throw new Exception('');
      }
      if (empty($name)) {
         $smarty->assign('errorName', 'Имя не может быть пустым');
         throw new Exception('');
      }
      try {
         $data_h->validateEmail($email);
      } catch (Exception $e) {
         $smarty->assign('errorEmail', $e->getMessage());
         throw new Exception('');
      }
      try {
         $data_h->validatePassword($pass);
      } catch (Exception $e) {
         $smarty->assign('errorPass', $e->getMessage());
         throw new Exception('');
      }
      try {
         $data_h->validateRepeatPasswords($pass, $repass);
      } catch (Exception $e) {
         $smarty->assign('errorRepass', $e->getMessage());
         throw new Exception('');
      }
      if (empty($photo) && empty($video)) {
         $smarty->assign('errorInfo', true);
         throw new Exception('');
      }
      try {
         Registration::Register($email, $name, $surname, $pass, $photo, $video);
         $mail = new Mail();
         $mail->sendActivationMail($email);
         $_SESSION['isRegister'] = true;
         Redirect('success_register');
      } catch (ValidateException $e) {
         $smarty->assign('errorName', $e->getMessage());
      } catch (DBException $e) {
         $smarty->assign('db_error', $e->getMessage());
      } catch (Exception $e) {
         $smarty->assign('errorEmail', $e->getMessage());
      }
   } catch (Exception $e) {}
}
$smarty->assign('name', !empty($name) ? $name : '')
       ->assign('surname', isset($surname) ? $surname : '')
       ->assign('email', isset($email) ? $email : '')
       ->assign('photo', !empty($photo) ? $photo : null)
       ->assign('video', !empty($video) ? $video : null)
       ->display('registration.tpl');