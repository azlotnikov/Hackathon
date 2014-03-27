<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/lib/reg_auth.inc';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Mail.php';

class Activation
{

   public static function Activate($type, $hash)
   {
      switch ($type) {
         case 'activation':
            self::_AccountActivate($hash, $_GET['email']);
            break;

         case 'forgotten_pass':
            self::_ForgottenPass($hash, $_GET['email']);
            break;

         case 'change_email':
            self::_ChangeEmail($hash);
         break;
      }
   }

   private static function _ForgottenPass($hash, $email)
   {
      global $smarty, $_userFPass;
      $smarty->assign('title', 'Восстановление пароля');
      $userInfo = $_userFPass->GetByEmail($email);
      if (empty($userInfo)) throw new Exception(ERROR_MAIL);
      $mail = new Mail();
      if (
            $mail->CompareUniqueSignature($hash, $email, $userInfo[$_userFPass->ToPrfxNm(UserForgottenPass::PASS_FLD)])
         && CheckDateDiff($userInfo[$_userFPass->ToPrfxNm(UserForgottenPass::DATE_FLD)])
      ) {
         AuthorizedUser::Authorize($email, $_userFPass->AcceptNewPassword($userInfo), true);
      } else {
         throw new Exception(ERROR_FORGOTTEN_PASS);
      }
      $smarty->assign('successMsg', 'Новый пароль принят!')
             ->assign('isGoAcc', true);
   }

   private static function _AccountActivate($hash, $email)
   {
      $subj = isset($_GET['subj']) ? $_GET['subj'] : '';
      global $smarty;
      $smarty->assign('title', 'Активация аккаунта');
      $_user = new User();
      $userInfo = $_user->SetSamplingScheme(User::ACTIVATION_SCHEME)->GetByEmail($email);
      if (empty($userInfo) || $userInfo[$_user->ToPrfxNm(User::VERIFICATION_FLD)]) throw new Exception(ERROR_MAIL);

      $time = new DateTime($userInfo[$_user->ToPrfxNm(User::REGISTER_DATE_FLD)]);
      $time->add(new DateInterval(CONFIRMATION_TERM));
      $curDate = new DateTime();
      if ($curDate > $time) throw new Exception(ERROR_MAIL_CONFIRM_EXPIRED);
      $mail = new Mail();
      if ($mail->compareUniqueSignature($hash, $email, $userInfo[$_user->ToPrfxNm(User::PASS_FLD)])) {
         $_user->SetFieldByName(User::VERIFICATION_FLD, 1)
               ->SetFieldByName(User::ID_FLD, $userInfo[$_user->ToPrfxNm(User::ID_FLD)])
               ->SetFieldByName(User::EMAIL_FLD, $email)
               ->Update();
         AuthorizedUser::authorize($email, $userInfo[$_user->ToPrfxNm(User::PASS_FLD)], true);
         $smarty->assign('isLogin', true);
      } else {
         throw new Exception(ERROR_MAIL);
      }
      $smarty->assign('successMsg', 'Вы успешно зарегистрированы!')
             ->assign('isGoAcc', true);
   }

   private static function _ChangeEmail($hash)
   {
      unset($_SESSION['isSend']);

      global $smarty;

      $oldEmail = $_GET['old_email'];
      $newEmail = $_GET['new_email'];

      $_user = new User();
      $userInfo = $_user->SetSamplingScheme(User::ACTIVATION_SCHEME)->GetByEmail($oldEmail);
      if (empty($userInfo)) throw new Exception(SEND_INCORRECT_MAIL);
      $userPass = $userInfo[$_user->ToPrfxNm(User::PASS_FLD)];
      $mail = new Mail();
      if ($mail->compareUniqueSignature($hash, $newEmail, $userPass)) {
         AuthorizedUser::ChangeEmail($oldEmail, $newEmail, $userPass);
      } else {
         throw new Exception(ERROR_CHANGE_MAIL);
      }
      $smarty->assign('isGoAcc', true)
             ->assign('successMsg', 'E-mail успешно изменен.');
   }

}