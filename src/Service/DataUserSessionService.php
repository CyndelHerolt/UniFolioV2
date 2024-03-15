<?php
namespace App\Service;

use App\Classes\DataUserSession;

class DataUserSessionService
{
    private DataUserSession $dataUserSession;

    public function __construct(DataUserSession $dataUserSession)
    {
        $this->dataUserSession = $dataUserSession;
    }

    public function getDataUserSession()
    {
        return $this->dataUserSession;
    }
}