<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.User.php';

class UserForgottenPass extends Entity
{
   const USER_FLD = 'user_id';
   const PASS_FLD = 'new_password';
   const SALT_FLD = 'new_salt';
   const DATE_FLD = 'change_date';

   const TABLE = 'users_forgotten_pass';

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         new Field(
            static::USER_FLD,
            IntType(),
            true
         ),
         new Field(
            static::PASS_FLD,
            StrType(80),
            true,
            'Пароль',
            Array(Validate::IS_NOT_EMPTY)
         ),
         new Field(
            static::SALT_FLD,
            StrType(8),
            true,
            '',
            Array(Validate::IS_NOT_EMPTY)
         ),
         new Field(
            static::DATE_FLD,
            TimestampType(),
            true
         ),
      );
   }

   public function GetByEmail($email)
   {
      global $_user;
      $this->CheckSearch();
      $this->search->AddClause(
         CCond(
            CF(User::TABLE, $_user->GetFieldByName(User::EMAIL_FLD)),
            CVP($email),
            'AND'
         )
      )->SetJoins([User::TABLE => [null, [static::USER_FLD, User::ID_FLD]]]);
      $result = $this->GetPart();
      $this->search->RemoveClause();
      return $result;
   }

   public function Insert($userId, $pass, $salt)
   {
      global $db;
      try {
         $db->link->beginTransaction();
         $db->Query('DELETE FROM ' . static::TABLE . ' WHERE ' . static::USER_FLD . ' = ?', [$userId]);
         $this->SetFieldByName(static::USER_FLD, $userId)
              ->SetFieldByName(static::PASS_FLD, $pass)
              ->SetFieldByName(static::SALT_FLD ,$salt)
              ->SetFieldByName(static::DATE_FLD, date(GENERAL_DATE_FORMAT));
         parent::Insert();
         $db->link->commit();
      } catch (DBException $e) {
         $db->link->rollback();
         throw new Exception($e->getMessage());
      }
   }

   public function AcceptNewPassword($ui)
   {
      global $db, $_user;
      try {
         $db->link->beginTransaction();
         $user_id = $ui[$this->ToPrfxNm(static::USER_FLD)];
         $_user->SetFieldByName(User::ID_FLD, $user_id)
               ->SetFieldByName(User::PASS_FLD, $ui[$this->ToPrfxNm(static::PASS_FLD)])
               ->SetFieldByName(User::SALT_FLD, $ui[$this->ToPrfxNm(static::SALT_FLD)])
               ->Update();
         $this->Delete($ui[$this->ToPrfxNm(static::ID_FLD)]);
         $new_pass = $_user->SetSamplingScheme(User::EMAIL_SCHEME)->GetById($user_id)[$_user->ToPrfxNm(User::PASS_FLD)];
         $db->link->commit();
      } catch (DBException $e) {
         $db->link->rollback();
         throw new Exception($e->getMessage());
      }
      return $new_pass;
   }
}

$_userFPass = new UserForgottenPass;