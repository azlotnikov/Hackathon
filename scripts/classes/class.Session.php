<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';

class Session extends Entity
{
   const SID_FLD  = 'sid_id';
   const USER_FLD = 'user_id';

   const TABLE = 'sessions';

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         new Field(
            static::USER_FLD,
            IntType(),
            true,
            '',
            Array(Validate::IS_NOT_EMPTY)
         ),
         new Field(
            static::SID_FLD,
            StrType(40),
            true,
            'идентификатор сессии',
            Array(Validate::IS_NOT_EMPTY)
         )
      );
   }

   public function Authorize($user_id)
   {
      global $db;
      $sid = uniqid($user_id);
      $db->Query(SQL::GetCallQuery('add_user_session', 2), [$user_id, $sid]);
      return $sid;
   }

   public function GetBySID($sid)
   {
      $this->CheckSearch();
      $this->selectFields =SQL::GetListFieldsForSelect(
         SQL::PrepareFieldsForSelect(
            static::TABLE,
            [$this->idField, $this->GetFieldByName(static::USER_FLD)]
         )
      );
      $this->search->AddClause(
         CCond(
            CF(static::TABLE, $this->GetFieldByName(static::SID_FLD)),
            CVP($sid),
            cAND
         )
      );
      $result = $this->GetPart();
      $this->search->RemoveClause();
      return $result;
   }

}

$_session = new Session();