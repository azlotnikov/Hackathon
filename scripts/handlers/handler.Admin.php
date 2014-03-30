<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/handlers/handler.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Place.php';
$post = GetPOST();

$_place->SetFieldByName(Place::NUMBER_FLD, $post['number'])
      ->SetFieldByName(Place::POLYGON_FLD, $post['polygon'])
      ->SetFieldByName(Place::TYPE_FLD, $post['place_type'])
      ->SetFieldByName(Place::FLOOR_FLD, $post['floor'])
      ->SetFieldByName(Place::HOSTEL_FLD, $post['hostel'])
      ->Insert();