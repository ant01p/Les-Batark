<?php

namespace App\Controller\Admin;

use App\Entity\AdminActivityLog;
use App\Repository\AdminActivityLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/activity', name: 'admin_activity_')]
#[IsGranted('ROLE_SUPER_ADMIN')]
final class AdminActivityController extends AbstractController
{
    private const STEP = 20;
    private const MAX_LIMIT = 200;

    #[Route('', name: 'index')]
    public function index(Request $request, AdminActivityLogRepository $repo): Response
    {
        $limit = $this->resolveLimit($request->query->getInt('limit', self::STEP));

        $author = $request->query->get('actor');
        $author = in_array($author, ['admin', 'member'], true) ? $author : null;

        $action = $request->query->get('action');
        $action = $action !== null && array_key_exists($action, AdminActivityLog::getActionChoices()) ? $action : null;

        $subjectType = $request->query->get('subject');
        $subjectType = $subjectType !== null && array_key_exists($subjectType, AdminActivityLog::getSubjectTypeChoices()) ? $subjectType : null;

        $q = trim((string) $request->query->get('q', '')) ?: null;

        $from = $this->parseDate($request->query->get('from'));
        $to = $this->parseDate($request->query->get('to'))?->setTime(23, 59, 59);

        $logs = $repo->findFiltered($author, $action, $subjectType, $q, $from, $to, $limit);
        $total = $repo->countFiltered($author, $action, $subjectType, $q, $from, $to);

        return $this->render('admin/activity/activity_index.html.twig', [
            'logs' => $logs,
            'hasMore' => $total > $limit,
            'canShowLess' => $limit > self::STEP,
            'limit' => $limit,
            'actionChoices' => AdminActivityLog::getActionChoices(),
            'subjectTypeChoices' => AdminActivityLog::getSubjectTypeChoices(),
            'filters' => [
                'actor' => $author,
                'action' => $action,
                'subject' => $subjectType,
                'q' => $q,
                'from' => $request->query->get('from'),
                'to' => $request->query->get('to'),
            ],
        ]);
    }

    private function resolveLimit(int $raw): int
    {
        $limit = max(self::STEP, min(self::MAX_LIMIT, $raw));

        return $limit - ($limit % self::STEP);
    }

    private function parseDate(?string $raw): ?\DateTimeImmutable
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $raw);

        return ($date && $date->format('Y-m-d') === $raw) ? $date->setTime(0, 0, 0) : null;
    }
}
