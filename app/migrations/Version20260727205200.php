<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260727205200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD suspended_at DATETIME DEFAULT NULL, ADD anonymized_at DATETIME DEFAULT NULL');

        // Bootstrap de la hiérarchie : les comptes ROLE_ADMIN existants deviennent aussi
        // ROLE_SUPER_ADMIN, pour conserver leurs droits actuels (ex. historique complet,
        // gestion des permissions) qui sont désormais réservés à ROLE_SUPER_ADMIN.
        $this->addSql("UPDATE user SET roles = JSON_ARRAY_APPEND(roles, '$', 'ROLE_SUPER_ADMIN') WHERE JSON_CONTAINS(roles, '\"ROLE_ADMIN\"') AND NOT JSON_CONTAINS(roles, '\"ROLE_SUPER_ADMIN\"')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE user SET roles = JSON_REMOVE(roles, JSON_UNQUOTE(JSON_SEARCH(roles, 'one', 'ROLE_SUPER_ADMIN'))) WHERE JSON_CONTAINS(roles, '\"ROLE_ADMIN\"') AND JSON_CONTAINS(roles, '\"ROLE_SUPER_ADMIN\"')");
        $this->addSql('ALTER TABLE user DROP suspended_at, DROP anonymized_at');
    }
}
