<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260617111500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add film ratings table and aggregated rating columns on film.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE film ADD average_rate DECIMAL(3, 1) DEFAULT NULL, ADD ratings_count INT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE TABLE film_rating (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, film_id INT NOT NULL, rate INT NOT NULL, INDEX IDX_6B6FEC3AA76ED395 (user_id), INDEX IDX_6B6FEC3A567F5183 (film_id), UNIQUE INDEX uniq_user_film_rating (user_id, film_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE film_rating ADD CONSTRAINT FK_6B6FEC3AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE film_rating ADD CONSTRAINT FK_6B6FEC3A567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE film_rating DROP FOREIGN KEY FK_6B6FEC3AA76ED395');
        $this->addSql('ALTER TABLE film_rating DROP FOREIGN KEY FK_6B6FEC3A567F5183');
        $this->addSql('DROP TABLE film_rating');
        $this->addSql('ALTER TABLE film DROP average_rate, DROP ratings_count');
    }
}
