<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260622134600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, unit_price INT NOT NULL, order_id INT NOT NULL, product_id INT NOT NULL, type_id INT DEFAULT NULL, quality_id INT DEFAULT NULL, INDEX IDX_52EA1F098D9F6D38 (order_id), INDEX IDX_52EA1F094584665A (product_id), INDEX IDX_52EA1F09C54C8C93 (type_id), INDEX IDX_52EA1F09BCFC6D57 (quality_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F098D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09BCFC6D57 FOREIGN KEY (quality_id) REFERENCES quality (id)');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY `FK_F52993984584665A`');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY `FK_F5299398BCFC6D57`');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY `FK_F5299398C54C8C93`');
        $this->addSql('DROP INDEX IDX_F52993984584665A ON `order`');
        $this->addSql('DROP INDEX IDX_F5299398BCFC6D57 ON `order`');
        $this->addSql('DROP INDEX IDX_F5299398C54C8C93 ON `order`');
        $this->addSql('ALTER TABLE `order` ADD total INT NOT NULL, DROP product_id, DROP quantity, DROP unit_price, DROP type_id, DROP quality_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F098D9F6D38');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F094584665A');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09C54C8C93');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09BCFC6D57');
        $this->addSql('DROP TABLE order_item');
        $this->addSql('ALTER TABLE `order` ADD quantity INT NOT NULL, ADD unit_price INT NOT NULL, ADD type_id INT DEFAULT NULL, ADD quality_id INT DEFAULT NULL, CHANGE total product_id INT NOT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT `FK_F52993984584665A` FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT `FK_F5299398BCFC6D57` FOREIGN KEY (quality_id) REFERENCES quality (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT `FK_F5299398C54C8C93` FOREIGN KEY (type_id) REFERENCES type (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F52993984584665A ON `order` (product_id)');
        $this->addSql('CREATE INDEX IDX_F5299398BCFC6D57 ON `order` (quality_id)');
        $this->addSql('CREATE INDEX IDX_F5299398C54C8C93 ON `order` (type_id)');
    }
}
