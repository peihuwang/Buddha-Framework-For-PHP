<?php

/**
 * Class Log
 */
class Log extends Buddha_App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = strtolower(__CLASS__);

    }


}