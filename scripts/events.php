<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Event.php';

$smarty->assign('events_list', $_event->GetList())
       ->display('events.tpl');