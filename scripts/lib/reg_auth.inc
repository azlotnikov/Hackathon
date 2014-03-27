<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/lib/cookie.inc';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.UserForgottenPass.php';

//------------class Authentification------------

class Authentification
{
   public static function CheckCredentials()
   {
      $isAuth = true;
      if (empty($_SESSION['email'])) {
         try {
            $cookie = new Cookie();
            $cookie->Validate();
            AuthorizedUser::Authorize($cookie->GetEmail(), $cookie->GetPass());
         } catch (CookieException $e) {
            $isAuth = false;
         } catch (Exception $e) {
            $isAuth = false;
         }
      }
      return $isAuth;
   }

}

//------------class Registration------------

class Registration
{
   public static function Register($email, $name, $surname, $password, $isPhoto, $isVideo)
   {
      global $_user;
      $userInfo = $_user->SetSamplingScheme(User::REGISTRATION_CHECK_SCHEME)->GetByEmail($email);
      $_user->SetFieldByName(User::SALT_FLD, Encryption::GenerateRandomString())
            ->SetFieldByName(User::PASS_FLD, $password)
            ->SetFieldByName(User::NAME_FLD, $name)
            ->SetFieldByName(User::SURNAME_FLD, $surname)
            ->SetFieldByName(User::EMAIL_FLD, $email)
            ->SetFieldByName(User::IS_PHOTO_FLD, $isPhoto)
            ->SetFieldByName(User::IS_VIDEO_FLD, $isVideo);
      if (!empty($userInfo)) {
         //check if the user tried to register before time expires but confirmation e-mail
         $time = new DateTime($userInfo[$_user->ToPrfxNm(User::REGISTER_DATE_FLD)]);
         if ($userInfo[$_user->ToPrfxNm(User::VERIFICATION_FLD)]) {
            throw new Exception(ERROR_MAIL_ALREADY_REGISTERED);
         } else {
            $time->add(new DateInterval(CONFIRMATION_TERM));
            $curDate = new DateTime();
            if ($curDate > $time) {
               $_user->SetFieldByName(User::REGISTER_DATE_FLD, date(GENERAL_DATE_FORMAT))
                     ->SetFieldByName(User::ID_FLD, $userInfo[$_user->ToPrfxNm(User::ID_FLD)])
                     ->Update();
            } else {
               throw new Exception(ERROR_MAIL_CONFIRM);
            }
         }
      } else {
         $_user->SetFieldByName(User::REGISTER_DATE_FLD, date(GENERAL_DATE_FORMAT))
               ->Insert();
      }
   }

}

//------------class AuthorizedUser------------

class AuthorizedUser
{
   public static function Login($email, $pass)
   {
      global $_user, $db;
      $_user->CheckSearch();
      $_user->search->AddClause(
         CCond(
            CF(User::TABLE, $_user->GetFieldByName(User::VERIFICATION_FLD)),
            CVP(1),
            'AND'
         )
      );
      $user = $_user->SetSamplingScheme(User::LOGIN_SCHEME)->GetByEmail($email);
      if (empty($user)) {
         throw new Exception(ERROR_LOGIN);
      }
      $result = $db->Query('SELECT create_encrypted_pass(?, ?) as db_pass', Array($pass, $user[$_user->ToPrfxNm(User::SALT_FLD)]));
      $encryptpass = $result[0]['db_pass'];
      if ($encryptpass == $user[$_user->ToPrfxNm(User::PASS_FLD)]) {
         self::Authorize($email, $encryptpass, true);
      } else {
         throw new Exception(ERROR_LOGIN);
      }
   }

   public static function Authorize($email, $pass, $isCookie = false)
   {
      $_SESSION['email'] = $email;
      if ($isCookie) {
         $cookie = new Cookie($email, $pass);
         $cookie->Set();
      }
   }

   public static function Unauthorized()
   {
      $_SESSION['email'] = null;
      try {
         $cookie = new Cookie();
         $cookie->Remove();
      } catch (CookieException $e) {}
      unset($_SESSION['email']);
      /////////////////////////////////////////////////////////////////////////////////////////////////
      //session_destroy();
   }

   public static function ChangeEmail($oldEmail, $newEmail, $pass)
   {
      global $_user;
      $_user->SetFieldByName(User::EMAIL_FLD, $newEmail)->UpdateByEmail($oldEmail);
      self::Authorize($newEmail, $pass, true);
   }

   public static function ChangePassword($email, $oldPass, $newPass)
   {
      try {
         self::login($email, $oldPass);
      } catch (Exception $e) {
         throw new Exception(ERROR_PASS);
      }
      global $_user;
      $_user->SetFieldByName(User::PASS_FLD, $newPass)->UpdateByEmail($email);
      self::Authorize($email, $newPass, true);
   }

   public static function ForgottenPassword($email)
   {
      global $_user, $_userFPass;
      $userInfo = $_user->SetSamplingScheme(User::REGISTRATION_CHECK_SCHEME)->GetByEmail($email);
      if (empty($userInfo) || !$userInfo[$_user->ToPrfxNm(User::VERIFICATION_FLD)]) {
         throw new Exception(INCORRECT_MAIL);
      }
      $new_pass = Encryption::generateRandomString(10);
      $_userFPass->Insert($userInfo[$_user->ToPrfxNm(User::ID_FLD)], $new_pass, Encryption::GenerateRandomString());
      return $new_pass;
   }

   public static function DeleteAccount($email)
   {
      global $_user;
      $_user->DeleteByEmail($email);
      self::Unauthorized();
   }

}