<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RuleController extends AbstractController
{
    #[Route('/reglement', name: 'app_rules')]
    public function index(): Response
    {
        return $this->render('rule/index.html.twig');
    }
}
