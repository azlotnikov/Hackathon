<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Image.php';

class User extends Entity
{
   const NAME_FLD          = 'name';
   const PASS_FLD          = 'password';
   const SALT_FLD          = 'salt';
   const SITE_FLD          = 'site';
   const PHOTO_FLD         = 'photo_id';
   const SKYPE_FLD         = 'skype';
   const PHONE_FLD         = 'phone';
   const EMAIL_FLD         = 'email';
   const SURNAME_FLD       = 'surname';
   const IS_PHOTO_FLD      = 'is_photographer';
   const IS_VIDEO_FLD      = 'is_videographer';
   const DESCRIPTION_FLD   = 'description';
   const LAST_UPDATE_FLD   = 'last_update';
   const VERIFICATION_FLD  = 'verification';
   const REGISTER_DATE_FLD = 'register_date';
   const PROFILE_VIEWS_FLD = 'profile_views';

   const EMAIL_SCHEME              = 2;
   const LOGIN_SCHEME              = 3;
   const TYPE_INFO_SCHEME          = 4;
   const NAME_INFO_SCHEME          = 5;
   const ACTIVATION_SCHEME         = 6;
   const EXTRA_DATA_SCHEME         = 7;
   const ADMIN_INFO_SCHEME         = 8;
   const PROFILE_INFO_SCHEME       = 9;
   const CONTACT_INFO_SCHEME       = 10;
   const REGISTRATION_CHECK_SCHEME = 11;
   const PHOTOSESSIONS_INFO_SCHEME = 12;
   const VIDEOSESSIONS_INFO_SCHEME = 13;

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
            static::EMAIL_FLD,
            StrType(120),
            true,
            'Email',
            Array(Validate::IS_NOT_EMPTY, Validate::IS_EMAIL)
         ),
         new Field(
            static::SITE_FLD,
            StrType(60),
            true
         ),
         new Field(
            static::SKYPE_FLD,
            StrType(30),
            true
         ),
         new Field(
            static::PHONE_FLD,
            StrType(30),
            true
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
            static::IS_PHOTO_FLD,
            IntType(),
            true,
            '',
            Array(Validate::IS_BOOL)
         ),
         new Field(
            static::IS_VIDEO_FLD,
            IntType(),
            true,
            '',
            Array(Validate::IS_BOOL)
         ),
         new Field(
            static::VERIFICATION_FLD,
            IntType(),
            true,
            '',
            Array(Validate::IS_BOOL)
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
         $this->GetFieldByName(static::EMAIL_FLD),
         $this->GetFieldByName(static::SITE_FLD),
         $this->GetFieldByName(static::SKYPE_FLD),
         $this->GetFieldByName(static::PHONE_FLD),
         $this->GetFieldByName(static::DESCRIPTION_FLD),
         $this->GetFieldByName(static::IS_PHOTO_FLD),
         // $this->GetFieldByName(static::PHOTO_FLD),
         $this->GetFieldByName(static::IS_VIDEO_FLD),
         $this->GetFieldByName(static::REGISTER_DATE_FLD),
         $this->GetFieldByName(static::LAST_UPDATE_FLD),
         $this->GetFieldByName(static::PROFILE_VIEWS_FLD)
      );
      $this->orderFields = [static::PROFILE_VIEWS_FLD => new OrderField(static::TABLE, $this->GetFieldByName(static::PROFILE_VIEWS_FLD))];
   }

   public function SetAccSelf($acc_self)
   {
      $this->acc_self = $acc_self;
   }

   public function ModifySample(&$sample)
   {
      if (empty($sample)) return;
      $part_format = 'd.m.Y';
      $general_format = 'd.m.Y H:i';
      switch ($this->samplingScheme) {
         case static::PROFILE_INFO_SCHEME:
            $registerKey   = $this->ToPrfxNm(static::REGISTER_DATE_FLD);
            $lastUpdateKey = $this->ToPrfxNm(static::LAST_UPDATE_FLD);
            $isPhoto = &$sample[0][$this->ToPrfxNm(static::IS_PHOTO_FLD)];
            $isVideo = &$sample[0][$this->ToPrfxNm(static::IS_VIDEO_FLD)];
            $sample[0][$registerKey]   = (new DateTime($sample[0][$registerKey]))->format($part_format);
            $sample[0][$lastUpdateKey] = (new DateTime($sample[0][$lastUpdateKey]))->format($general_format);
            $isPhoto = $isPhoto ? 'Фотограф'  : null;
            $isVideo = $isVideo ? 'Видеограф' : null;
            if (!empty($isPhoto) && (!empty($isVideo))) {
               $isVideo = ', видеограф';
            }
            break;

         case static::ADMIN_INFO_SCHEME:
            $registerKey     = $this->ToPrfxNm(static::REGISTER_DATE_FLD);
            $lastUpdateKey   = $this->ToPrfxNm(static::LAST_UPDATE_FLD);
            $verificationKey = $this->ToPrfxNm(static::VERIFICATION_FLD);
            foreach ($sample as &$set) {
               $set[$registerKey]     = (new DateTime($set[$registerKey]))->format($part_format);
               $set[$lastUpdateKey]   = (new DateTime($set[$lastUpdateKey]))->format($general_format);
               $set[$verificationKey] = $set[$verificationKey] ? 'Подтверждено' : 'Не подтверждено';
            }
            break;
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
               SQL::PrepareFieldsForSelect(
                  static::TABLE,
                  Array(
                     $this->idField,
                     $this->GetFieldByName(static::VERIFICATION_FLD),
                     $this->GetFieldByName(static::REGISTER_DATE_FLD)
                  )
               );
            break;

         case static::EMAIL_SCHEME:
            $fields =
               SQL::PrepareFieldsForSelect(
                  static::TABLE,
                  Array(
                     $this->idField,
                     $this->GetFieldByName(static::VERIFICATION_FLD),
                     $this->GetFieldByName(static::PASS_FLD)
                  )
               );
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

         case static::ACTIVATION_SCHEME:
            $fields =
               SQL::PrepareFieldsForSelect(
                  static::TABLE,
                  Array(
                     $this->idField,
                     $this->GetFieldByName(static::PASS_FLD),
                     $this->GetFieldByName(static::VERIFICATION_FLD),
                     $this->GetFieldByName(static::REGISTER_DATE_FLD)
                  )
               );
            break;

         case static::ADMIN_INFO_SCHEME:
            $fields =
               SQL::PrepareFieldsForSelect(
                  static::TABLE,
                  array_merge(
                     $this->profileFields,
                     Array($this->GetFieldByName(static::VERIFICATION_FLD))
                  )
               );
            $fields[] = ImageWithFlagSelectSQL(static::TABLE, $this->GetFieldByName(static::PHOTO_FLD));
            $this->AddOrder(static::PROFILE_VIEWS_FLD, OT_DESC);
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

         case static::TYPE_INFO_SCHEME:
            $fields =
               SQL::PrepareFieldsForSelect(
                  static::TABLE,
                  Array(
                     $this->GetFieldByName(static::IS_PHOTO_FLD),
                     $this->GetFieldByName(static::IS_VIDEO_FLD)
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

   public function Insert($getLastInsertId = false)
   {
      global $db;
      $resID = -1;
      try {
         $db->link->beginTransaction();
         $resID = Entity::Insert($getLastInsertId);
         $db->Query('CALL remove_old_users_info()');
         $db->link->commit();
      } catch (DBException $e) {
         $db->link->rollback();
         throw new Exception($e->getMessage());
      }
      return $resID;
   }

   public function UpdateByEmail($email)
   {
      global $db;
      list($names, $params) = $this->SetChangeParams();
      $query    = SQL::GetUpdateQuery(static::TABLE, $names, static::EMAIL_FLD);
      $params[] = $email;
      return $db->Query($query, $params);
   }

   public function GetById($id)
   {
      $scheme = $this->samplingScheme;
      if ($scheme == static::PROFILE_INFO_SCHEME || $scheme == static::ADMIN_INFO_SCHEME) {
         $this->search->SetJoins([], [$id]);
      }
      return parent::GetById($id);
   }

   public function GetByEmail($email)
   {
      $this->CheckSearch();
      $this->search->AddClause(
         CCond(
            CF(static::TABLE, $this->GetFieldByName(static::EMAIL_FLD)),
            CVP($email),
            'AND'
         )
      );
      $scheme = $this->samplingScheme;
      if ($scheme == static::PROFILE_INFO_SCHEME || $scheme == static::ADMIN_INFO_SCHEME) {
         $this->search->SetJoins([], [$email]);
      }
      $result = $this->GetPart();
      $this->search->RemoveClause();
      return $result;
   }

   public function DeleteByEmail($email)
   {
     global $db;
     $db->Query('DELETE FROM ' . static::TABLE . ' WHERE email = ?', Array($email));
   }

}

$_user = new User;

class UserSessionSpec extends Entity
{
   const USER_FLD     = 'user_id';
   const CATEGORY_FLD = 'category_id';

   public function ModifySample(&$sample)
   {
      $arr = [];
      foreach ($sample as &$set) {
         $arr[$set[$this->ToPrfxNm(static::CATEGORY_FLD)]] = true;
      }
      $sample = $arr;
   }

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         new Field(
            static::CATEGORY_FLD,
            IntType(),
            true
         ),
         new Field(
            static::USER_FLD,
            IntType(),
            true
         )
      );
   }

   public function GetUserSpec($user_id, $email)
   {
      global $_user;
      $this->search = new Search(
         static::TABLE,
         new Clause(
            CCond(
               CF(
                  User::TABLE,
                  $_user->GetFieldByName(
                     !empty($user_id) ? User::ID_FLD : User::EMAIL_FLD
                  )
               ),
               CVP(!empty($user_id) ? $user_id : $email),
               'AND'
            )
         ),
         [User::TABLE => [null, [static::USER_FLD, User::ID_FLD]]]
      );
      return $this->GetAll();
   }

   public function UpdateSpec($spec, $id)
   {
      global $db;
      try {
         $db->link->beginTransaction();
         $query = 'DELETE FROM '
                . static::TABLE
                . ' WHERE '
                . $this->ToTblNm(static::USER_FLD)
                . ' = ?';
         $db->Query($query, [$id]);
         $this->SetFieldByName(static::USER_FLD, $id);
         foreach ($spec as $id => $value) {
            if ($value) {
               $this->SetFieldByName(static::CATEGORY_FLD, $id)->Insert();
            }
         }
         $db->link->commit();
      } catch (DBException $e) {
         $db->link->rollback();
         throw new Exception($e->getMessage());
      }
   }
}

class UserPSSpec extends UserSessionSpec
{
   const TABLE = 'user_ps_spec';
}

class UserVSSpec extends UserSessionSpec
{
   const TABLE = 'user_vs_spec';
}

function GetUserSpec($user_id, $email)
{
   $_userPSSpec = new UserPSSpec;
   $_userVSSpec = new UserVSSpec;
   return Array('ps' => $_userPSSpec->GetUserSpec($user_id, $email), 'vs' => $_userVSSpec->GetUserSpec($user_id, $email));
}