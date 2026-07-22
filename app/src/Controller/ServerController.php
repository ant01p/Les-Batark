<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ServerController extends AbstractController
{
    #[Route('/serveurs', name: 'app_servers')]
    public function index(): Response
    {
        return $this->render('server/index.html.twig');
    }
}
