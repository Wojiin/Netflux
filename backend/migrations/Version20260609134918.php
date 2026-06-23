<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260609134918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE film CHANGE img_link img_link VARCHAR(1024) NOT NULL, CHANGE video_link video_link VARCHAR(1024) NOT NULL');
        $this->addSql('ALTER TABLE person CHANGE portrait_link portrait_link VARCHAR(1024) NOT NULL');
        $this->addSql('ALTER TABLE poster CHANGE url url VARCHAR(1024) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE film CHANGE img_link img_link VARCHAR(255) NOT NULL, CHANGE video_link video_link VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE person CHANGE portrait_link portrait_link VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE poster CHANGE url url VARCHAR(255) NOT NULL');
    }
}
