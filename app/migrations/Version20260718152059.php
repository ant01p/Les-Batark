<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260718152059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute Order.deliveredAt pour le suivi de livraison côté admin';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `order` ADD delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `order` DROP delivered_at');
    }
}
