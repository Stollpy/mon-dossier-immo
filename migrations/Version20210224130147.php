<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210224130147 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE income ADD individual_id INT NOT NULL');
        $this->addSql('ALTER TABLE income ADD CONSTRAINT FK_3FA862D0AE271C0D FOREIGN KEY (individual_id) REFERENCES individual (id)');
        $this->addSql('CREATE INDEX IDX_3FA862D0AE271C0D ON income (individual_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE income DROP FOREIGN KEY FK_3FA862D0AE271C0D');
        $this->addSql('DROP INDEX IDX_3FA862D0AE271C0D ON income');
        $this->addSql('ALTER TABLE income DROP individual_id');
    }
}
