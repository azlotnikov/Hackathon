<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';

class Event extends Entity
{
   const TABLE             = 'events';
   const DESCRIPTION_FLD   = 'description';
   const OWNER_FLD         = 'owner_id';
   const TYPE_FLD          = 'event_type';
   const CREATION_DATE_FLD = 'creation_date';
   const DELETION_DATE_FLD = 'deletion_date';



   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         new Field(
            static::DESCRIPTION_FLD,
            StrType(2000),
            true,
            'Описание',
            Array(Validate::IS_NOT_EMPTY_STRING)
         ),
         new Field(
            static::OWNER_FLD,
            IntType(),
            false,
            'Инициатор',
            Array(Validate::IS_NUMERIC, Validate::IS_NOT_EMPTY)
         ),
         new Field(
            static::TYPE_FLD,
            IntType(),
            true,
            'Вид события',
            Array(Validate::IS_NUMERIC, Validate::IS_NOT_EMPTY)
         ),
         new Field(
            static::CREATION_DATE_FLD,
            TimestampType(),
            true,
            'Время создания',
            Array(Validate::IS_NOT_EMPTY)
         ),
         new Field(
            static::DELETION_DATE_FLD,
            TimestampType(),
            true,
            'Время отмены',
            Array(Validate::IS_NOT_EMPTY)
         )
      );
   }


}