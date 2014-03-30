<?php
//$_POST['__file']
try {
  require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/utils.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.TableImages.php';
  $ajaxOtherResult = Array('result' => true, 'message' => 'Загрузка прошла успешно!');
  $post = GetPOST();
  $item_id = $post['item_id'];
  switch ($post['uploadType']) {
    case 'ps':
      require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.MediaSessions.php';
      if (empty($post['isAvatar']) || !$post['isAvatar']) {
         $_POST['__file'] = $_psImages->SetFieldByName(PSImages::SESSION_FLD, $item_id)->Insert(true);
      }
      break;

   case 'vs_av':
   case 'user_av':
      require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.MediaSessions.php';
      require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.User.php';
      $obj = $post['uploadType'] == 'vs_av' ? $_vs : $_user;
      if (!empty($post['image_id'])) {
         $_image->Delete($post['image_id']);
      }
      try {
         $db->link->beginTransaction();
         $_POST['__file'] = $_image->Insert(true);
         $obj->SetFieldByName($obj::PHOTO_FLD, $_POST['__file'])->SetFieldByName($obj::ID_FLD, $item_id)->Update();
         $db->link->commit();
      } catch (DBException $e) {
         $db->link->rollback();
         throw new Exception($e->getMessage());
      }
      break;

    default:
      $ajaxOtherResult['result'] = false;
      $ajaxOtherResult['message'] = 'Неопознаный тип загрузки!';
      break;
  }
} catch (DBException $e) {
  $ajaxOtherResult['result'] = false;
  $ajaxOtherResult['message'] = 'Ошибка, связанная с базой данных!';
}