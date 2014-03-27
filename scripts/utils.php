<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.DataHandling.php';

function SetActiveItem($item = 'main')
{
   global $smarty;
   $smarty->assign('active_item', $item);
}

function MakeIndexMenu()
{
   global $psCats;
   $result = '';
   foreach ($psCats as $id => $name) {
      $result .= "<li><a id='$id' data='cat_$id' href='/top/$id#$id'>$name</a></li>";
   }
   return $result;
}

function MakeCatalogMenu($isPhotosess, $page)
{
   global $psCats, $vsCats;
   $assoc = $isPhotosess ? $psCats : $vsCats;
   $result = '';
   foreach ($assoc as $id => $name) {
      $result .= "<li><a id='$id' data='cat_$id' href='/$page/$id#$id'>$name</a></li>";
   }
   return $result;
}

function MakeCatMenu($isPhotosess, $sessAmount, $userId = null)
{
   global $psCats, $vsCats;
   $assoc = $isPhotosess ? $psCats : $vsCats;
   $href_part = $isPhotosess ? 'photosessions' : 'videosessions';
   $result = '';
   if ($sessAmount != 0) {
      foreach ($assoc as $id => $name) {
         $href = "/profile/$href_part/?" . (!empty($userId) ? "user=$userId&" : '') . "category=$id";
         $result .= "<li><a id='$id' data='cat_$id' href='$href#$id'>$name</a></li>";
      }
   }
   return $result;
}

function GetPage()
{
   $page = 0;
   $data_h = new DataHandling();
   if (isset($_GET['page'])) {
      try {
         $data_h->ValidatePositiveNum($_GET['page']);
         $page = $_GET['page'] - 1; //в гет запрос передаем страницы начиная с единицы а не с нуля
      } catch (Exception $e) {}
   }
   return $page;
}

function CheckDateDiff($date, $interval = CONFIRMATION_TERM)
{
   $time = new DateTime($date);
   $time->add(new DateInterval(CONFIRMATION_TERM));
   $curDate = new DateTime();
   return $curDate < $time;
}

function Redirect($url = '/')
{
   header("Location: $url");
   exit;
}

function DisplaySuccess($succesName = 'var', $isGoAcc = false)
{
   global $smarty;
   $smarty->assign('isGoAcc', $isGoAcc)
          ->assign($succesName, true)
          ->display('message.tpl');
   exit;
}

function SetLastViewedID($name)
{
   global $smarty;
   if (isset($_SESSION[$name])) {
      $smarty->assign('last_viewed_id', $_SESSION[$name]);
      unset($_SESSION[$name]);
   }
}

function _GeneratePages($amount, $amount_on_page)
{
   $current_page = GetPage() + 1;
   $newsOnPage = $amount_on_page;
   $pagesAmount = $newsOnPage != 0 ? ceil($amount / $newsOnPage) : 0;
   if ($pagesAmount > 7) {
      if ($current_page <= 4) {
         $result = array_merge(range(1, $current_page + 2), array('...', $pagesAmount));
      } elseif ($current_page > 4 and $pagesAmount - $current_page > 4) {
         $result = array_merge(array(1, '...'), range($current_page - 2, $current_page + 2), array('...', $pagesAmount));
      } elseif ($pagesAmount - $current_page <= 4) {
         $result = array_merge(array(1, '...'), range($current_page - 2, $pagesAmount));
      }
   } elseif ($pagesAmount == 0) {
      $result = [1];
   } else {
      $result = range(1, $pagesAmount);
   }
   return [$current_page - 1, ['amount' => $pagesAmount, 'num' => $result]];
}

function GeneratePages($obj)
{
   return _GeneratePages($obj->GetAllAmount(), $obj::AMOUNT_PAGE);
}

function GetPOST($deleteTags = true)
{
   foreach ($_POST as &$value) {
      if (!is_array($value)) {
         $value = trim($value);
         if ($deleteTags) {
            $value = strip_tags($value);
            $value = htmlspecialchars($value);
         }
      }
   }
   return $_POST;
}

function CutString($str, $amount)
{
   $new_str = mb_substr($str, 0, $amount);
   if ($str != $new_str) {
      $new_str .= '...';
   }
   return $new_str;
}

function GetMonthByNumber($m)
{
   $months = Array(
      1  =>  'Январь',
      2  =>  'Февраль',
      3  =>  'Март',
      4  =>  'Апрель',
      5  =>  'Май',
      6  =>  'Июнь',
      7  =>  'Июль',
      8  =>  'Август',
      9  =>  'Сентябрь',
      10 => 'Октябрь',
      11 => 'Ноябрь',
      12 => 'Декабрь'
   );
   return $months[$m];
}

function GetBentMonthByNumber($m)
{
   $months = Array(
      1 =>  'Января',
      2 =>  'Февраля',
      3 =>  'Марта',
      4 =>  'Апреля',
      5 =>  'Мая',
      6 =>  'Июня',
      7 =>  'Июля',
      8 =>  'Августа',
      9 =>  'Сентября',
      10 => 'Октября',
      11 => 'Ноября',
      12 => 'Декабря'
   );
   return $months[$m];
}