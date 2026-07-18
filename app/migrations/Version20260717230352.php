<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260717230352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renomme le type "Déjà crafté" en "Déjà crafté x2" et réinsère les qualités dans l\'ordre Commun, Inhabituel, Rare, Épique, Légendaire, Mythique';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE type SET name = \'Déjà crafté x2\' WHERE name = \'Déjà crafté\'');

        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('DELETE FROM quality');
        $this->addSql('ALTER TABLE quality AUTO_INCREMENT = 1');
        $this->addSql('INSERT INTO quality (name) VALUES (\'Commun\'), (\'Inhabituel\'), (\'Rare\'), (\'Épique\'), (\'Légendaire\'), (\'Mythique\')');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE type SET name = \'Déjà crafté\' WHERE name = \'Déjà crafté x2\'');

        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('DELETE FROM quality');
        $this->addSql('ALTER TABLE quality AUTO_INCREMENT = 1');
        $this->addSql('INSERT INTO quality (name) VALUES (\'Épique\'), (\'Légendaire\'), (\'Mythique\')');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }
}
