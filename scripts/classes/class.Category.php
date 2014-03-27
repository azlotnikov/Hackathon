<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';

class Category extends Entity
{
   const NAME_FLD = 'name';

   public function ModifySample(&$sample)
   {
      $arr = [];
      foreach ($sample as &$set) {
         $arr[$set[$this->ToPrfxNm(static::ID_FLD)]] = $set[$this->ToPrfxNm(static::NAME_FLD)];
      }
      $sample = $arr;
   }

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         new Field(
            static::NAME_FLD,
            StrType(20),
            false
         )
      );
   }
}

class PSCategory extends Category
{
   const TABLE = 'ps_categories';
}

class VSCategory extends Category
{
   const TABLE = 'vs_categories';
}

$_psCategory = new PSCategory();
$_vsCategory = new VSCategory();