<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    #[Route(name: 'app_index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_swagger_ui_v1');
    }
}
