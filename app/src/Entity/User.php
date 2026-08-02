<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_PSEUDO', fields: ['pseudo'])]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cette adresse email.')]
#[UniqueEntity(fields: ['pseudo'], message: 'Ce pseudo est déjà pris.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Rôles administratifs attribuables depuis la fiche membre, avec leur libellé lisible.
     * ROLE_ADMIN et ROLE_SUPER_ADMIN y figurent aussi : ce sont des rôles comme les autres,
     * simplement placés plus haut dans ROLE_LEVELS et security.yaml#role_hierarchy.
     *
     * @var array<string, string>
     */
    public const MANAGEABLE_ROLES = [
        'ROLE_ADMIN_EVENTS' => 'Gestion des événements',
        'ROLE_ADMIN_SHOP' => 'Gestion de la boutique',
        'ROLE_ADMIN_ORDERS' => 'Gestion des commandes',
        'ROLE_ADMIN_SERVERS' => 'Gestion des serveurs',
        'ROLE_ADMIN_RULES' => 'Gestion du règlement',
        'ROLE_ADMIN_MEMBERS' => 'Gestion des membres',
        'ROLE_ADMIN' => 'Administrateur (tous les droits)',
        'ROLE_SUPER_ADMIN' => 'Super-administrateur',
    ];

    /**
     * Niveau de chaque rôle administratif, utilisé pour interdire à un administrateur
     * d'attribuer un rôle supérieur à son propre niveau d'autorisation.
     *
     * @var array<string, int>
     */
    public const ROLE_LEVELS = [
        'ROLE_ADMIN_EVENTS' => 1,
        'ROLE_ADMIN_SHOP' => 1,
        'ROLE_ADMIN_ORDERS' => 1,
        'ROLE_ADMIN_SERVERS' => 1,
        'ROLE_ADMIN_RULES' => 1,
        'ROLE_ADMIN_MEMBERS' => 1,
        'ROLE_ADMIN' => 2,
        'ROLE_SUPER_ADMIN' => 3,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private ?string $pseudo = null;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orders;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Cart $cart = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $suspendedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $anonymizedAt = null;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): static
    {
        // set the owning side of the relation if necessary
        if ($cart->getUser() !== $this) {
            $cart->setUser($this);
        }

        $this->cart = $cart;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getSuspendedAt(): ?\DateTimeImmutable
    {
        return $this->suspendedAt;
    }

    public function setSuspendedAt(?\DateTimeImmutable $suspendedAt): static
    {
        $this->suspendedAt = $suspendedAt;

        return $this;
    }

    public function isSuspended(): bool
    {
        return $this->suspendedAt !== null;
    }

    public function getAnonymizedAt(): ?\DateTimeImmutable
    {
        return $this->anonymizedAt;
    }

    public function setAnonymizedAt(?\DateTimeImmutable $anonymizedAt): static
    {
        $this->anonymizedAt = $anonymizedAt;

        return $this;
    }

    public function isAnonymized(): bool
    {
        return $this->anonymizedAt !== null;
    }

    /**
     * Rôles réellement stockés (sans le ROLE_USER garanti par getRoles()), utilisé par la
     * gestion des permissions pour calculer les rôles ajoutés/retirés sans jamais y toucher.
     *
     * @return list<string>
     */
    public function getAssignedRoles(): array
    {
        return $this->roles;
    }

    /**
     * True si ce membre détient au moins un rôle administratif géré depuis la fiche membre.
     */
    public function isAdministrator(): bool
    {
        return array_intersect($this->roles, array_keys(self::MANAGEABLE_ROLES)) !== [];
    }
}
