<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260717221244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_item ADD sex VARCHAR(20) DEFAULT NULL, ADD version VARCHAR(20) DEFAULT NULL, ADD stat VARCHAR(30) DEFAULT NULL, ADD `option` VARCHAR(50) DEFAULT NULL, ADD color VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE order_item ADD sex VARCHAR(20) DEFAULT NULL, ADD version VARCHAR(20) DEFAULT NULL, ADD stat VARCHAR(30) DEFAULT NULL, ADD `option` VARCHAR(50) DEFAULT NULL, ADD color VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD versions_disponibles JSON DEFAULT NULL, ADD sexe_actif TINYINT DEFAULT NULL, ADD option_dino VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_item DROP sex, DROP version, DROP stat, DROP `option`, DROP color');
        $this->addSql('ALTER TABLE order_item DROP sex, DROP version, DROP stat, DROP `option`, DROP color');
        $this->addSql('ALTER TABLE product DROP versions_disponibles, DROP sexe_actif, DROP option_dino');
    }
}
