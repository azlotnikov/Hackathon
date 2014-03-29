<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/handlers/handler.php';

class PlaceHandler extends Handler
{
   public function __construct()
   {
      $this->entity = new Place();
   }

   public function Handle($in)
   {
      parent::Handle($in);
   }
}