<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';

class Floor extends Entity
{
   const NUMBER_FLD = 'number';

   const TABLE = 'floors';

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         new Field(
            static::NUMBER_FLD,
            IntType(),
            false
         )
      );
   }
}

$_floor = new Floor();