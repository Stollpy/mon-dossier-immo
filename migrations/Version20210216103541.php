<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210216103541 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profil_model_data ADD individual_data_id INT NOT NULL');
        $this->addSql('ALTER TABLE profil_model_data ADD CONSTRAINT FK_BB62847AE2920B1 FOREIGN KEY (individual_data_id) REFERENCES individual_data (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BB62847AE2920B1 ON profil_model_data (individual_data_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profil_model_data DROP FOREIGN KEY FK_BB62847AE2920B1');
        $this->addSql('DROP INDEX UNIQ_BB62847AE2920B1 ON profil_model_data');
        $this->addSql('ALTER TABLE profil_model_data DROP individual_data_id');
    }
}
