<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260726135114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute Product.createdAt (pour le fil d'actualités de l'accueil), backfillée à la date d'exécution pour les produits existants";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE product ALTER created_at DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP created_at');
    }
}
