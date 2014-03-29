<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';

class EventType extends Entity
{
   const TABLE           = 'event_types';
   const TYPENAME_FLD    = 'type_name';

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         new Field(
            static::TYPENAME_FLD,
            StrType(200),
            false
         )
      );
   }
}