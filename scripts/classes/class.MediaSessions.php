<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.TableImages.php';

class MediaSession extends Entity
{
   const NAME_FLD        = 'name';
   const SITE_FLD        = 'site';
   const USER_FLD        = 'user_id';
   const DATE_FLD        = 'create_date';
   const PHOTO_FLD       = 'photo_id';
   const PHOTOS_FLD      = 'photos';
   const CATEGORY_FLD    = 'category_id';
   const DESCRIPTION_FLD = 'description';

   const ALL_SCHEME                   = 2;
   const CURRENT_SCHEME               = 3;
   const CATALOG_PHOTOGRAPHS_SCHEME   = 4;
   const CATALOG_VIDEOGRAPHS_SCHEME   = 5;
   const CATALOG_PHOTOSESSIONS_SCHEME = 6;
   const CATALOG_VIDEOSESSIONS_SCHEME = 7;

   const MOST_POPULAR_PHOTOGRAPHS_AMOUNT   = 50;
   const MOST_POPULAR_PHOTOSESSIONS_AMOUNT = 10;

   const CATALOG_AMOUNT = 20;

   public function __construct()
   {
      parent::__construct();
      $this->fields = Array(
         $this->idField,
         new Field(
            static::NAME_FLD,
            StrType(100),
            true,
            'Название фотосессии',
            [Validate::IS_NOT_EMPTY]
         ),
         new Field(
            static::SITE_FLD,
            StrType(60),
            true
         ),
         new Field(
            static::DESCRIPTION_FLD,
            TextType()
         ),
         new Field(
            static::DATE_FLD,
            TimestampType()
         ),
         new Field(
            static::CATEGORY_FLD,
            IntType(),
            true,
            'Категория фотосессии',
            [Validate::IS_NOT_EMPTY]
         ),
         new Field(
            static::PHOTO_FLD,
            IntType()
         ),
         new Field(
            static::USER_FLD,
            IntType()
         )
      );
      $this->orderFields = [static::DATE_FLD => new OrderField(static::TABLE, $this->GetFieldByName(static::DATE_FLD))];
   }

   protected function ConvertDate(&$set)
   {
      $dateKey = $this->ToPrfxNm(static::DATE_FLD);
      $set[$dateKey] = date_format(date_create($set[$dateKey]), 'd.m.Y');
   }

   public function ModifySample(&$sample)
   {
      if (empty($sample)) return;
      foreach ($sample as &$set) {
         $this->ConvertDate($set);
      }
   }

   public function GetUserSessionsAmount($user_id, $email)
   {
      $this->ResetSearch();
      global $_user;
      $this->search = new Search(
         static::TABLE,
         new Clause(
            CCond(
               CF(
                  User::TABLE,
                  $_user->GetFieldByName(
                     !empty($user_id) ? User::ID_FLD : User::EMAIL_FLD
                  )
               ),
               CVP(!empty($user_id) ? $user_id : $email),
               'AND'
            )
         ),
         [User::TABLE => [null, [static::USER_FLD, User::ID_FLD]]]
      );
      return $this->GetAllAmount();
   }

   public function CreateCatalogSearch($category, $user_id, $email)
   {
      $this->ResetSearch();
      $this->selectFields =
         SQL::GetListFieldsForSelect(
            array_merge(
               SQL::PrepareFieldsForSelect(
                  static::TABLE,
                  Array(
                     $this->idField,
                     $this->GetFieldByName(static::DATE_FLD),
                     $this->GetFieldByName(static::NAME_FLD),
                     $this->GetFieldByName(static::PHOTO_FLD)
                  )
               ),
               [ImageWithFlagSelectSQL(static::TABLE, $this->GetFieldByName(static::PHOTO_FLD))]
            )
         );
      global $_user;
      $this->search = new Search(
         static::TABLE,
         new Clause(
            CCond(
               CF(static::TABLE, $this->GetFieldByName(static::CATEGORY_FLD)),
               CVP($category)
            ),
            CCond(
               CF(
                  User::TABLE,
                  $_user->GetFieldByName(
                     !empty($user_id) ? User::ID_FLD : User::EMAIL_FLD
                  )
               ),
               CVP(!empty($user_id) ? $user_id : $email),
               'AND'
            )
         ),
         [User::TABLE => [null, [static::USER_FLD, User::ID_FLD]]]
      );
      $this->samplingScheme = static::ALL_SCHEME;
      $this->AddOrder(static::DATE_FLD, OT_DESC);
   }

