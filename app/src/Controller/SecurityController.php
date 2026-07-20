<?php

namespace App\Controller;

use App\Form\LoginFormType;
use App\Security\Exception\UnverifiedEmailException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(private FormFactoryInterface $formFactory)
    {
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Nom de formulaire vide : les champs doivent s'appeler _username/_password/_csrf_token
        // (sans préfixe) pour correspondre à ce que l'authenticator form_login attend.
        $loginForm = $this->formFactory->createNamed('', LoginFormType::class, [
            '_username' => $lastUsername,
        ]);

        return $this->render('security/login.html.twig', [
            'loginForm' => $loginForm,
            'error' => $error,
            'unverifiedEmail' => $error instanceof UnverifiedEmailException,
            'lastUsername' => $lastUsername,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
