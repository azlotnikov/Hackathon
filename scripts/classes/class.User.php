<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Image.php';

class User extends Entity
{
   const NAME_FLD          = 'name';
   const PASS_FLD          = 'password';
   const SALT_FLD          = 'salt';
   const ROOM_FLD          = 'room';
   const LOGIN_FLD         = 'login';
   const PHOTO_FLD         = 'photo_id';
   const PHONE_FLD         = 'phone';
   const SURNAME_FLD       = 'surname';
   const DESCRIPTION_FLD   = 'description';
   const LAST_UPDATE_FLD   = 'last_update';
   const REGISTER_DATE_FLD = 'register_date';
   const PROFILE_VIEWS_FLD = 'profile_views';

   const LOGIN_SCHEME              = 2;
   const NAME_INFO_SCHEME          = 3;
   const EXTRA_DATA_SCHEME         = 4;
   const PROFILE_INFO_SCHEME       = 5;
   const CONTACT_INFO_SCHEME       = 6;
   const REGISTRATION_CHECK_SCHEME = 7;

   const TABLE = 'users';

   private
      $acc_self = false,
      $profileFields;

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         new Field(
            static::LOGIN_FLD,
            StrType(70),
            true,
            'Логин',
            Array(Validate::IS_NOT_EMPTY)
         ),
         new Field(
            static::NAME_FLD,
            StrType(70),
            true,
            'Имя',
            Array(Validate::IS_NOT_EMPTY)
         ),
         new Field(
            static::SURNAME_FLD,
            StrType(70),
            true
         ),
         new Field(
            static::PHONE_FLD,
            StrType(30),
            true
         ),
         new Field(
            static::ROOM_FLD,
            IntType(),
            true,
            'Комната',
            Array(Validate::IS_NOT_EMPTY)
         ),
         new Field(
            static::DESCRIPTION_FLD,
            TextType(),
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
            static::REGISTER_DATE_FLD,
            TimestampType(),
            true
         ),
         new Field(
            static::LAST_UPDATE_FLD,
            TimestampType(),
            true
         ),
         new Field(
            static::PROFILE_VIEWS_FLD,
            IntType(),
            true
         ),
         new Field(
            static::PHOTO_FLD,
            IntType(),
            true
         )
      );
      $this->profileFields = Array(
         $this->idField,
         $this->GetFieldByName(static::NAME_FLD),
         $this->GetFieldByName(static::SURNAME_FLD),
         $this->GetFieldByName(static::PHONE_FLD),
         $this->GetFieldByName(static::DESCRIPTION_FLD),
         // $this->GetFieldByName(static::PHOTO_FLD),
         $this->GetFieldByName(static::REGISTER_DATE_FLD),
         $this->GetFieldByName(static::LAST_UPDATE_FLD),
         $this->GetFieldByName(static::PROFILE_VIEWS_FLD)
      );
   }

   public function SetAccSelf($acc_self)
   {
      $this->acc_self = $acc_self;
   }

   public function ModifySample(&$sample)
   {
      if (empty($sample)) return;
      if ($this->samplingScheme == static::PROFILE_INFO_SCHEME) {
         $registerKey   = $this->ToPrfxNm(static::REGISTER_DATE_FLD);
         $lastUpdateKey = $this->ToPrfxNm(static::LAST_UPDATE_FLD);
         $sample[0][$registerKey]   = (new DateTime($sample[0][$registerKey]))->format('d.m.Y');
         $sample[0][$lastUpdateKey] = (new DateTime($sample[0][$lastUpdateKey]))->format('d.m.Y H:i');
      }
   }

   public function SetSelectValues()
   {
      if ($this->TryToApplyUsualScheme()) return;
      $this->CheckSearch();
      $fields = Array();
      switch ($this->samplingScheme) {
         case static::REGISTRATION_CHECK_SCHEME:
            $fields =
               SQL::PrepareFieldsForSelect(static::TABLE, [$this->idField]);
            break;

         case static::LOGIN_SCHEME:
            $fields =
               SQL::PrepareFieldsForSelect(
                  static::TABLE,
                  Array(
                     $this->idField,
                     $this->GetFieldByName(static::PASS_FLD),
                     $this->GetFieldByName(static::SALT_FLD)
                  )
               );
            break;

         case static::PROFILE_INFO_SCHEME:
            $fields =
               SQL::PrepareFieldsForSelect(
                  static::TABLE,
                  $this->profileFields
               );
            $fields[] = ImageWithFlagSelectSQL(static::TABLE, $this->GetFieldByName(static::PHOTO_FLD));
            $fields[] = '(SELECT get_user_photo_amount_by_' . ($this->acc_self ? 'email' : 'id' ) . '(?)) as work_amount';
            break;

         case static::EXTRA_DATA_SCHEME:
            $fields =
               SQL::PrepareFieldsForSelect(
                  static::TABLE,
                  Array($this->GetFieldByName(static::DESCRIPTION_FLD))
               );
            break;

         case static::CONTACT_INFO_SCHEME:
            $fields =
               SQL::PrepareFieldsForSelect(
                  static::TABLE,
                  Array(
                     $this->GetFieldByName(static::SITE_FLD),
                     $this->GetFieldByName(static::SKYPE_FLD),
                     $this->GetFieldByName(static::PHONE_FLD)
                  )
               );
            break;

         case static::NAME_INFO_SCHEME:
            $fields =
               SQL::PrepareFieldsForSelect(
                  static::TABLE,
                  Array(
                     $this->GetFieldByName(static::NAME_FLD),
                     $this->GetFieldByName(static::SURNAME_FLD)
                  )
               );
            break;
      }
      $this->selectFields = SQL::GetListFieldsForSelect($fields);
   }

   // public function UpdateByEmail($email)
   // {
   //    global $db;
   //    list($names, $params) = $this->SetChangeParams();
   //    $query    = SQL::GetUpdateQuery(static::TABLE, $names, static::EMAIL_FLD);
   //    $params[] = $email;
   //    return $db->Query($query, $params);
   // }

   // public function GetById($id)
   // {
   //    $scheme = $this->samplingScheme;
   //    if ($scheme == static::PROFILE_INFO_SCHEME) {
   //       $this->search->SetJoins([], [$id]);
   //    }
   //    return parent::GetById($id);
   // }

   public function GetByLogin($login)
   {
      $this->CheckSearch();
      $this->search->AddClause(
         CCond(
            CF(static::TABLE, $this->GetFieldByName(static::LOGIN_FLD)),
            CVP($login),
            cAND
         )
      );
      $result = $this->GetPart();
      $this->search->RemoveClause();
      return $result;
   }

   public function DeleteByLogin($login)
   {
     global $db;
     $db->Query('DELETE FROM ' . static::TABLE . ' WHERE login = ?', [$login]);
   }

}

$_user = new User();