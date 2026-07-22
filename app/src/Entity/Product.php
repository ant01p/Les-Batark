<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    public const STATS = ['HP', 'Stamina', 'Oxygen', 'Food', 'Weight', 'Melee Damage'];
    public const OPTIONS = ['Full XP', 'Imprint 100%', 'Imprint 200%', 'Full XP Imprint 100%', 'Full XP Imprint 200%'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\Column]
    private ?bool $hasType = null;

    #[ORM\Column]
    private ?bool $hasQuality = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $versionsDisponibles = [];

    #[ORM\Column(nullable: true)]
    private ?bool $sexeActif = false;

    /**
     * @var Collection<int, ProductImage>
     */
    #[ORM\OneToMany(targetEntity: ProductImage::class, mappedBy: 'product', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $images;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function hasType(): ?bool
    {
        return $this->hasType;
    }

    public function setHasType(bool $hasType): static
    {
        $this->hasType = $hasType;

        return $this;
    }

    public function hasQuality(): ?bool
    {
        return $this->hasQuality;
    }

    public function setHasQuality(bool $hasQuality): static
    {
        $this->hasQuality = $hasQuality;

        return $this;
    }

    public function getVersionsDisponibles(): ?array
    {
        return $this->versionsDisponibles;
    }

    public function setVersionsDisponibles(?array $versionsDisponibles): static
    {
        $this->versionsDisponibles = $versionsDisponibles;

        return $this;
    }

    public function isSexeActif(): ?bool
    {
        return $this->sexeActif;
    }

    public function setSexeActif(?bool $sexeActif): static
    {
        $this->sexeActif = $sexeActif;

        return $this;
    }

    /**
     * @return Collection<int, ProductImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ProductImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProduct($this);
        }

        return $this;
    }

    public function removeImage(ProductImage $image): static
    {
        if ($this->images->removeElement($image)) {
            if ($image->getProduct() === $this) {
                $image->setProduct(null);
            }
        }

        return $this;
    }

    public function getMainImage(): ?ProductImage
    {
        foreach ($this->images as $image) {
            if ($image->isMain()) {
                return $image;
            }
        }

        return $this->images->first() ?: null;
    }

    /**
     * @return ProductImage[] images ordered with the main image first
     */
    public function getOrderedImages(): array
    {
        $images = $this->images->toArray();

        usort($images, fn (ProductImage $a, ProductImage $b) => (int) $b->isMain() <=> (int) $a->isMain());

        return $images;
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
