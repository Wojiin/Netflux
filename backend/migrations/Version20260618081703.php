<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260618081703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename film_rating indexes to match Doctrine naming strategy.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE film_rating RENAME INDEX idx_6b6fec3aa76ed395 TO IDX_B37B1B28A76ED395');
        $this->addSql('ALTER TABLE film_rating RENAME INDEX idx_6b6fec3a567f5183 TO IDX_B37B1B28567F5183');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE film_rating RENAME INDEX idx_b37b1b28567f5183 TO IDX_6B6FEC3A567F5183');
        $this->addSql('ALTER TABLE film_rating RENAME INDEX idx_b37b1b28a76ed395 TO IDX_6B6FEC3AA76ED395');
    }
}
