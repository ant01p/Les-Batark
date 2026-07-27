<?php

namespace App\Service;

use App\Entity\AdminActivityLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class AdminActivityLogger
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function log(
        ?User $actor,
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $subjectLabel = null,
        ?string $detail = null,
    ): void {
        $entry = new AdminActivityLog();
        $entry->setActor($actor);
        $entry->setActorLabel($actor?->getPseudo() ?? 'Système');
        $entry->setAction($action);
        $entry->setSubjectType($subjectType);
        $entry->setSubjectId($subjectId);
        $entry->setSubjectLabel($subjectLabel);
        $entry->setDetail($detail);

        $this->em->persist($entry);
        $this->em->flush();
    }
}
