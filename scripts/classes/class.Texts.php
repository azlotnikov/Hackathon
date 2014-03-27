<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';

class Texts extends Entity
{
   const ABOUT1_TEXT_ID = 1;
   const ABOUT2_TEXT_ID = 2;

   const NAME_FLD       = 'name';
   const TEXT_BODY_FLD  = 'text_body';

   const MAIN_SCHEME  = 2;
   const ABOUT_SCHEME = 3;
   const ADMIN_SCHEME = 4;

   const TABLE = 'texts';

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         new Field(
            static::NAME_FLD,
            StrType(150),
            false
         ),
         new Field(
            static::TEXT_BODY_FLD,
            TextType(),
            true
         )
      );
      $this->orderFields = [static::ID_FLD => new OrderField(static::TABLE, $this->GetFieldByName(static::ID_FLD))];
   }

   public function ModifySample(&$sample)
   {
      $res = [];
      switch ($this->samplingScheme) {
         case static::ADMIN_SCHEME:
         case static::ABOUT_SCHEME:
            foreach ($sample as $key => &$set) {
               ++$key;
               $res["about$key"] = $set;
            }
            break;

         case static::MAIN_SCHEME:
            $textKey = $this->ToPrfxNm(static::TEXT_BODY_FLD);
            $res['banner_top']    = $sample[0][$textKey];
            $res['banner_bottom'] = $sample[1][$textKey];
            break;
      }
      $sample = $res;
   }

   public function SetSelectValues()
   {
      $this->AddOrder(static::ID_FLD, OT_ASC);
      if ($this->TryToApplyUsualScheme()) return;
      $this->CheckSearch();
      $fields = Array();
      switch ($this->samplingScheme) {
         case static::ADMIN_SCHEME:
            $fields = SQL::PrepareFieldsForSelect(static::TABLE, $this->fields);
            break;

         case static::ABOUT_SCHEME:
            $fields = SQL::PrepareFieldsForSelect(static::TABLE, $this->fields);
            $this->search->AddClause($this->GetCondForText(ABOUT1_ID))->AddClause($this->GetCondForText(ABOUT2_ID));
            break;

         case static::MAIN_SCHEME:
            $fields = SQL::PrepareFieldsForSelect(
               static::TABLE,
               [$this->GetFieldByName(static::TEXT_BODY_FLD)]
            );
            $this->search->AddClause($this->GetCondForText(MAIN_BOTTOM_BANNER_ID))->AddClause($this->GetCondForText(MAIN_TOP_BANNER_ID));
            break;
      }
      $this->selectFields = SQL::GetListFieldsForSelect($fields);
   }

   private function GetCondForText($id)
   {
      return CCond(
         CF(static::TABLE, $this->GetFieldByName(static::ID_FLD)),
         CVP($id),
         'OR'
      );
   }

}

$_texts = new Texts();