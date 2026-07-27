<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260727191414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute la table admin_activity_log pour l'historique des actions d'administration";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE admin_activity_log (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(40) NOT NULL, subject_type VARCHAR(20) DEFAULT NULL, subject_id INT DEFAULT NULL, subject_label VARCHAR(255) DEFAULT NULL, actor_label VARCHAR(255) NOT NULL, detail VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, actor_id INT DEFAULT NULL, INDEX IDX_844754AA10DAF24A (actor_id), INDEX IDX_844754AA8B8E8428 (created_at), INDEX IDX_844754AA47CC8C92 (action), INDEX IDX_844754AA1E14D3F2 (subject_type), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE admin_activity_log ADD CONSTRAINT FK_844754AA10DAF24A FOREIGN KEY (actor_id) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE admin_activity_log DROP FOREIGN KEY FK_844754AA10DAF24A');
        $this->addSql('DROP TABLE admin_activity_log');
    }
}
