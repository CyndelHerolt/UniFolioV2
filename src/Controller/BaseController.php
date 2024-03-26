<?php
namespace App\Controller;

use App\Service\DataUserSessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    private DataUserSessionService $dataUserSessionService;

    public function __construct(DataUserSessionService $dataUserSessionService)
    {
        $this->dataUserSessionService = $dataUserSessionService;
    }

    public function getDataUserSession()
    {
        return $this->dataUserSessionService->getDataUserSession();
    }
}