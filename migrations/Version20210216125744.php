<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210216125744 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE individual_data ADD profil_model_data_id INT DEFAULT NULL, DROP code');
        $this->addSql('ALTER TABLE individual_data ADD CONSTRAINT FK_653169D0C3747536 FOREIGN KEY (profil_model_data_id) REFERENCES profil_model_data (id)');
        $this->addSql('CREATE INDEX IDX_653169D0C3747536 ON individual_data (profil_model_data_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE individual_data DROP FOREIGN KEY FK_653169D0C3747536');
        $this->addSql('DROP INDEX IDX_653169D0C3747536 ON individual_data');
        $this->addSql('ALTER TABLE individual_data ADD code VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP profil_model_data_id');
    }
}
