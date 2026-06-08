<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260604134643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE film CHANGE released_at released_at DATE NOT NULL');
        $this->addSql('ALTER TABLE person CHANGE birthday birthday DATE NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_play_film_actor_role ON play (film_id, actor_id, role_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE film CHANGE released_at released_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE person CHANGE birthday birthday DATETIME NOT NULL');
        $this->addSql('DROP INDEX uniq_play_film_actor_role ON play');
    }
}
