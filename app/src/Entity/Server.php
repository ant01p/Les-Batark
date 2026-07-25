<?php

namespace App\Entity;

use App\Repository\ServerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServerRepository::class)]
class Server
{
    public const MODE_PVE = 'PVE';
    public const MODE_PVP = 'PVP';

    /** Images utilisées quand le serveur n'a pas encore de photos propres renseignées. */
    private const DEFAULT_IMAGES = ['jungle.png', 'abe.jpg'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 10)]
    private ?string $gameMode = null;

    #[ORM\Column]
    private ?int $maxPlayers = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $harvestRate = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $tamingSpeed = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $matingInterval = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $maturationSpeed = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $incubationSpeed = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $itemStackSize = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $wildDinoFoodDrain = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxDinoLevel = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxTekDinoLevel = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxEggLevel = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $mods = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $images = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getGameMode(): ?string
    {
        return $this->gameMode;
    }

    public function setGameMode(string $gameMode): static
    {
        $this->gameMode = $gameMode;

        return $this;
    }

    public function getMaxPlayers(): ?int
    {
        return $this->maxPlayers;
    }

    public function setMaxPlayers(int $maxPlayers): static
    {
        $this->maxPlayers = $maxPlayers;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getHarvestRate(): ?float
    {
        return $this->harvestRate;
    }

    public function setHarvestRate(?float $harvestRate): static
    {
        $this->harvestRate = $harvestRate;

        return $this;
    }

    public function getTamingSpeed(): ?float
    {
        return $this->tamingSpeed;
    }

    public function setTamingSpeed(?float $tamingSpeed): static
    {
        $this->tamingSpeed = $tamingSpeed;

        return $this;
    }

    public function getMatingInterval(): ?float
    {
        return $this->matingInterval;
    }

    public function setMatingInterval(?float $matingInterval): static
    {
        $this->matingInterval = $matingInterval;

        return $this;
    }

    public function getMaturationSpeed(): ?float
    {
        return $this->maturationSpeed;
    }

    public function setMaturationSpeed(?float $maturationSpeed): static
    {
        $this->maturationSpeed = $maturationSpeed;

        return $this;
    }

    public function getIncubationSpeed(): ?float
    {
        return $this->incubationSpeed;
    }

    public function setIncubationSpeed(?float $incubationSpeed): static
    {
        $this->incubationSpeed = $incubationSpeed;

        return $this;
    }

    public function getItemStackSize(): ?float
    {
        return $this->itemStackSize;
    }

    public function setItemStackSize(?float $itemStackSize): static
    {
        $this->itemStackSize = $itemStackSize;

        return $this;
    }

    public function getWildDinoFoodDrain(): ?float
    {
        return $this->wildDinoFoodDrain;
    }

    public function setWildDinoFoodDrain(?float $wildDinoFoodDrain): static
    {
        $this->wildDinoFoodDrain = $wildDinoFoodDrain;

        return $this;
    }

    public function getMaxDinoLevel(): ?int
    {
        return $this->maxDinoLevel;
    }

    public function setMaxDinoLevel(?int $maxDinoLevel): static
    {
        $this->maxDinoLevel = $maxDinoLevel;

        return $this;
    }

    public function getMaxTekDinoLevel(): ?int
    {
        return $this->maxTekDinoLevel;
    }

    public function setMaxTekDinoLevel(?int $maxTekDinoLevel): static
    {
        $this->maxTekDinoLevel = $maxTekDinoLevel;

        return $this;
    }

    public function getMaxEggLevel(): ?int
    {
        return $this->maxEggLevel;
    }

    public function setMaxEggLevel(?int $maxEggLevel): static
    {
        $this->maxEggLevel = $maxEggLevel;

        return $this;
    }

    public function getMods(): ?string
    {
        return $this->mods;
    }

    public function setMods(?string $mods): static
    {
        $this->mods = $mods;

        return $this;
    }

    /**
     * @return string[] mod names parsed from the comma-separated raw value
     */
    public function getModsList(): array
    {
        if (!$this->mods) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->mods))));
    }

    public function getImages(): ?string
    {
        return $this->images;
    }

    public function setImages(?string $images): static
    {
        $this->images = $images;

        return $this;
    }

    /**
     * @return string[] image filenames (relative to public/images/) parsed from the comma-separated raw value
     */
    public function getImagesList(): array
    {
        if (!$this->images) {
            return self::DEFAULT_IMAGES;
        }

        $list = array_values(array_filter(array_map('trim', explode(',', $this->images))));

        return $list ?: self::DEFAULT_IMAGES;
    }

    public function isPve(): bool
    {
        return $this->gameMode !== self::MODE_PVP;
    }

    /**
     * Jeton court identifiant la couleur associée au mode de jeu (bleu = PvE, violet = PvP).
     */
    public function getAccent(): string
    {
        return $this->isPve() ? 'info' : 'primary';
    }

    public function getBorderClass(): string
    {
        return 'border-' . $this->getAccent();
    }

    public function getModeBadgeClass(): string
    {
        return 'bg-' . $this->getAccent();
    }

    public function getCardModifierClass(): string
    {
        return 'server-card--' . ($this->isPve() ? 'pve' : 'pvp');
    }

    /**
     * Liste ordonnée des paramètres de jeu à afficher (icône + libellé + valeur formatée),
     * en ignorant les paramètres non renseignés.
     *
     * @return array<int, array{icon: string, label: string, value: string}>
     */
    public function getFormattedSettings(): array
    {
        $multipliers = [
            ['icon' => 'bi-hammer', 'label' => 'Récolte', 'value' => $this->harvestRate],
            ['icon' => 'bi-tools', 'label' => 'Apprivoisement', 'value' => $this->tamingSpeed],
            ['icon' => 'bi-heart-fill', 'label' => 'Accouplement', 'value' => $this->matingInterval],
            ['icon' => 'bi-feather', 'label' => 'Maturité', 'value' => $this->maturationSpeed],
            ['icon' => 'bi-egg-fill', 'label' => 'Incubation', 'value' => $this->incubationSpeed],
            ['icon' => 'bi-cup-straw', 'label' => 'Faim des dinos', 'value' => $this->wildDinoFoodDrain],
        ];

        $quantities = [
            ['icon' => 'bi-boxes', 'label' => "Piles d'objets", 'value' => $this->itemStackSize],
            ['icon' => 'bi-graph-up-arrow', 'label' => 'Niveau max dino', 'value' => $this->maxDinoLevel],
            ['icon' => 'bi-cpu-fill', 'label' => 'Niveau max Tek', 'value' => $this->maxTekDinoLevel],
            ['icon' => 'bi-egg', 'label' => 'Niveau max œuf', 'value' => $this->maxEggLevel],
        ];

        $settings = [];

        foreach ($multipliers as $multiplier) {
            if ($multiplier['value'] === null) {
                continue;
            }

            $settings[] = [
                'icon'  => $multiplier['icon'],
                'label' => $multiplier['label'],
                'value' => 'x' . self::formatNumber((float) $multiplier['value']),
            ];
        }

        foreach ($quantities as $quantity) {
            if ($quantity['value'] === null) {
                continue;
            }

            $settings[] = [
                'icon'  => $quantity['icon'],
                'label' => $quantity['label'],
                'value' => self::formatNumber((float) $quantity['value']),
            ];
        }

        return $settings;
    }

    /**
     * Formate un nombre sans décimale inutile (5.0 -> "5", 0.2 -> "0.2").
     */
    private static function formatNumber(float $value): string
    {
        if (fmod($value, 1.0) === 0.0) {
            return (string) (int) $value;
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
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
}