   public function CreateUserSearch($user_id, $email)
   {
      global $_psImages;
      $this->selectFields =
         SQL::GetListFieldsForSelect(
            array_merge(
               SQL::PrepareFieldsForSelect(
                  static::TABLE,
                  Array(
                     $this->idField,
                     $this->GetFieldByName(static::DATE_FLD),
                     $this->GetFieldByName(static::USER_FLD),
                     $this->GetFieldByName(static::NAME_FLD),
                     $this->GetFieldByName(static::SITE_FLD),
                     $this->GetFieldByName(static::DESCRIPTION_FLD),
                     $this->GetFieldByName(static::CATEGORY_FLD),
                     $this->GetFieldByName(static::PHOTO_FLD)
                  )
               ),
               [
                  ImageSelectSQL($this, $_psImages, $_psImages::SESSION_FLD),
                  ImageWithFlagSelectSQL(static::TABLE, $this->GetFieldByName(static::PHOTO_FLD))
               ]
            )
         );
      global $_user;
      $this->search = new Search(
         static::TABLE,
         new Clause(
            CCond(
               CF(
                  User::TABLE,
                  $_user->GetFieldByName(
                     !empty($user_id) ? User::ID_FLD : User::EMAIL_FLD
                  )
               ),
               CVP(!empty($user_id) ? $user_id : $email),
               'AND'
            )
         ),
         [User::TABLE => [null, [static::USER_FLD, User::ID_FLD]]]
      );
      $this->samplingScheme = static::CURRENT_SCHEME;
   }

   public function GetMainView($category)
   {
      global $_user;
      $fields = $this->PackFields(static::TABLE, User::TABLE);
      $spec = new UserPSSpec;
      $search = $this->GetCatalogSearch($category);
      $specField = $_user->ToPrfxNm('spec');
      $sessionName = $this->GetSessionName();
      $fields[] = ImageWithFlagSelectSQL(static::TABLE, $this->GetFieldByName(static::PHOTO_FLD));
      $fields[] =
           'IFNULL(('
         . SQL::SimpleQuerySelect(
               'GROUP_CONCAT(' . $spec->ToTblNm($spec::CATEGORY_FLD) . ')',
               $spec::TABLE,
               new Clause(CCond(
                  CF($spec::TABLE, $spec->GetFieldByName($spec::USER_FLD)),
                  CF(static::TABLE, $this->GetFieldByName(static::USER_FLD))
               ))
           )
             . ' GROUP BY '
             . $this->ToTblNm(static::ID_FLD)
         . '), \'\') as '
         . $specField;
      $from = sprintf(
         '(SELECT %s FROM %s %s WHERE %s GROUP BY %s ORDER BY %s DESC LIMIT ?, ?) as %s',
         SQL::GetListFieldsForSelect($fields),
         static::TABLE,
         $search->GetJoins(),
         $search->GetClause(),
         $_user->ToTblNm(User::ID_FLD),
         $_user->ToTblNm(User::PROFILE_VIEWS_FLD),
         $sessionName
      );
      $fields = $this->PackFields($sessionName, $sessionName, true);
      $fields[] = SQL::ToTblNm($sessionName, $specField);
      try {
         global $db, $psCats;
         $result = $db->Query(
               sprintf('SELECT %s FROM %s ORDER BY RAND() LIMIT ?, ?', SQL::GetListFieldsForSelect($fields), $from),
               [$category, 0, static::MOST_POPULAR_PHOTOGRAPHS_AMOUNT, 0, static::MOST_POPULAR_PHOTOSESSIONS_AMOUNT]
         );
         $this->ModifyMainCatalogInfo(
            $result,
            $psCats,
            $specField
         );
      } catch (Exception $e) {
         echo "EXCEPTION";
         $result = Array();
      }
      return $result;
   }

   private function ModifyMainCatalogInfo(&$sample, $cats, $specField)
   {
      foreach ($sample as &$set) {
         $res = explode(',', $set[$specField]);
         $set[$specField] = [];
         $idx = 0;
         $maxAmount = 3;
         foreach ($res as $catID) {
            if ($idx++ == $maxAmount) {
               $rest = count($res) - $idx + 1;
               if ($rest > 0 ) {
                  $set['spec_other'] = $rest == 1 ? '1 жанр' : "$rest жанра";
               }
               break;
            }
            $set[$specField][] = $cats[$catID];
         }
      }
   }

