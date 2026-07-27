<?php

namespace App\Entity;

use App\Repository\AdminActivityLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminActivityLogRepository::class)]
#[ORM\Index(columns: ['created_at'])]
#[ORM\Index(columns: ['action'])]
#[ORM\Index(columns: ['subject_type'])]
class AdminActivityLog
{
    // ── Actions actuellement émises ──
    public const ACTION_PRODUCT_CREATED = 'product_created';
    public const ACTION_PRODUCT_UPDATED = 'product_updated';
    public const ACTION_PRODUCT_DELETED = 'product_deleted';
    public const ACTION_SERVER_CREATED = 'server_created';
    public const ACTION_SERVER_UPDATED = 'server_updated';
    public const ACTION_SERVER_DELETED = 'server_deleted';
    public const ACTION_EVENT_CREATED = 'event_created';
    public const ACTION_EVENT_UPDATED = 'event_updated';
    public const ACTION_EVENT_DELETED = 'event_deleted';
    public const ACTION_CATEGORY_CREATED = 'category_created';
    public const ACTION_CATEGORY_UPDATED = 'category_updated';
    public const ACTION_CATEGORY_DELETED = 'category_deleted';
    public const ACTION_ORDER_DELIVERED = 'order_delivered';

    // ── Réservées pour la future gestion des membres (non émises pour l'instant) ──
    public const ACTION_MEMBER_PERMISSION_GRANTED = 'member_permission_granted';
    public const ACTION_MEMBER_PERMISSION_REVOKED = 'member_permission_revoked';
    public const ACTION_MEMBER_PROMOTED_ADMIN = 'member_promoted_admin';
    public const ACTION_MEMBER_PROMOTED_SUPER_ADMIN = 'member_promoted_super_admin';
    public const ACTION_MEMBER_DEMOTED = 'member_demoted';
    public const ACTION_MEMBER_SUSPENDED = 'member_suspended';
    public const ACTION_MEMBER_REACTIVATED = 'member_reactivated';
    public const ACTION_MEMBER_ANONYMIZED = 'member_anonymized';
    public const ACTION_MEMBER_DELETED = 'member_deleted';

    public const SUBJECT_PRODUCT = 'product';
    public const SUBJECT_SERVER = 'server';
    public const SUBJECT_EVENT = 'event';
    public const SUBJECT_CATEGORY = 'category';
    public const SUBJECT_ORDER = 'order';
    public const SUBJECT_MEMBER = 'member';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private ?string $action = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $subjectType = null;

    // Volontairement pas de relation Doctrine : aucune FK à violer/cascader si l'élément
    // concerné est supprimé ensuite, la ligne d'historique doit toujours survivre.
    #[ORM\Column(nullable: true)]
    private ?int $subjectId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subjectLabel = null;

    // Nullable : l'administrateur auteur de l'action peut être supprimé/anonymisé plus
    // tard (cf. Order::$user), l'historique doit survivre à la suppression de son auteur.
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $actor = null;

    #[ORM\Column(length: 255)]
    private ?string $actorLabel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $detail = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getSubjectType(): ?string
    {
        return $this->subjectType;
    }

    public function setSubjectType(?string $subjectType): static
    {
        $this->subjectType = $subjectType;

        return $this;
    }

    public function getSubjectId(): ?int
    {
        return $this->subjectId;
    }

    public function setSubjectId(?int $subjectId): static
    {
        $this->subjectId = $subjectId;

        return $this;
    }

    public function getSubjectLabel(): ?string
    {
        return $this->subjectLabel;
    }

    public function setSubjectLabel(?string $subjectLabel): static
    {
        $this->subjectLabel = $subjectLabel;

        return $this;
    }

    public function getActor(): ?User
    {
        return $this->actor;
    }

    public function setActor(?User $actor): static
    {
        $this->actor = $actor;

        return $this;
    }

    public function getActorLabel(): ?string
    {
        return $this->actorLabel;
    }

