<?php

class Encryption
{
   private static $cypher = 'blowfish';
   private static $mode = 'cfb';
   private static $key = 'I want to get a nine for this job';

   public static function Encrypt($plaintext)
   {
      /*$td = mcrypt_module_open(static::$cypher, '', self::$mode, '');
      $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
      mcrypt_generic_init($td, self::$key, $iv);
      $crypttext = mcrypt_generic($td, $plaintext);
      mcrypt_generic_deinit($td);
      return $iv . $crypttext;*/
      return $plaintext;
   }

   public static function Decrypt($crypttext)
   {
      /*$td = mcrypt_module_open(self::$cypher, '', self::$mode, '');
      $ivsize = mcrypt_enc_get_iv_size($td);
      $iv = substr($crypttext, 0, $ivsize);
      $crypttext = substr($crypttext, $ivsize);
      $plaintext = '';
      if ($iv) {
      mcrypt_generic_init($td, self::$key, $iv);
      $plaintext = mdecrypt_generic($td, $crypttext);
      mcrypt_generic_deinit($td);
      }
      return $plaintext;*/
      return $crypttext;
   }

   public static function GenerateRandomString($len = 7)
   {
      $pattern = "abcdefghiklmnopqrstvwxyzABCDEFGHIKLMNOPQRSTVWXYZ0123456789";
      $pattern_len = strlen($pattern);
      mt_srand((int)(microtime(true) * 0xFFFF));
      $salt = '';
      for ($i = 0; $i < $len; $i++) {
         $salt .= $pattern[mt_rand() % $pattern_len];
      }
      return $salt;
   }

}