<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Place.php';

define('etPARTY', 'Мероприятия');
define('etSERVICE', 'Услуги');
define('etLEISURE', 'Досуг');

class Event extends Entity
{
   const TABLE             = 'events';
   const OWNER_FLD         = 'owner_id';
   const TYPE_FLD          = 'event_type';
   const PLACE_FLD         = 'place_id';
   const DUE_DATE_FLD      = 'due_date';
   const DESCRIPTION_FLD   = 'description';
   const CREATION_DATE_FLD = 'creation_date';

   const INIT_SCHEME = 2;

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         new Field(
            static::DESCRIPTION_FLD,
            TextType(),
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
            static::PLACE_FLD,
            IntType(),
            true,
            'Место',
            Array(Validate::IS_NOT_EMPTY)
         )
      );
   }

   public function ModifySample(&$sample)
   {
      if (empty($sample)) return;
      switch ($this->samplingScheme) {
         case static::INIT_SCHEME:
            $parties = $services = $leisuries = [];
            $idKey = $this->ToPrfxNm(static::ID_FLD);
            $typeKey = $this->ToPrfxNm(static::TYPE_FLD);
            foreach ($sample as $event) {
               if ($event[$typeKey] == etPARTY) {
                  $parties[] = $event[$idKey];
               } elseif ($event[$typeKey] == etSERVICE) {
                  $services[] = $event[$idKey];
               } elseif ($event[$typeKey] == etPARTY) {
                  $leisuries[] = $event[$idKey];
               }
            }
            $sample = [
               etPARTY   => $parties,
               etSERVICE => $services,
               etLEISURE => $leisuries
            ];
            break;

         // case static:::
         //    break;

      }
      // if ($this->samplingScheme == static::PROFILE_INFO_SCHEME) {
      //    $registerKey   = $this->ToPrfxNm(static::REGISTER_DATE_FLD);
      //    $lastUpdateKey = $this->ToPrfxNm(static::LAST_UPDATE_FLD);
      //    $sample[0][$registerKey]   = (new DateTime($sample[0][$registerKey]))->format('d.m.Y');
      //    $sample[0][$lastUpdateKey] = (new DateTime($sample[0][$lastUpdateKey]))->format('d.m.Y H:i');
      // }
   }

   public function SetSelectValues()
   {
      if ($this->TryToApplyUsualScheme()) return;
      $this->CheckSearch();
      $fields = Array();
      switch ($this->samplingScheme) {
         case static::INIT_SCHEME:
            global $_eventType;
            $fields = SQL::PrepareFieldsForSelect(
               static::TABLE,
               [$this->idField, $this->GetFieldByName(static::TYPE_FLD)]
            );
            break;

         // case static:::
         //    break;

      }
      // SQL::PrepareFieldsForSelect(EventType::TABLE, [$_eventType->GetFieldByName(PlaceType::TYPENAME_FLD)])
      // $this->search->SetJoins([EventType::TABLE => [null, [static::TYPE_FLD, EventType::ID_FLD]]]);
      $this->selectFields = SQL::GetListFieldsForSelect($fields);
   }

}

$_event = new Event();