    public function setActorLabel(string $actorLabel): static
    {
        $this->actorLabel = $actorLabel;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(?string $detail): static
    {
        $this->detail = $detail;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Phrase française complète décrivant l'action, affichée en colonne "Description".
     * Couvre aussi les actions membres réservées : aucune modification nécessaire ici
     * quand la gestion des membres sera construite, seuls de nouveaux appels au logger
     * seront ajoutés.
     */
    public function getActionLabel(): string
    {
        return match ($this->action) {
            self::ACTION_PRODUCT_CREATED => sprintf('Le produit « %s » a été ajouté à la boutique', $this->subjectLabel),
            self::ACTION_PRODUCT_UPDATED => sprintf('Le produit « %s » a été modifié', $this->subjectLabel),
            self::ACTION_PRODUCT_DELETED => sprintf('Le produit « %s » a été supprimé', $this->subjectLabel),
            self::ACTION_SERVER_CREATED => sprintf('Le serveur « %s » a été ajouté', $this->subjectLabel),
            self::ACTION_SERVER_UPDATED => sprintf('Le serveur « %s » a été modifié', $this->subjectLabel),
            self::ACTION_SERVER_DELETED => sprintf('Le serveur « %s » a été supprimé', $this->subjectLabel),
            self::ACTION_EVENT_CREATED => sprintf('L\'événement « %s » a été créé', $this->subjectLabel),
            self::ACTION_EVENT_UPDATED => sprintf('L\'événement « %s » a été modifié', $this->subjectLabel),
            self::ACTION_EVENT_DELETED => sprintf('L\'événement « %s » a été supprimé', $this->subjectLabel),
            self::ACTION_CATEGORY_CREATED => sprintf('La catégorie « %s » a été créée', $this->subjectLabel),
            self::ACTION_CATEGORY_UPDATED => sprintf('La catégorie « %s » a été modifiée', $this->subjectLabel),
            self::ACTION_CATEGORY_DELETED => sprintf('La catégorie « %s » a été supprimée', $this->subjectLabel),
            self::ACTION_ORDER_DELIVERED => sprintf('La commande %s a été marquée comme livrée', $this->subjectLabel),
            self::ACTION_MEMBER_PERMISSION_GRANTED => sprintf('Une permission a été attribuée à « %s »%s', $this->subjectLabel, $this->detail ? sprintf(' (%s)', $this->detail) : ''),
            self::ACTION_MEMBER_PERMISSION_REVOKED => sprintf('Une permission a été retirée à « %s »%s', $this->subjectLabel, $this->detail ? sprintf(' (%s)', $this->detail) : ''),
            self::ACTION_MEMBER_PROMOTED_ADMIN => sprintf('« %s » a été promu administrateur', $this->subjectLabel),
            self::ACTION_MEMBER_PROMOTED_SUPER_ADMIN => sprintf('« %s » a été promu super-administrateur', $this->subjectLabel),
            self::ACTION_MEMBER_DEMOTED => sprintf('« %s » a été rétrogradé', $this->subjectLabel),
            self::ACTION_MEMBER_SUSPENDED => sprintf('Le compte de « %s » a été suspendu', $this->subjectLabel),
            self::ACTION_MEMBER_REACTIVATED => sprintf('Le compte de « %s » a été réactivé', $this->subjectLabel),
            self::ACTION_MEMBER_ANONYMIZED => sprintf('Le compte de « %s » a été anonymisé', $this->subjectLabel),
            self::ACTION_MEMBER_DELETED => sprintf('Le compte de « %s » a été supprimé', $this->subjectLabel),
            default => $this->subjectLabel !== null ? sprintf('Action « %s » sur « %s »', $this->action, $this->subjectLabel) : (string) $this->action,
        };
    }

    /**
     * Classe de badge Bootstrap dérivée du suffixe/mot-clé de l'action plutôt que d'un
     * match par constante (même idiome que Server::getAccent()/getModeBadgeClass()) :
     * les futures actions membres héritent d'un badge cohérent sans toucher l'entité.
     */
    public function getBadgeClass(): string
    {
        return match (true) {
            str_ends_with($this->action ?? '', '_created'),
            str_ends_with($this->action ?? '', '_delivered') => 'bg-success',
            str_ends_with($this->action ?? '', '_updated') => 'bg-primary',
            str_ends_with($this->action ?? '', '_deleted') => 'bg-danger',
            str_contains($this->action ?? '', 'suspend'),
            str_contains($this->action ?? '', 'revoked'),
            str_contains($this->action ?? '', 'demoted') => 'bg-warning text-dark',
            str_contains($this->action ?? '', 'reactivat'),
            str_contains($this->action ?? '', 'granted'),
            str_contains($this->action ?? '', 'promoted') => 'bg-info',
            str_contains($this->action ?? '', 'anonymiz') => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Actions actuellement émises, pour le badge court dans le tableau et le filtre
     * déroulant. ⚠️ À compléter manuellement le jour où de vrais appels au logger sont
     * ajoutés pour la gestion des membres (contrairement à getActionLabel()/getBadgeClass()
     * qui couvrent déjà ces actions automatiquement).
     *
     * @return array<string, string>
     */
    public static function getActionChoices(): array
    {
        return [
            self::ACTION_PRODUCT_CREATED => 'Produit créé',
            self::ACTION_PRODUCT_UPDATED => 'Produit modifié',
            self::ACTION_PRODUCT_DELETED => 'Produit supprimé',
            self::ACTION_SERVER_CREATED => 'Serveur créé',
            self::ACTION_SERVER_UPDATED => 'Serveur modifié',
            self::ACTION_SERVER_DELETED => 'Serveur supprimé',
            self::ACTION_EVENT_CREATED => 'Événement créé',
            self::ACTION_EVENT_UPDATED => 'Événement modifié',
            self::ACTION_EVENT_DELETED => 'Événement supprimé',
            self::ACTION_CATEGORY_CREATED => 'Catégorie créée',
            self::ACTION_CATEGORY_UPDATED => 'Catégorie modifiée',
            self::ACTION_CATEGORY_DELETED => 'Catégorie supprimée',
            self::ACTION_ORDER_DELIVERED => 'Commande livrée',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getSubjectTypeChoices(): array
    {
        return [
            self::SUBJECT_PRODUCT => 'Boutique',
            self::SUBJECT_SERVER => 'Serveurs',
            self::SUBJECT_EVENT => 'Événements',
            self::SUBJECT_CATEGORY => 'Catégories',
            self::SUBJECT_ORDER => 'Commandes',
        ];
    }
}
