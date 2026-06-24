<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260609142748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE film ADD genre_id INT DEFAULT NULL');
        $this->addSql('UPDATE film f INNER JOIN (SELECT film_id, MIN(genre_id) AS genre_id FROM genre_film GROUP BY film_id) gf ON gf.film_id = f.id SET f.genre_id = gf.genre_id');
        $this->addSql('ALTER TABLE film MODIFY genre_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_8244BE224296D31F ON film (genre_id)');
        $this->addSql('ALTER TABLE film ADD CONSTRAINT FK_8244BE224296D31F FOREIGN KEY (genre_id) REFERENCES genre (id)');
        $this->addSql('ALTER TABLE genre_film DROP FOREIGN KEY `FK_39A967D24296D31F`');
        $this->addSql('ALTER TABLE genre_film DROP FOREIGN KEY `FK_39A967D2567F5183`');
        $this->addSql('DROP TABLE genre_film');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE genre_film (genre_id INT NOT NULL, film_id INT NOT NULL, INDEX IDX_39A967D24296D31F (genre_id), INDEX IDX_39A967D2567F5183 (film_id), PRIMARY KEY (genre_id, film_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE genre_film ADD CONSTRAINT `FK_39A967D24296D31F` FOREIGN KEY (genre_id) REFERENCES genre (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE genre_film ADD CONSTRAINT `FK_39A967D2567F5183` FOREIGN KEY (film_id) REFERENCES film (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('INSERT INTO genre_film (genre_id, film_id) SELECT genre_id, id FROM film');
        $this->addSql('ALTER TABLE film DROP FOREIGN KEY FK_8244BE224296D31F');
        $this->addSql('DROP INDEX IDX_8244BE224296D31F ON film');
        $this->addSql('ALTER TABLE film DROP genre_id');
    }
}
