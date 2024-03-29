<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/lib/cookie.inc';
// require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.UserForgottenPass.php';


//------------class Authentification------------

class Authentification
{
   public static function CheckCredentials()
   {
      $isAuth = true;
      if (empty($_SESSION['sid'])) {
         try {
            AuthorizedUser::Authorize((new AuthCookie())->Validate());
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
   public static function Register($fields, $pass)
   {
      global $_user, $db;
      $userInfo = $_user->SetSamplingScheme(User::REGISTRATION_CHECK_SCHEME)->GetByLogin($fields[User::LOGIN_FLD]);
      $_user->SetFields($fields);
      if (empty($userInfo) && $db->isConnected) {
         AuthorizedUser::Authorize(
            $_user->SetFields(
               array_merge(
                  $fields,
                  [
                     User::SALT_FLD          => Encryption::GenerateRandomString(),
                     User::PASS_FLD          => $pass,
                     User::REGISTER_DATE_FLD => date(GENERAL_DATE_FORMAT)
                  ]
               )
            )->Insert(true)
         );
      } else {
         throw new Exception(ERROR_LOGIN_ALREADY_REGISTERED);
      }
   }
}

//------------class AuthorizedUser------------

class AuthorizedUser
{
   public static function Login($login, $pass)
   {
      global $_user, $db;
      $_user->CheckSearch();
      $_user->search->AddClause(
         CCond(
            CF(User::TABLE, $_user->GetFieldByName(User::LOGIN_FLD)),
            CVP($login),
            cAND
         )
      );
      $user = $_user->SetSamplingScheme(User::LOGIN_SCHEME)->GetByLogin($login);
      if (empty($user)) {
         throw new Exception(ERROR_LOGIN);
      }
      $encryptpass = $db->Query('SELECT create_encrypted_pass(?, ?) as db_pass', [$pass, $user[$_user->ToPrfxNm(User::SALT_FLD)]])[0]['db_pass'];
      if ($encryptpass == $user[$_user->ToPrfxNm(User::PASS_FLD)]) {
         self::Authorize($user[$_user->ToPrfxNm(User::ID_FLD)]);
      } else {
         throw new Exception(ERROR_LOGIN);
      }
   }

   public static function Authorize($user_id)
   {
      global $_session;
      $_session->SetFieldByName(Session::USER_FLD, $user_id);
      $_SESSION['sid'] = $_session->Authorize($user_id);
      (new AuthCookie($_SESSION['sid']))->Set();
   }

   public static function Unauthorized()
   {
      global $_session;
      $_SESSION['sid'] = null;
      try {
         (new AuthCookie())->Remove();
      } catch (CookieException $e) {}
      unset($_SESSION['sid']);
      /////////////////////////////////////////////////////////////////////////////////////////////////
      //session_destroy();
   }

   public static function ChangePassword($login, $oldPass, $newPass)
   {
      try {
         static::Login($login, $oldPass);
      } catch (Exception $e) {
         throw new Exception(ERROR_PASS);
      }
      global $_user;
      $_user->SetFieldByName(User::PASS_FLD, $newPass)->UpdateByLogin($login);
   }

   public static function DeleteAccount($login)
   {
      global $_user;
      $_user->DeleteByLogin($login);
      self::Unauthorized();
   }

}