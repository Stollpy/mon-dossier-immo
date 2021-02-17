<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210217111312 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE individual_individual (individual_source INT NOT NULL, individual_target INT NOT NULL, INDEX IDX_C383430880B83090 (individual_source), INDEX IDX_C3834308995D601F (individual_target), PRIMARY KEY(individual_source, individual_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profiles (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profiles_profil_model_data (profiles_id INT NOT NULL, profil_model_data_id INT NOT NULL, INDEX IDX_2B6BE27022077C89 (profiles_id), INDEX IDX_2B6BE270C3747536 (profil_model_data_id), PRIMARY KEY(profiles_id, profil_model_data_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE individual_individual ADD CONSTRAINT FK_C383430880B83090 FOREIGN KEY (individual_source) REFERENCES individual (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE individual_individual ADD CONSTRAINT FK_C3834308995D601F FOREIGN KEY (individual_target) REFERENCES individual (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profiles_profil_model_data ADD CONSTRAINT FK_2B6BE27022077C89 FOREIGN KEY (profiles_id) REFERENCES profiles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profiles_profil_model_data ADD CONSTRAINT FK_2B6BE270C3747536 FOREIGN KEY (profil_model_data_id) REFERENCES profil_model_data (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profil_model_data ADD CONSTRAINT FK_BB62847AAA1B9FA0 FOREIGN KEY (individual_data_category_id) REFERENCES individual_data_category (id)');
        $this->addSql('CREATE INDEX IDX_BB62847AAA1B9FA0 ON profil_model_data (individual_data_category_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profiles_profil_model_data DROP FOREIGN KEY FK_2B6BE27022077C89');
        $this->addSql('DROP TABLE individual_individual');
        $this->addSql('DROP TABLE profiles');
        $this->addSql('DROP TABLE profiles_profil_model_data');
        $this->addSql('ALTER TABLE profil_model_data DROP FOREIGN KEY FK_BB62847AAA1B9FA0');
        $this->addSql('DROP INDEX IDX_BB62847AAA1B9FA0 ON profil_model_data');
    }
}
