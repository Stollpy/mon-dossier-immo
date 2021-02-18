<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210218155625 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profiles ADD parent_profile_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profiles ADD CONSTRAINT FK_8B3085308BB996E8 FOREIGN KEY (parent_profile_id) REFERENCES profiles (id)');
        $this->addSql('CREATE INDEX IDX_8B3085308BB996E8 ON profiles (parent_profile_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profiles DROP FOREIGN KEY FK_8B3085308BB996E8');
        $this->addSql('DROP INDEX IDX_8B3085308BB996E8 ON profiles');
        $this->addSql('ALTER TABLE profiles DROP parent_profile_id');
    }
}