   private function PreparedFieldsFunc($table, $fields, $preffix = null, $isAlias)
   {
      $result = Array();
      foreach ($fields as $f) {
         $field = SQL::ToTblNm($table, (!empty($preffix) ? $preffix . '_' : '') . $f->GetName());
         $result[] =
            $field . ($isAlias ? ' as ' . SQL::ToPrfxNm($table, $f->GetName()) : '');
      }
      return $result;
   }


   private function PackFields($table1, $table2, $isPreffix = false)
   {
      global $_user;
      $isAlias = $table1 != $table2;
      return
         array_merge(
            $this->PreparedFieldsFunc(
               $table1,
               (!$isAlias ? [$this->idField, $this->GetFieldByName(static::PHOTO_FLD)] : [$this->idField]),
               $isPreffix ? $this::TABLE : null,
               $isAlias
            ),
            $this->PreparedFieldsFunc(
               $table2,
               Array(
                  $_user->idField,
                  $_user->GetFieldByName(User::NAME_FLD),
                  $_user->GetFieldByName(User::SURNAME_FLD),
                  $_user->GetFieldByName(User::PROFILE_VIEWS_FLD)
               ),
               $isPreffix ? $_user::TABLE : null,
               $isAlias
            )
         );
   }

   private function GetCatalogSearch($category)
   {
      return new Search(
         static::TABLE,
         new Clause(
            CCond(
               CF(static::TABLE, $this->GetFieldByName(static::CATEGORY_FLD)),
               CVP($category)
            )
         ),
         [User::TABLE => [null, [static::USER_FLD, User::ID_FLD]]]
      );
   }

   private function GetSessionName()
   {
      return 'sess';
   }

