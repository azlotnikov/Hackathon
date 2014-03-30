<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.User.php';

class Place extends Entity
{
   const NUMBER_FLD      = 'number';
   const FLOOR_FLD       = 'floor';
   const LAST_UPDATE_FLD = 'last_update';
   const POLYGON_FLD     = 'polygon';
   const TYPE_FLD        = 'place_type';
   const HOSTEL_FLD      = 'hostel';

   const INIT_SCHEME       = 2;
   const AVAILABLE_SCHEME  = 3;
//   const EXTRA_DATA_SCHEME = 4;

   const TABLE = 'places';

   private
      $initFields = null;

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
         case static::AVAILABLE_SCHEME:
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
               $this->initFields = $fields;
               $this->search->AddClause(
                  CCond(
                     CF(static::TABLE, $this->GetFieldByName(static::FLOOR_FLD)),
                     CVP($this->GetFieldByName(static::FLOOR_FLD)->GetValue()),
                     cAND
                  )
               )->AddClause(
                  CCond(
                     CF(static::TABLE, $this->GetFieldByName(static::HOSTEL_FLD)),
                     CVP($this->GetFieldByName(static::HOSTEL_FLD)->GetValue()),
                     cAND,
                     opEQ
                  )
               );
            break;

         case static::AVAILABLE_SCHEME:
// SELECT
//    places.id  as places_id,
//    places.number  as places_number,
//    places.polygon  as places_polygon,
//    places.place_type  as places_place_type
// FROM places
// WHERE
//    places.floor = 1 AND
//    (
//       (
//          places.place_type = 1 AND
//          places.number = (SELECT users.room FROM users INNER JOIN sessions ON sessions.user_id = users.id WHERE sessions.sid = '153382469192f1')
//       )
//       OR places.place_type <> 1
//    )
            $fields = $this->initFields;
            global $_user, $_session;
            $this->search->AddClause(
               CCond(
                  CF(static::TABLE, $this->GetFieldByName(static::TYPE_FLD)),
                  CVP(1),
                  cAND,
                  opEQ,
                  '(('
               )
            )->AddClause(
               CCond(
                  CF(static::TABLE, $this->GetFieldByName(static::NUMBER_FLD)),
                  CVS(
                     '(' . SQL::SimpleQuerySelect(
                        $_user->ToTblNm(User::ROOM_FLD),
                        sprintf(
                           '%s %s',
                           User::TABLE,
                           SQL::MakeJoin(
                              User::TABLE,
                              [Session::TABLE => [null, [User::ID_FLD, Session::USER_FLD]]]
                           )
                        ),
                        new Clause(
                           CCond(
                              CF(Session::TABLE, $_session->GetFieldByName(Session::SID_FLD)),
                              CVS(sprintf("'%s'", $_SESSION['sid']))
                           )
                        )
                     ) . ')'
                  ),
                  cAND,
                  opEQ,
                  null,
                  ')'
               )
            )->AddClause(
               CCond(
                  CF(static::TABLE, $this->GetFieldByName(static::TYPE_FLD)),
                  CVP(1),
                  cOR,
                  opNE,
                  null,
                  ')'
               )
            );
            break;
      }
      $this->selectFields = SQL::GetListFieldsForSelect($fields);
   }

}

$_place = new Place();