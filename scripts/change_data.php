<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.DataHandling.php';

define('DELETE_ACC', 'delete_acc');
// define('FORGOTTEN_PASS', 'forgotten_pass');
define('CHANGE_PASS', 'change_password');
define('CHANGE_CONTACT', 'change_contact_information');
define('CHANGE_EXTRA_DATA', 'change_extra_information');
define('CHANGE_NAME', 'change_name');

$possible_types = Array(
   DELETE_ACC,
   // FORGOTTEN_PASS,
   CHANGE_NAME,
   CHANGE_PASS,
   CHANGE_EXTRA_DATA,
   CHANGE_CONTACT,
);

$type = !empty($_GET['type']) ? $_GET['type'] : null;
if (empty($type) || !in_array($type, $possible_types)) Redirect('/404');
// if ($type != FORGOTTEN_PASS) {
$user = $_user->SetSamplingScheme(User::REGISTRATION_CHECK_SCHEME)->GetBySID(isset($_SESSION['sid']) ? $_SESSION['sid'] : null);
if (empty($user)) Redirect('/404');

$login = $user[$_user->ToPrfxNm(User::LOGIN_FLD)];
// }

// SetActiveItem($type == FORGOTTEN_PASS ? 'login' : 'profile');

try {
   $smarty->assign('type', $type);
   $post = GetPOST();
   if (isset($post['submit'])) {
      $data_h = new DataHandling();
      switch ($type) {
         case DELETE_ACC:
            if ($post['submit'] == 'delete') {
               AuthorizedUser::DeleteAccount($_SESSION['email']);
               Redirect();
            } elseif ($post['submit'] == 'cancel') {
               Redirect('/profile');
            }
            break;

         case CHANGE_PASS:
            extract($post);
            $data_h->validatePassword($new_pass)->validateRepeatPasswords($new_pass, $re_new_pass);
            AuthorizedUser::ChangePassword($login, $pass, $new_pass);
            DisplaySuccess('isChangePass', true);
            break;

         case CHANGE_EXTRA_DATA:
            $_user->SetFieldByName(User::DESCRIPTION_FLD, $post['additional'])->UpdateByLogin($post['login']);
            DisplaySuccess('extra_data', true);
            break;

         case CHANGE_CONTACT:
            extract($post);
            if (!empty($phone)) {
               $data_h->validatePhone($phone);
            }
            (new DataHandling)->ValidateRoom($room);
            $_user->SetFieldByName(User::ROOM_FLD, $room)
                  ->SetFieldByName(User::PHONE_FLD, $phone)
                  ->UpdateByLogin($login);
            DisplaySuccess('contact_data', true);
            break;

         case CHANGE_NAME:
            extract($post);
            if (empty($name)) {
               $smarty->assign('errorName', 'Имя не может быть пустым');
               throw new Exception('');
            }
            $_user->SetFieldByName(User::NAME_FLD, $name)
                  ->SetFieldByName(User::SURNAME_FLD, $surname)
                  ->UpdateByLogin($login);
            DisplaySuccess('name_data', true);
            break;

         // case FORGOTTEN_PASS:
         //    $email = $post['email'];
         //    $data_h->validateEmail($email);
         //    $new_pass = AuthorizedUser::ForgottenPassword($email);
         //    $mail = new Mail();
         //    $mail->SendForgottenPassMail($email, $new_pass);
         //    DisplaySuccess('new_pass');
         //    break;

         default:
            header('Location: /');
            break;
      }
   }
} catch (Exception $e) {
   $smarty->assign('errorMsg', $e->getMessage());
}

if ($type == CHANGE_EXTRA_DATA) {
   $_user->SetSamplingScheme(User::EXTRA_DATA_SCHEME);
} elseif ($type == CHANGE_CONTACT) {
   $_user->SetSamplingScheme(User::CONTACT_INFO_SCHEME);
} else if ($type == CHANGE_NAME) {
   $_user->SetSamplingScheme(User::NAME_INFO_SCHEME);
}

if ($type != CHANGE_PASS) {
   print_r($_user->GetBySID($_SESSION['sid']));
   $smarty->assign('udata', $_user->GetBySID($_SESSION['sid']));
}

$smarty->assign('login', $login)
       ->display('change_data.tpl');