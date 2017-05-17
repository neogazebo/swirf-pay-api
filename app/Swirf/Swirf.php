<?php

namespace App\Swirf;

class Swirf
{
    private $app_id = null;

    public function getAppId()
    {
        return $this->app_id;
    }

    public function setAppId($app_id)
    {
        $this->app_id = $app_id;
    }
}