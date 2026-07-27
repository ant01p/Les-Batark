<?php

namespace App\Controller\Admin;

use App\Entity\AdminActivityLog;
use App\Entity\Server;
use App\Entity\ServerImage;
use App\Entity\User;
use App\Form\ServerType;
use App\Repository\ServerRepository;
use App\Service\AdminActivityLogger;
use App\Service\ImageHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/servers', name: 'admin_server_')]
#[IsGranted('ROLE_ADMIN')]
final class AdminServerController extends AbstractController
{
    private const IMAGE_SUBDIR = 'imageServer';

    #[Route('', name: 'index')]
    public function index(ServerRepository $serverRepository): Response
    {
        return $this->render('admin/server/servers_index.html.twig', [
            'servers' => $serverRepository->findAllOrderedWithCreator(),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em, ImageHandler $imageHandler, AdminActivityLogger $activityLogger): Response
    {
        $server = new Server();

        /** @var User $user */
        $user = $this->getUser();
        $server->setCreatedBy($user);

        $form = $this->createForm(ServerType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newImagesByKey = $this->uploadNewImages($form, $server, $imageHandler);
            $this->resolveMainImage($server, $newImagesByKey, $request->request->get('mainImage'));

            $em->persist($server);
            $em->flush();

            $activityLogger->log(actor: $user, action: AdminActivityLog::ACTION_SERVER_CREATED, subjectType: AdminActivityLog::SUBJECT_SERVER, subjectId: $server->getId(), subjectLabel: $server->getTitle());
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
    public function edit(Server $server, Request $request, EntityManagerInterface $em, ImageHandler $imageHandler, AdminActivityLogger $activityLogger): Response
    {
        $form = $this->createForm(ServerType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->deleteMarkedImages($server, $request->request->all('deleteImages'), $imageHandler, $em);
            $newImagesByKey = $this->uploadNewImages($form, $server, $imageHandler);
            $this->resolveMainImage($server, $newImagesByKey, $request->request->get('mainImage'));

            $em->flush();

            $activityLogger->log(actor: $this->getUser(), action: AdminActivityLog::ACTION_SERVER_UPDATED, subjectType: AdminActivityLog::SUBJECT_SERVER, subjectId: $server->getId(), subjectLabel: $server->getTitle());
            $this->addFlash('success', 'Serveur modifié avec succès.');
            return $this->redirectToRoute('admin_server_index');
        }

        return $this->render('admin/server/server_form.html.twig', [
            'form'   => $form,
            'server' => $server,
            'mode'   => 'edit',
        ]);
    }

    /**
     * @return array<string, ServerImage> new images indexed by their "new_{index}" key
     */
    private function uploadNewImages(FormInterface $form, Server $server, ImageHandler $imageHandler): array
    {
        $newImagesByKey = [];

        foreach ($form->get('images')->getData() ?? [] as $index => $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $filename = $imageHandler->upload($file, self::IMAGE_SUBDIR);
            $image = new ServerImage();
            $image->setFilename($filename);
            $server->addImage($image);
            $newImagesByKey['new_' . $index] = $image;
        }

        return $newImagesByKey;
    }

    /**
     * @param array<string, ServerImage> $newImagesByKey
     */
    private function resolveMainImage(Server $server, array $newImagesByKey, ?string $mainImageKey): void
    {
        $imagesByKey = $newImagesByKey;
        foreach ($server->getImages() as $image) {
            if ($image->getId() !== null) {
                $imagesByKey['existing_' . $image->getId()] = $image;
            }
        }

        foreach ($server->getImages() as $image) {
            $image->setIsMain(false);
        }

        if ($mainImageKey !== null && isset($imagesByKey[$mainImageKey])) {
            $imagesByKey[$mainImageKey]->setIsMain(true);
        } elseif (!$server->getImages()->isEmpty()) {
            $server->getImages()->first()->setIsMain(true);
        }
    }

    /**
     * @param string[] $imageIdsToDelete
     */
    private function deleteMarkedImages(Server $server, array $imageIdsToDelete, ImageHandler $imageHandler, EntityManagerInterface $em): void
    {
        foreach ($server->getImages()->toArray() as $image) {
            if (in_array((string) $image->getId(), $imageIdsToDelete, true)) {
                $imageHandler->remove($image->getFilename(), self::IMAGE_SUBDIR);
                $server->removeImage($image);
                $em->remove($image);
            }
        }
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Server $server, Request $request, EntityManagerInterface $em, AdminActivityLogger $activityLogger): Response
    {
        if ($this->isCsrfTokenValid('delete_server_' . $server->getId(), $request->request->get('_token'))) {
            $id = $server->getId();
            $label = $server->getTitle();

            $em->remove($server);
            $em->flush();

            $activityLogger->log(actor: $this->getUser(), action: AdminActivityLog::ACTION_SERVER_DELETED, subjectType: AdminActivityLog::SUBJECT_SERVER, subjectId: $id, subjectLabel: $label);
            $this->addFlash('success', 'Serveur supprimé.');
        }

        return $this->redirectToRoute('admin_server_index');
    }
}
