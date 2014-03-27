<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Image.php';

class TableImages extends Entity
{
   const PHOTO_FLD  = 'photo_id';

   protected
      $photoField;

   public function __construct()
   {
      parent::__construct();
      $this->photoField = new Field(static::PHOTO_FLD, IntType(), true);
   }

   public function Insert($getLastInsertId = false)
   {
      global $db, $_image;
      $resId = -1;
      try {
         $db->link->beginTransaction();
         $resId = $_image->Insert($getLastInsertId);
         $this->SetFieldByName(static::PHOTO_FLD, $resId);
         Entity::Insert();
         $db->link->commit();
      } catch (DBException $e) {
         $db->link->rollback();
         throw new Exception($e->getMessage());
      }
      return $resId;
   }
}

class PSImages extends TableImages
{
   const SESSION_FLD = 'sess_id';

   const TABLE = 'ps_imgs';

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         $this->photoField,
         new Field(
            static::SESSION_FLD,
            IntType(),
            true
         )
      );
   }

   public static function SimpleQuerySelect($fields, $table, $where = null)
   {
      $result = 'SELECT ' . $fields . ' FROM ' . $table;
      if (!empty($where)) {
         $result .= ' WHERE ' . $where->GetClause();
      }
      return $result;
   }

   public function GetBySessionId($id)
   {
      global $db;
      $query = SQL::SimpleQuerySelect(
         $this->ToTblNm(static::PHOTO_FLD),
         static::TABLE,
         new Clause(
            CCond(
               CF(static::TABLE, $this->GetFieldByName(static::SESSION_FLD)),
               CVS('?')
            )
         )
      );
      return $db->Query($query, [$id]);
   }

}

$_psImages = new PSImages();