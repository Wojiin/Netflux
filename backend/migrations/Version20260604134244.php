<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260604134244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE actor (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, UNIQUE INDEX UNIQ_447556F9217BBB47 (person_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE director (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, UNIQUE INDEX UNIQ_1E90D3F0217BBB47 (person_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE film (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, released_at DATETIME NOT NULL, img_link VARCHAR(255) NOT NULL, duration INT NOT NULL, synopsis LONGTEXT NOT NULL, rate INT DEFAULT NULL, director_id INT NOT NULL, INDEX IDX_8244BE22899FB366 (director_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE genre (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE genre_film (genre_id INT NOT NULL, film_id INT NOT NULL, INDEX IDX_39A967D24296D31F (genre_id), INDEX IDX_39A967D2567F5183 (film_id), PRIMARY KEY (genre_id, film_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(150) NOT NULL, last_name VARCHAR(150) NOT NULL, gender VARCHAR(50) NOT NULL, birthday DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE play (id INT AUTO_INCREMENT NOT NULL, film_id INT NOT NULL, actor_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_5E89DEBA567F5183 (film_id), INDEX IDX_5E89DEBA10DAF24A (actor_id), INDEX IDX_5E89DEBAD60322AC (role_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE poster (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, film_id INT NOT NULL, INDEX IDX_2D710CF2567F5183 (film_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, character_first_name VARCHAR(150) NOT NULL, character_last_name VARCHAR(150) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_liked_film (user_id INT NOT NULL, film_id INT NOT NULL, INDEX IDX_A1DFD65CA76ED395 (user_id), INDEX IDX_A1DFD65C567F5183 (film_id), PRIMARY KEY (user_id, film_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_watched_film (user_id INT NOT NULL, film_id INT NOT NULL, INDEX IDX_F07BAF49A76ED395 (user_id), INDEX IDX_F07BAF49567F5183 (film_id), PRIMARY KEY (user_id, film_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE actor ADD CONSTRAINT FK_447556F9217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE director ADD CONSTRAINT FK_1E90D3F0217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE film ADD CONSTRAINT FK_8244BE22899FB366 FOREIGN KEY (director_id) REFERENCES director (id)');
        $this->addSql('ALTER TABLE genre_film ADD CONSTRAINT FK_39A967D24296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE genre_film ADD CONSTRAINT FK_39A967D2567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE play ADD CONSTRAINT FK_5E89DEBA567F5183 FOREIGN KEY (film_id) REFERENCES film (id)');
        $this->addSql('ALTER TABLE play ADD CONSTRAINT FK_5E89DEBA10DAF24A FOREIGN KEY (actor_id) REFERENCES actor (id)');
        $this->addSql('ALTER TABLE play ADD CONSTRAINT FK_5E89DEBAD60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE poster ADD CONSTRAINT FK_2D710CF2567F5183 FOREIGN KEY (film_id) REFERENCES film (id)');
        $this->addSql('ALTER TABLE user_liked_film ADD CONSTRAINT FK_A1DFD65CA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_liked_film ADD CONSTRAINT FK_A1DFD65C567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_watched_film ADD CONSTRAINT FK_F07BAF49A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_watched_film ADD CONSTRAINT FK_F07BAF49567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actor DROP FOREIGN KEY FK_447556F9217BBB47');
        $this->addSql('ALTER TABLE director DROP FOREIGN KEY FK_1E90D3F0217BBB47');
        $this->addSql('ALTER TABLE film DROP FOREIGN KEY FK_8244BE22899FB366');
        $this->addSql('ALTER TABLE genre_film DROP FOREIGN KEY FK_39A967D24296D31F');
        $this->addSql('ALTER TABLE genre_film DROP FOREIGN KEY FK_39A967D2567F5183');
        $this->addSql('ALTER TABLE play DROP FOREIGN KEY FK_5E89DEBA567F5183');
        $this->addSql('ALTER TABLE play DROP FOREIGN KEY FK_5E89DEBA10DAF24A');
        $this->addSql('ALTER TABLE play DROP FOREIGN KEY FK_5E89DEBAD60322AC');
        $this->addSql('ALTER TABLE poster DROP FOREIGN KEY FK_2D710CF2567F5183');
        $this->addSql('ALTER TABLE user_liked_film DROP FOREIGN KEY FK_A1DFD65CA76ED395');
        $this->addSql('ALTER TABLE user_liked_film DROP FOREIGN KEY FK_A1DFD65C567F5183');
        $this->addSql('ALTER TABLE user_watched_film DROP FOREIGN KEY FK_F07BAF49A76ED395');
        $this->addSql('ALTER TABLE user_watched_film DROP FOREIGN KEY FK_F07BAF49567F5183');
        $this->addSql('DROP TABLE actor');
        $this->addSql('DROP TABLE director');
        $this->addSql('DROP TABLE film');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE genre_film');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE play');
        $this->addSql('DROP TABLE poster');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE user_liked_film');
        $this->addSql('DROP TABLE user_watched_film');
    }
}
