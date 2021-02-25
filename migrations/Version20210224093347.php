<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210224093347 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE income (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, amount DOUBLE PRECISION NOT NULL, label VARCHAR(255) NOT NULL, year VARCHAR(255) NOT NULL, INDEX IDX_3FA862D0C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE income_type (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE income ADD CONSTRAINT FK_3FA862D0C54C8C93 FOREIGN KEY (type_id) REFERENCES income_type (id)');
        $this->addSql('ALTER TABLE document ADD income_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76640ED2C0 FOREIGN KEY (income_id) REFERENCES income (id)');
        $this->addSql('CREATE INDEX IDX_D8698A76640ED2C0 ON document (income_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76640ED2C0');
        $this->addSql('ALTER TABLE income DROP FOREIGN KEY FK_3FA862D0C54C8C93');
        $this->addSql('DROP TABLE income');
        $this->addSql('DROP TABLE income_type');
        $this->addSql('DROP INDEX IDX_D8698A76640ED2C0 ON document');
        $this->addSql('ALTER TABLE document DROP income_id');
    }
}
