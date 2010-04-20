<?php

class Mock_Logger extends Log_null
{
    public function __construct()
    {
        parent::__construct('null');
    }
}

