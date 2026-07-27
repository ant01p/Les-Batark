<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260726215054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Remplace Server.images (liste de noms de fichiers) par une vraie entité ServerImage uploadée (même système que ProductImage)";
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE server_image (
              id INT AUTO_INCREMENT NOT NULL,
              filename VARCHAR(255) NOT NULL,
              is_main TINYINT NOT NULL,
              server_id INT NOT NULL,
              INDEX IDX_65BC66561844E6B7 (server_id),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              server_image
            ADD
              CONSTRAINT FK_65BC66561844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)
        SQL);
        $this->addSql('ALTER TABLE server DROP images');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE server_image DROP FOREIGN KEY FK_65BC66561844E6B7');
        $this->addSql('DROP TABLE server_image');
        $this->addSql('ALTER TABLE server ADD images VARCHAR(500) DEFAULT NULL');
    }
}
