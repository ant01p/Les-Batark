<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260621143540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD quantity INT NOT NULL, ADD unit_price INT NOT NULL, ADD type_id INT DEFAULT NULL, ADD quality_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398BCFC6D57 FOREIGN KEY (quality_id) REFERENCES quality (id)');
        $this->addSql('CREATE INDEX IDX_F5299398C54C8C93 ON `order` (type_id)');
        $this->addSql('CREATE INDEX IDX_F5299398BCFC6D57 ON `order` (quality_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398C54C8C93');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398BCFC6D57');
        $this->addSql('DROP INDEX IDX_F5299398C54C8C93 ON `order`');
        $this->addSql('DROP INDEX IDX_F5299398BCFC6D57 ON `order`');
        $this->addSql('ALTER TABLE `order` DROP quantity, DROP unit_price, DROP type_id, DROP quality_id');
    }
}
