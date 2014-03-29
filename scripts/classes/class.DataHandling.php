<?php
class DataHandling
{
   public function ValidateNum($num, $message)
   {
      if (!is_numeric($num)) {
         throw new Exception($message);
      }
      return $this;
   }

   public function ValidatePositiveNum($num, $message = '')
   {
      if (!is_numeric($num) || $num <= 0) {
         throw new Exception($message);
      }
      return $this;
   }

   public function ValidateLogin($login, $message = ERROR_LOGIN_LEN)
   {
      if (strlen($login) < LOGIN_LEN) throw new Exception($message);
      return $this;
   }

   public function ValidatePassword($pass, $message = ERROR_PASS_LEN)
   {
      if (strlen($pass) < PASS_LEN) throw new Exception($message);
      return $this;
   }

   public function ValidateRepeatPasswords($pass1, $pass2, $message = ERROR_OLD_NEW_PASS)
   {
      if ($pass1 != $pass2) throw new Exception($message);
      return $this;
   }

   public function ValidatePhone($phone, $message = ERROR_CONTACT_PHONE)
   {
      if (!preg_match('/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/', $phone)) {
         throw new Exception($message);
      }
      return $this;
   }

   function UnixToMySQL($timestamp)
   {
      return date('Y-m-d H:i:s', $timestamp);
   }

}