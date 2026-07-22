<?php

namespace App\Controller\Admin;

use App\Entity\Server;
use App\Entity\User;
use App\Form\ServerType;
use App\Repository\ServerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/servers', name: 'admin_server_')]
#[IsGranted('ROLE_ADMIN')]
final class AdminServerController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(ServerRepository $serverRepository): Response
    {
        return $this->render('admin/server/servers_index.html.twig', [
            'servers' => $serverRepository->findAllOrderedWithCreator(),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $server = new Server();

        /** @var User $user */
        $user = $this->getUser();
        $server->setCreatedBy($user);

        $form = $this->createForm(ServerType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($server);
            $em->flush();

            $this->addFlash('success', 'Serveur créé avec succès.');
            return $this->redirectToRoute('admin_server_index');
        }

        return $this->render('admin/server/server_form.html.twig', [
            'form'   => $form,
            'server' => $server,
            'mode'   => 'create',
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Server $server, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ServerType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Serveur modifié avec succès.');
            return $this->redirectToRoute('admin_server_index');
        }

        return $this->render('admin/server/server_form.html.twig', [
            'form'   => $form,
            'server' => $server,
            'mode'   => 'edit',
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Server $server, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_server_' . $server->getId(), $request->request->get('_token'))) {
            $em->remove($server);
            $em->flush();
            $this->addFlash('success', 'Serveur supprimé.');
        }

        return $this->redirectToRoute('admin_server_index');
    }
}
