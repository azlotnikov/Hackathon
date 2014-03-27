<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/lib/encryption.inc';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/lib/exception.inc';

class CookieBase
{
   const GLUE         = '|';
   const COOKIEPATH   = '/';
   const EXPIRATION   = 86400000; //86400000 seconds in a 1000 days
   const COOKIENAME   = '';
   const COOKIEDOMAIN = COOKIE_SITE;

   protected function _Package($parts)
   {
      return Encryption::Encrypt(implode(static::GLUE, $parts));
   }

   protected function _Unpackage($cookie)
   {
      return explode(static::GLUE, Encryption::Decrypt($cookie));
   }

   protected function Set($parts)
   {
      setcookie(static::COOKIENAME, $this->_Package($parts), time() + static::EXPIRATION, static::COOKIEPATH, static::COOKIEDOMAIN);
   }

   public function Remove()
   {
      setcookie(static::COOKIENAME, "", 0, static::COOKIEPATH, static::COOKIEDOMAIN);
   }

}

class Cookie extends CookieBase
{
   //cookie data
   private
      $pass,
      $email;

   const COOKIENAME = 'USERAUTH';

   function __construct($email = false, $pass = false)
   {
      if (!empty($email)) {
         $this->email = $email;
         $this->pass = $pass;
      } elseif (array_key_exists(static::COOKIENAME, $_COOKIE)) {
         list($this->email, $this->pass) = $this->_Unpackage($_COOKIE[static::COOKIENAME]);
      } else {
         throw new CookieException();
      }
   }

   public function Set()
   {
      parent::Set([$this->email, $this->pass]);
   }

   public function GetEmail()
   {
      return $this->email;
   }

   public function GetPass()
   {
      return $this->pass;
   }

   public function Validate()
   {
      global $_user;
      $user = $_user->SetSamplingScheme(User::EMAIL_SCHEME)->GetByEmail($this->email);
      if (
            empty($user)
         || $user[$_user->ToPrfxNm(User::PASS_FLD)] != $this->pass
         || !$user[$_user->ToPrfxNm(User::VERIFICATION_FLD)]
      )
         throw new CookieException();
   }

}

class CookieProfileView extends CookieBase
{
   private
      $user_id;

   const COOKIENAME = 'USERPROFILEVIEW';
   const EXPIRATION = 86400; //86400 seconds in a 1 day
   // const EXPIRATION = 5;

   function __construct($user_id)
   {
      $this->user_id = $user_id;
      if (array_key_exists(static::COOKIENAME . $user_id, $_COOKIE)) {
         throw new CookieException();
      }
   }

   public function Set()
   {
      $views = User::PROFILE_VIEWS_FLD;
      global $db;
      try {
         $db->Query('UPDATE ' . User::TABLE . " SET $views = $views + 1 WHERE " . User::ID_FLD . ' = ?', [$this->user_id]);
      } catch (DBException $e) {
         throw new CookieException();
      }
      setcookie(
         static::COOKIENAME . $this->user_id,
         $this->_Package([$this->user_id]),
         time() + static::EXPIRATION,
         static::COOKIEPATH,
         static::COOKIEDOMAIN
      );
   }

}