   public function GetCatalog($page, $category)
   {
      global $_user;
      $pageNum = 0;
      $pagesInfo = Array();
      $profViews = null;
      $sessionName = $this->GetSessionName();
      $query = $fromAmount = $from = '';
      $search = $this->GetCatalogSearch($category);
      if ($page == PHOTOGRAPHS || $page == VIDEOGRAPHS) {
         $fields = $this->PackFields(static::TABLE, User::TABLE);
         $spec = $page == PHOTOGRAPHS ? new UserPSSpec : new UserVSSpec;
         $specField = $_user->ToPrfxNm('spec');
         $fields[] = ImageWithFlagSelectSQL(static::TABLE, $this->GetFieldByName(static::PHOTO_FLD));
         $fields[] =
              'IFNULL(('
            . SQL::SimpleQuerySelect(
                  'GROUP_CONCAT(' . $spec->ToTblNm($spec::CATEGORY_FLD) . ')',
                  $spec::TABLE,
                  new Clause(CCond(
                     CF($spec::TABLE, $spec->GetFieldByName($spec::USER_FLD)),
                     CF(static::TABLE, $this->GetFieldByName(static::USER_FLD))
                  ))
              )
                . ' GROUP BY '
                . $this->ToTblNm(static::ID_FLD)
            . '), \'\') as '
            . $specField;
         $fromPart = sprintf(
            'FROM %s %s WHERE %s ORDER BY %s DESC) as %s',
            static::TABLE,
            $search->GetJoins(),
            $search->GetClause(),
            // $_user->ToTblNm(User::PROFILE_VIEWS_FLD),
            $this->ToTblNm(static::DATE_FLD),
            $sessionName
         );
         $from = sprintf(
            '(SELECT %s %s',
            SQL::GetListFieldsForSelect($fields),
            $fromPart . ' GROUP BY ' . SQL::ToTblNm($sessionName, SQL::ToPrfxNm(User::TABLE, static::ID_FLD))
         );
         $fromAmount = sprintf(
            '(SELECT DISTINCT(%s) %s',
            $this->ToTblNm(static::USER_FLD),
            $fromPart
         );
         $fields = $this->PackFields($sessionName, $sessionName, true);
         $fields[] = SQL::ToTblNm($sessionName, $specField);
         $profViews = 'ORDER BY ' . SQL::ToTblNm($sessionName, SQL::ToPrfxNm(User::TABLE, User::PROFILE_VIEWS_FLD)) . ' DESC ';
      } else {
         $sessionName = static::TABLE;
         $sessFields = array_merge(
            SQL::PrepareFieldsForSelect(
               static::TABLE,
               Array(
                  $this->idField,
                  $this->GetFieldByName(static::DATE_FLD),
                  $this->GetFieldByName(static::NAME_FLD)
               )
            ),
            [ImageWithFlagSelectSQL(static::TABLE, $this->GetFieldByName(static::PHOTO_FLD))]
         );
         $fields = array_merge(
            $sessFields,
            SQL::PrepareFieldsForSelect(
               User::TABLE,
               Array(
                  $_user->idField,
                  $_user->GetFieldByName(User::NAME_FLD),
                  $_user->GetFieldByName(User::SURNAME_FLD),
                  $_user->GetFieldByName(User::PROFILE_VIEWS_FLD)
               )
            )
         );
         $from = $fromAmount = sprintf(
            '%s %s WHERE %s ORDER BY %s DESC, %s DESC',
            static::TABLE,
            $search->GetJoins(),
            $search->GetClause(),
            $this->ToTblNm(static::DATE_FLD),
            $_user->ToTblNm(User::PROFILE_VIEWS_FLD)
         );
      }
      try {
         global $db;
         // echo 'SELECT COUNT(' . SQL::ToTblNm($sessionName, static::USER_FLD) . ') as amount FROM ' . $fromAmount;
         // echo "<br>";
         $result = $db->Query(
            'SELECT COUNT(' . SQL::ToTblNm($sessionName, static::USER_FLD) . ') as amount FROM ' . $fromAmount,
            [$category]
         );
         $amount = !empty($result) ? $result[0]['amount'] : 0;
         list($pageNum, $pagesInfo) = _GeneratePages($amount, static::CATALOG_AMOUNT);
         $result = $db->Query(
            sprintf('SELECT %s FROM %s %s LIMIT ?, ?', SQL::GetListFieldsForSelect($fields), $from, $profViews),
            [$category, $pageNum * static::CATALOG_AMOUNT, static::CATALOG_AMOUNT]
         );
         // echo sprintf('SELECT %s FROM %s %s LIMIT ?, ?', SQL::GetListFieldsForSelect($fields), $from, $profViews);
         if ($page == PHOTOSESSIONS || $page == VIDEOSESSIONS) {
            foreach ($result as &$set) {
               $this->ConvertDate($set);
            }
         } else {
            global $psCats, $vsCats;
            $this->ModifyMainCatalogInfo($result, $page == PHOTOGRAPHS ? $psCats : $vsCats, $specField);
         }
      } catch (Exception $e) {
         $result = Array();
      }
      return [$pageNum, $pagesInfo, $result];
   }
}

class PS extends MediaSession
{
   const AMOUNT_PAGE = 1;

   const TABLE = 'photosessions';

   public function ModifySample(&$sample)
   {
      if (empty($sample)) return;
      parent::ModifySample($sample);
      switch ($this->samplingScheme) {
         case static::ALL_SCHEME:
         case static::CURRENT_SCHEME:
            $key = $this->ToPrfxNm(static::PHOTOS_FLD);
            $sample[0][$key] = !empty($sample[0][$key]) ? explode(',', $sample[0][$key]) : Array();
            break;
      }
   }

   public function Delete($id)
   {
      global $_image, $db, $_psImages;
      try {
         $db->link->beginTransaction();
         $images = $_psImages->GetBySessionId($id);
         parent::Delete($id);
         $key = PSImages::PHOTO_FLD;
         foreach ($images as $img) {
            $_image->DeleteImg($img[$key]);
         }
         $db->link->commit();
      } catch (DBException $e) {
         $db->link->rollback();
         throw new Exception($e->getMessage());
      }
   }

}

class VS extends MediaSession
{
   const AMOUNT_PAGE = 2;

   const TABLE = 'videosessions';

   public function __construct()
   {
      parent::__construct();
      $this->samplingScheme = static::ALL_SCHEME;
   }

   public function Delete($id)
   {
      global $_image;
      try {
         parent::Delete($id);
         $_image->DeleteImg($this->GetFieldByName(static::PHOTO_FLD)->GetValue());
      } catch (DBException $e) {
         throw new Exception($e->getMessage());
      }
   }
}

$_ps = new PS();
$_vs = new VS();