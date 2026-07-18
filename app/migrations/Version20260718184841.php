<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260718184841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute 2 types produits : 'Déjà craft x2' et 'BluePrint'";
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO type (name) VALUES ('Déjà craft x2')");
        $this->addSql("INSERT INTO type (name) VALUES ('BluePrint')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM type WHERE name IN ('Déjà craft x2', 'BluePrint')");
    }
}
