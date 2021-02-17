<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210217130953 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE individual_profiles (individual_id INT NOT NULL, profiles_id INT NOT NULL, INDEX IDX_1800A62CAE271C0D (individual_id), INDEX IDX_1800A62C22077C89 (profiles_id), PRIMARY KEY(individual_id, profiles_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE individual_profiles ADD CONSTRAINT FK_1800A62CAE271C0D FOREIGN KEY (individual_id) REFERENCES individual (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE individual_profiles ADD CONSTRAINT FK_1800A62C22077C89 FOREIGN KEY (profiles_id) REFERENCES profiles (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE individual_profiles');
    }
}
