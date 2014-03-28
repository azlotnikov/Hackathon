<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/scripts/classes/class.Entity.php';

class Floor extends Entity
{
    const TABLE = 'floors';

    public function __construct()
    {
        parent::__construct();
        $this->fields = Array(

        );
    }
}