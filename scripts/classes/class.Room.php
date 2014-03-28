<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';

class Room extends Entity
{
   const NUMBER_FLD      = 'number';
   const FLOOR_FLD       = 'floor_id';
   const LAST_UPDATE_FLD = 'last_update';
   const POLYGON_FLD     = 'polygon';

   const TABLE = 'rooms';

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
         )
      );
   }
}