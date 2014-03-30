<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Place.php';

define('etPARTY', 2);
define('etSERVICE', 1);
define('etLEISURE', 3);

class Event extends Entity
{
   const TABLE             = 'events';
   const TYPE_FLD          = 'event_type';
   const HEAD_FLD          = 'header';
   const PLACE_FLD         = 'place_id';
   const OWNER_FLD         = 'owner_id';
   const DUE_DATE_FLD      = 'due_date';
   const DESCRIPTION_FLD   = 'description';
   const CREATION_DATE_FLD = 'creation_date';

   const INIT_SCHEME = 2;
   const INFO_SCHEME = 3;

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         new Field(
            static::DESCRIPTION_FLD,
            TextType(),
            true
         ),
         new Field(
            static::HEAD_FLD,
            StrType(100),
            true,
            'Инициатор',
            Array(Validate::IS_NUMERIC, Validate::IS_NOT_EMPTY)
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
            static::DUE_DATE_FLD,
            TimestampType(),
            true
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
            $placeKey = $this->ToPrfxNm(static::PLACE_FLD);
            foreach ($sample as $event) {
               if ($event[$typeKey] == etPARTY) {
                  $parties[] = [$idKey => $event[$idKey], $placeKey => $event[$placeKey]];
               } elseif ($event[$typeKey] == etSERVICE) {
                  $services[] = [$idKey => $event[$idKey], $placeKey => $event[$placeKey]];
               } elseif ($event[$typeKey] == etLEISURE) {
                  $leisuries[] = [$idKey => $event[$idKey], $placeKey => $event[$placeKey]];
               }
            }
            $sample = [
               etPARTY   => $parties,
               etSERVICE => $services,
               etLEISURE => $leisuries
            ];
            break;

         case static::INFO_SCHEME:
            $part_format = 'd.m.Y';
            $general_format = 'd.m.Y H:i';
            $idKey         = $this->ToPrfxNm(static::ID_FLD);
            $dueDateKey    =  $this->ToPrfxNm(static::DUE_DATE_FLD);
            $createDateKey =  $this->ToPrfxNm(static::CREATION_DATE_FLD);
            $result = [];
            foreach ($sample as &$set) {
               if ($set[$dueDateKey]) {
                  $set[$dueDateKey] = (new DateTime($set[$dueDateKey]))->format($part_format);
               }
               $set[$createDateKey] = (new DateTime($set[$createDateKey]))->format($general_format);
               $result[$set[$idKey]] = $set;
            }
            $sample = $result;
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
               [
                  $this->idField,
                  $this->GetFieldByName(static::TYPE_FLD),
                  $this->GetFieldByName(static::PLACE_FLD)
               ]
            );
            break;

         case static::INFO_SCHEME:
            global $_user;
            $fields =
               array_merge(
                  SQL::PrepareFieldsForSelect(
                     static::TABLE,
                     [
                        $this->idField,
                        $this->GetFieldByName(static::HEAD_FLD),
                        $this->GetFieldByName(static::DUE_DATE_FLD),
                        $this->GetFieldByName(static::DESCRIPTION_FLD),
                        $this->GetFieldByName(static::CREATION_DATE_FLD)
                     ]
                  ),
                  SQL::PrepareFieldsForSelect(
                     User::TABLE,
                     [$_user->GetFieldByName(User::NAME_FLD), $_user->GetFieldByName(User::SURNAME_FLD)]
                  )
               );
               break;

      }
      // SQL::PrepareFieldsForSelect(EventType::TABLE, [$_eventType->GetFieldByName(PlaceType::TYPENAME_FLD)])
      // $this->search->SetJoins([EventType::TABLE => [null, [static::TYPE_FLD, EventType::ID_FLD]]]);
      $this->selectFields = SQL::GetListFieldsForSelect($fields);
   }

   public function ProcessEvent($type, $data)
   {
      //Не забыть разобраться с форматом даты а то будет пиздец
      extract($data);
      global $db;
      return $db->Query(
         SQL::GetCallFuncQuery('process_event', 'result', 8),
         [
            $_SESSION['sid'],
            $type,
            (!empty($eid) ? $eid : 0),
            $header,
            (!empty($place_id) ? $place_id : -1),
            $event_type,
            $description,
            (!empty($due_date) ? $due_date : null) //if not party then due date must be empty
         ]
      )[0]['result'];
   }

   public function GetEventInfo($ids)
   {
      $this->search = new Search(
         static::TABLE,
         null,
         [User::TABLE => [null, [static::OWNER_FLD, User::ID_FLD]]]
      );
      foreach ($ids as $id) {
         $this->search->AddClause(
            CCond(
               CF(static::TABLE, $this->GetFieldByName(static::ID_FLD)),
               CVP($id),
               cOR
            )
         );
      }
      return $this->SetSamplingScheme(static::INFO_SCHEME)->GetAll();
   }

}

$_event = new Event();
