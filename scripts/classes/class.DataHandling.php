<?php
class DataHandling
{
   public function validateForm($form_vars, $message = ERROR_FORM_FILL)
   {
      $result = true;
      foreach ($form_vars as $key => $value) {
         if (!isset($key) || empty($value)) {
            $result = false;
         }
      }
      if (!count($form_vars) || !$result) throw new Exception($message);
      return $this;
   }

   public function validateEmail($mail, $message = INCORRECT_MAIL)
   {
      if (!preg_match('/^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/', $mail)) {
         throw new Exception($message);
      }
      return $this;
   }

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

   public function validateLogin($login, $message = ERROR_LOGIN_LEN)
   {
      if (strlen($login) < LOGIN_LEN) throw new Exception($message);
      return $this;
   }

   public function validatePassword($pass, $message = ERROR_PASS_LEN)
   {
      if (strlen($pass) < PASS_LEN) throw new Exception($message);
      return $this;
   }

   public function validateRepeatPasswords($pass1, $pass2, $message = ERROR_OLD_NEW_PASS)
   {
      if ($pass1 != $pass2) throw new Exception($message);
      return $this;
   }

   public function validatePhone($phone, $message = ERROR_CONTACT_PHONE)
   {
      if (!preg_match('/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/', $phone)) {
         throw new Exception($message);
      }
      return $this;
   }

   function unixToMySQL($timestamp)
   {
      return date('Y-m-d H:i:s', $timestamp);
   }

}