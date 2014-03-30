<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Event.php';

$page     = 1;
$userId   = isset($_GET['user'])     ? $_GET['user']    : null;
$sid      = isset($_SESSION['sid'])  ? $_SESSION['sid'] : null;

if (empty($userId)) {
   if (!Authentification::CheckCredentials()) Redirect('/login/?originating_uri=' . $_SERVER['REQUEST_URI']);
   $accSelf = true;
} else {
   $user = $_user->SetSamplingScheme(User::REGISTRATION_CHECK_SCHEME)->GetById($userId);
   $userAuth = $_user->SetSamplingScheme(User::REGISTRATION_CHECK_SCHEME)->GetBySID($sid);
   if (empty($user)) Redirect('/404');
   $accSelf = !empty($userAuth) && $userAuth[$_user->ToPrfxNm(User::ID_FLD)] == $userId;
}

if (!$accSelf) {
   try {
      (new CookieProfileView($userId))->Set();
   } catch (CookieException $e) {}
}

$_user->SetSamplingScheme(User::PROFILE_INFO_SCHEME);
$displayedUser = $accSelf ? $_user->GetBySID($sid) : $_user->GetById($userId);
if (empty($displayedUser)) Redirect('/');

$userId = $accSelf ? $displayedUser[$_user->ToPrfxNm(User::ID_FLD)] : $userId;
$smarty->assign('acc_self', $accSelf)
       ->assign('user_id', $userId)
       ->assign('user_info', $displayedUser)
       ->assign('loaded_amount', Event::LIST_LIMIT)
       ->assign('events_list', $_event->GetList($userId))
       ->display('profile.tpl');