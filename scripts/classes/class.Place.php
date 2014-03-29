<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';
// require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.PlaceType.php';
// require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Hostel.php';
// require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Floor.php';

class Place extends Entity
{
   const NUMBER_FLD      = 'number';
   const FLOOR_FLD       = 'floor';
   const LAST_UPDATE_FLD = 'last_update';
   const POLYGON_FLD     = 'polygon';
   const TYPE_FLD        = 'place_type';
   const HOSTEL_FLD      = 'hostel';

   const INIT_SCHEME      = 9;
//   const NAME_INFO_SCHEME  = 3;
//   const EXTRA_DATA_SCHEME = 4;

   const TABLE = 'places';

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         new Field(
            static::NUMBER_FLD,
            StrType(100),
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
         ),
         new Field(
            static::HOSTEL_FLD,
            IntType(),
            true,
            'Общага'
         )
      );
   }

   public function ModifySample(&$sample)
   {
      if (empty($sample)) return;
      switch ($this->samplingScheme) {
         case static::INIT_SCHEME:
            $result = [];
            $idKey = $this->ToPrfxNm(static::ID_FLD);
            foreach ($sample as &$event) {
               $id = $event[$idKey];
//               unset($event[$idKey]);
               $result[$id] = $event;
            }
            $sample = $result;
            break;
      }
   }

   public function SetSelectValues()
   {
      $fields = Array();
      $this->CheckSearch();
      switch($this->samplingScheme) {
         case static::INIT_SCHEME:
            $fields =
               SQL::PrepareFieldsForSelect(
                  static::TABLE,
                  [
                     $this->idField,
                     $this->GetFieldByName(static::NUMBER_FLD),
                     $this->GetFieldByName(static::POLYGON_FLD),
                     $this->GetFieldByName(static::TYPE_FLD)
                  ]
               );
            $this->search->AddClause(
               CCond(
                  CF(static::TABLE, $this->GetFieldByName(static::FLOOR_FLD)),
                  CVP($this->GetFieldByName(static::FLOOR_FLD)->GetValue()),
                  'cAND'
               ),
               CCond(
                  CF(static::TABLE, $this->GetFieldByName(static::HOSTEL_FLD)),
                  CVP($this->GetFieldByName(static::HOSTEL_FLD)->GetValue()),
                  'cAND'
               )
            );
            break;
      }
      $this->selectFields = SQL::GetListFieldsForSelect($fields);
//
//      $this->selectFields = SQL::GetListFieldsForSelect(
//         array_merge(
//            SQL::PrepareFieldsForSelect(static::TABLE, $this->fields),
//            SQL::PrepareFieldsForSelect(PlaceType::TABLE, [$_placeType->GetFieldByName(PlaceType::TYPENAME_FLD)]),
//            SQL::PrepareFieldsForSelect(Floor::TABLE, [$_floor->GetFieldByName(Floor::NUMBER_FLD)]),
//            SQL::PrepareFieldsForSelect(Hostel::TABLE, [$_hostel->GetFieldByName(Floor::NUMBER_FLD)])
//         )
//      );
//      $this->search->SetJoins([
//                                 PlaceType::TABLE => [null, [static::TYPE_FLD, PlaceType::ID_FLD]],
//                                 Floor::TABLE => [null, [static::FLOOR_FLD, Floor::ID_FLD]],
//                                 Hostel::TABLE => [null, [static::HOSTEL_FLD, Hostel::ID_FLD]]
//                              ]);
   }

//   public function ModifySample(&$sample)
//   {
//      if (empty($sample)) return;
//      foreach ($sample as &$set) {
//         $polygonKey = $this->ToPrfxNm(static::POLYGON_FLD);
//         $set[$polygonKey] = json_decode($set[$polygonKey]);
//      }
//      undef($set);
//   }
}

$_place = new Place();