<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260722200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la liste des images du serveur (carrousel de la page publique)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE server ADD images VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE server DROP images');
    }
}
