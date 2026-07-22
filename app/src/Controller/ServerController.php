<?php

namespace App\Controller;

use App\Repository\ServerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ServerController extends AbstractController
{
    #[Route('/serveurs', name: 'app_servers')]
    public function index(ServerRepository $serverRepository): Response
    {
        $servers = $serverRepository->findAllOrdered();

        return $this->render('server/index.html.twig', [
            'servers' => $servers,
            'total'   => count($servers),
        ]);
    }
}
