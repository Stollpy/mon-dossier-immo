<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210224180510 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE income CHANGE income_year_id income_year_id INT NOT NULL');
        $this->addSql('ALTER TABLE income ADD CONSTRAINT FK_3FA862D0F88388DE FOREIGN KEY (income_year_id) REFERENCES income_year (id)');
        $this->addSql('CREATE INDEX IDX_3FA862D0F88388DE ON income (income_year_id)');
        $this->addSql('ALTER TABLE income_year ADD individual_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE income_year ADD CONSTRAINT FK_71C86519AE271C0D FOREIGN KEY (individual_id) REFERENCES individual (id)');
        $this->addSql('CREATE INDEX IDX_71C86519AE271C0D ON income_year (individual_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE income DROP FOREIGN KEY FK_3FA862D0F88388DE');
        $this->addSql('DROP INDEX IDX_3FA862D0F88388DE ON income');
        $this->addSql('ALTER TABLE income CHANGE income_year_id income_year_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE income_year DROP FOREIGN KEY FK_71C86519AE271C0D');
        $this->addSql('DROP INDEX IDX_71C86519AE271C0D ON income_year');
        $this->addSql('ALTER TABLE income_year DROP individual_id');
    }
}
