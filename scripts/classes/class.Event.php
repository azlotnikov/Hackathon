<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Place.php';

class Event extends Entity
{
   const TABLE             = 'events';
   const DESCRIPTION_FLD   = 'description';
   const OWNER_FLD         = 'owner_id';
   const TYPE_FLD          = 'event_type';
   const CREATION_DATE_FLD = 'creation_date';
   const DELETION_DATE_FLD = 'deletion_date';
   const PLACE_FLD         = 'place_id';

//   const ALL_SCHEME              = 1;
//   const NAME_INFO_SCHEME          = 3;
//   const EXTRA_DATA_SCHEME         = 4;
//   const PROFILE_INFO_SCHEME       = 5;
//   const CONTACT_INFO_SCHEME       = 6;
//   const REGISTRATION_CHECK_SCHEME = 7;



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
         ),
         new Field(
            static::PLACE_FLD,
            IntType(),
            true,
            'Место',
            Array(Validate::IS_NOT_EMPTY)
         )
      );
   }

   public function SetSelectValues()
   {
      global $_eventType;
      $this->CheckSearch();
      $this->selectFields = SQL::GetListFieldsForSelect(
         array_merge(
            SQL::PrepareFieldsForSelect(static::TABLE, $this->fields),
            SQL::PrepareFieldsForSelect(EventType::TABLE, [$_eventType->GetFieldByName(EventType::TYPENAME_FLD)]),
            SQL::PrepareFieldsForSelect(PlaceType::TABLE, [$_eventType->GetFieldByName(PlaceType::TYPENAME_FLD)])
         )
      );
      $this->search->SetJoins([
            EventType::TABLE => [null, [static::TYPE_FLD, EventType::ID_FLD], [static::PLACE_FLD, PlaceType::ID_FLD]]
      ]);
   }

}