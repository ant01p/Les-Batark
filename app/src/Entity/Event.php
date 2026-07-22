<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_ONGOING  = 'ongoing';
    public const STATUS_FINISHED = 'finished';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $duration = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxParticipants = null;

    #[ORM\Column(length: 20)]
    private ?string $status = self::STATUS_UPCOMING;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $rewards = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = self::STATUS_UPCOMING;
        $this->rewards = [
            'type' => 'simple',
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getMaxParticipants(): ?int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(?int $maxParticipants): static
    {
        $this->maxParticipants = $maxParticipants;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getRewards(): ?array
    {
        return $this->rewards;
    }

    public function setRewards(?array $rewards): static
    {
        $this->rewards = $rewards ?? [];

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    // ── Helpers statut ─────────────────────────────────────────────

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_UPCOMING => 'À venir',
            self::STATUS_ONGOING  => 'En cours',
            self::STATUS_FINISHED => 'Terminé',
            default               => $this->status ?? '',
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_UPCOMING => 'bg-warning text-dark',
            self::STATUS_ONGOING  => 'bg-success text-dark',
            self::STATUS_FINISHED => 'bg-secondary text-dark',
            default               => 'bg-secondary text-dark',
        };
    }

    // ── Helpers récompenses ────────────────────────────────────────

    public function isRanking(): bool
    {
        return ($this->rewards['type'] ?? 'simple') === 'ranking';
    }

    public function getIsRankingMode(): bool
    {
        return $this->isRanking();
    }

    public function setIsRankingMode(?bool $isRankingMode): static
    {
        $this->rewards['type'] = $isRankingMode ? 'ranking' : 'simple';

        return $this;
    }

    public function getRewardSimple(): ?string
    {
        return $this->rewards['value'] ?? null;
    }

    public function setRewardSimple(?string $rewardSimple): static
    {
        $this->rewards['value'] = $rewardSimple;

        return $this;
    }

    public function getReward1(): ?string
    {
        return $this->rewards['1'] ?? $this->rewards['reward1'] ?? null;
    }

    public function setReward1(?string $reward1): static
    {
        $this->rewards['1'] = $reward1;

        return $this;
    }

    public function getReward2(): ?string
    {
        return $this->rewards['2'] ?? $this->rewards['reward2'] ?? null;
    }

    public function setReward2(?string $reward2): static
    {
        $this->rewards['2'] = $reward2;

        return $this;
    }

    public function getReward3(): ?string
    {
        return $this->rewards['3'] ?? $this->rewards['reward3'] ?? null;
    }

    public function setReward3(?string $reward3): static
    {
        $this->rewards['3'] = $reward3;

        return $this;
    }

    public function getRewardGeneral(): ?string
    {
        return $this->rewards['general'] ?? null;
    }

    public function setRewardGeneral(?string $rewardGeneral): static
    {
        $this->rewards['general'] = $rewardGeneral;

        return $this;
    }
}