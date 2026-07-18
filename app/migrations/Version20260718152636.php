<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260718152636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Retire product.image_url, colonne orpheline (absente de l\'entité Product depuis le passage à ProductImage)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP image_url');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD image_url VARCHAR(255) DEFAULT NULL');
    }
}
