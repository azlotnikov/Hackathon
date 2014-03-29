<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';

class PlaceType extends Entity
{
   const TYPENAME_FLD = 'type_name';

   const TABLE = 'place_types';

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         new Field(
            static::TYPENAME_FLD,
            StrType(300),
            false
         )
      );
   }
}

$_placeType = new PlaceType();