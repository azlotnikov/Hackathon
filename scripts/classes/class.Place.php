<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';

class Place extends Entity
{
   const NUMBER_FLD      = 'number';
   const FLOOR_FLD       = 'floor_id';
   const LAST_UPDATE_FLD = 'last_update';
   const POLYGON_FLD     = 'polygon';
   const TYPE_FLD        = 'place_type';

   const TABLE = 'places';

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         new Field(
            static::NUMBER_FLD,
            IntType(),
            true,
            'Номер',
            Array(Validate::IS_NUMERIC)
         ),
         new Field(
            static::LAST_UPDATE_FLD,
            TimestampType(),
            true
         ),
         new Field(
            static::FLOOR_FLD,
            IntType(),
            true
         ),
         new Field(
            static::POLYGON_FLD,
            StrType(500),
            true
         ),
         new Field(
            static::TYPE_FLD,
            IntType(),
            true,
            'Тип'
         )
      );
   }

   public function ModifySample(&$sample)
   {
      if (empty($sample)) return;
      foreach ($sample as &$set) {
         //

      }
      undef($set);
   }
}