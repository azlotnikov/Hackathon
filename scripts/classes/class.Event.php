<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Place.php';

define('etSERVICE', 1);
define('etPARTY', 2);
define('etLEISURE', 3);

$event_names = [etPARTY => 'Мероприятия', etSERVICE => 'Услуги', etLEISURE => 'Досуг'];

class Event extends Entity
{
   const TABLE             = 'events';
   const TYPE_FLD          = 'event_type';
   const HEAD_FLD          = 'header';
   const PLACE_FLD         = 'place_id';
   const OWNER_FLD         = 'owner_id';
   const DUE_DATE_FLD      = 'due_date';
   const DESCRIPTION_FLD   = 'description';
   const UPDATED_DATE_FLD  = 'updated_date';
   const CREATION_DATE_FLD = 'creation_date';
   const DELETION_DATE_FLD = 'deletion_date';

   const INIT_SCHEME          = 2;
   const INFO_SCHEME          = 3;
   const LIST_SCHEME          = 4;
   const NEW_DELETTION_SCHEME = 5;

   const LIST_LIMIT = 2;

   private
      $createDateKey   = null,
      $lastUpdatedDate = null;

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
            static::DELETION_DATE_FLD,
            TimestampType()
         ),
         new Field(
            static::UPDATED_DATE_FLD,
            TimestampType()
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
      $this->createDateKey = $this->ToPrfxNm(static::CREATION_DATE_FLD);
   }

   private function ModifyCreateDate(&$set)
   {
      $set[$this->createDateKey] = (new DateTime($set[$this->createDateKey]))->format('d.m.Y');
   }

   public function ModifySample(&$sample)
   {
      if (empty($sample)) return;
      $idKey = $this->ToPrfxNm(static::ID_FLD);
      switch ($this->samplingScheme) {
         case static::INIT_SCHEME:
            $parties = $services = $leisuries = [];
            $typeKey = $this->ToPrfxNm(static::TYPE_FLD);
            $placeKey = $this->ToPrfxNm(static::PLACE_FLD);
            foreach ($sample as $event) {
               if ($event[$typeKey] == etPARTY) {
                  $parties[$event[$idKey]] = [$idKey => $event[$idKey], $placeKey => $event[$placeKey]];
               } elseif ($event[$typeKey] == etSERVICE) {
                  $services[$event[$idKey]] = [$idKey => $event[$idKey], $placeKey => $event[$placeKey]];
               } elseif ($event[$typeKey] == etLEISURE) {
                  $leisuries[$event[$idKey]] = [$idKey => $event[$idKey], $placeKey => $event[$placeKey]];
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
            $dueDateKey    =  $this->ToPrfxNm(static::DUE_DATE_FLD);
            $result = [];
            foreach ($sample as &$set) {
               if ($set[$dueDateKey]) {
                  $set[$dueDateKey] = (new DateTime($set[$dueDateKey]))->format($part_format);
               }
               $this->ModifyCreateDate($set);
               $result[$set[$idKey]] = $set;
            }
            $sample = $result;
            break;

         case static::NEW_DELETTION_SCHEME:
            $result = [];
            foreach ($sample as &$set) {
               $result[] = $set[$idKey];
            }
            $sample = $result;
            break;

      }
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

         case static::NEW_DELETTION_SCHEME:
            $fields = SQL::PrepareFieldsForSelect(static::TABLE, [$this->idField]);
            $this->search = new Search(
               static::TABLE,
               new Clause(
                  CCond(
                     CF(static::TABLE, $this->GetFieldByName(static::DELETION_DATE_FLD)),
                     CVP($this->lastUpdatedDate),
                     null,
                     opGE
                  )
               )
            );
            break;

      }
      // SQL::PrepareFieldsForSelect(EventType::TABLE, [$_eventType->GetFieldByName(PlaceType::TYPENAME_FLD)])
      // $this->search->SetJoins([EventType::TABLE => [null, [static::TYPE_FLD, EventType::ID_FLD]]]);
      $this->selectFields = SQL::GetListFieldsForSelect($fields);
   }

   private function GenQueryForList($user_id, $limit, $start = 0)
   {
      global $_user, $_place;
      $fields = SQL::GetListFieldsForSelect(
         array_merge(
            SQL::PrepareFieldsForSelect(
               static::TABLE,
               [
                  $this->idField,
                  $this->GetFieldByName(static::HEAD_FLD),
                  $this->GetFieldByName(static::CREATION_DATE_FLD),
                  $this->GetFieldByName(static::TYPE_FLD)
               ]
            ),
            SQL::PrepareFieldsForSelect(
               User::TABLE,
               [
                  $_user->GetFieldByName(User::ID_FLD),
                  $_user->GetFieldByName(User::NAME_FLD),
                  $_user->GetFieldByName(User::SURNAME_FLD)
               ]
            ),
            SQL::PrepareFieldsForSelect(
               Place::TABLE,
               [
                  $_place->GetFieldByName(Place::TYPE_FLD),
                  $_place->GetFieldByName(Place::NUMBER_FLD)
               ]
            )
         )
      );
      return sprintf(
         'SELECT %s FROM %s %s WHERE %s = %s %s ORDER BY %s DESC LIMIT %d, %d',
         $fields,
         static::TABLE,
         SQL::MakeJoin(
            static::TABLE,
            [
               User::TABLE  => [null, [static::OWNER_FLD, User::ID_FLD]],
               Place::TABLE => [null, [static::PLACE_FLD, Place::ID_FLD]]
            ]
         ),
         $this->ToTblNm(static::TYPE_FLD),
         '%d',
         (!empty($user_id) ? sprintf('AND %s = ?', $_user->ToTblNm(User::ID_FLD)) : ''),
         $this->ToTblNm(static::CREATION_DATE_FLD),
         $start,
         $limit
      );
   }

   public function GetList($user_id = null)
   {
      $qryBase = sprintf('(%s)', $this->GenQueryForList($user_id, static::LIST_LIMIT));
      foreach (range(1, 3) as $i) {
         $qries[] = sprintf($qryBase, $i);
      }
      global $db;
      try {
         $result = $db->Query(implode(' UNION ', $qries), !empty($user_id) ? array_fill(0, 3, $user_id) : []);
         $typeKey = $this->ToPrfxNm(static::TYPE_FLD);
         global $event_names;
         $services = ['type_alias' => $event_names[etSERVICE], 'type_name' => 'services', 'type_key' => etSERVICE, 'events' => []];
         $leisure  = ['type_alias' => $event_names[etLEISURE], 'type_name' => 'leisure',  'type_key' => etLEISURE, 'events' => []];
         $parties  = ['type_alias' => $event_names[etPARTY],   'type_name' => 'parties',  'type_key' => etPARTY,   'events' => []];
         $support  = [etPARTY => &$parties, etSERVICE => &$services, etLEISURE => &$leisure];
         foreach ($result as &$event) {
            $this->ModifyCreateDate($event);
            $type = $event[$typeKey];
            unset($event[$typeKey]);
            $support[$type]['events'][] = $event;
         }
         $result = [$services, $leisure, $parties];
      } catch (Exception $e) {
         $result = [];
      }
      return $result;
   }

   public function GetMoreListByType($user_id = null, $amount, $type)
   {
      global $db;
      try {
         $result = $db->Query(
            sprintf(
               sprintf('(%s)', $this->GenQueryForList($user_id, static::LIST_LIMIT, $amount + 1)),
               $type
            ),
            (!empty($user_id) ? [$user_id] : [])
         );
         foreach ($result as &$event) {
            $this->ModifyCreateDate($event);
         }
      } catch (Exception $e) {
         $result = [];
      }
      return $result;
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

   public function GetNewInfo($lastUpdatedDate)
   {
      $this->lastUpdatedDate = $lastUpdatedDate;
      $result['deleted'] = $this->SetSamplingScheme(static::NEW_DELETTION_SCHEME)->GetAll();
      $this->search = new Search(
         static::TABLE,
         new Clause(
            CCond(
               CF(static::TABLE, $this->GetFieldByName(static::UPDATED_DATE_FLD)),
               CVP($lastUpdatedDate),
               null,
               opGE
            )
         )
      );
      $result['created'] = $this->SetSamplingScheme(static::INIT_SCHEME)->GetAll();
   }
}

$_event = new Event();
