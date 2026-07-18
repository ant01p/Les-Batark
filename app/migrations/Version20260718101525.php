<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260718101525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute Order.finished, et rattrape le schéma en retard sur customer_email/customer_pseudo et la nullabilité (ON DELETE SET NULL) de order.user_id';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY `FK_F5299398A76ED395`');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              `order`
            ADD
              customer_email VARCHAR(255) NOT NULL,
            ADD
              customer_pseudo VARCHAR(255) NOT NULL,
            ADD
              finished TINYINT NOT NULL,
            CHANGE
              user_id user_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              `order`
            ADD
              CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE
            SET
              NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              `order`
            DROP
              customer_email,
            DROP
              customer_pseudo,
            DROP
              finished,
            CHANGE
              user_id user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              `order`
            ADD
              CONSTRAINT `FK_F5299398A76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON
            UPDATE
              NO ACTION ON DELETE NO ACTION
        SQL);
    }
}
