<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210301091928 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invitation_category (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invitation ADD invitation_category_id INT NOT NULL');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A21AB191E2 FOREIGN KEY (invitation_category_id) REFERENCES invitation_category (id)');
        $this->addSql('CREATE INDEX IDX_F11D61A21AB191E2 ON invitation (invitation_category_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A21AB191E2');
        $this->addSql('DROP TABLE invitation_category');
        $this->addSql('DROP INDEX IDX_F11D61A21AB191E2 ON invitation');
        $this->addSql('ALTER TABLE invitation DROP invitation_category_id');
    }
}
