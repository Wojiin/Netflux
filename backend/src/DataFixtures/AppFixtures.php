<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Director;
use App\Entity\Film;
use App\Entity\Genre;
use App\Entity\Person;
use App\Entity\Play;
use App\Entity\Poster;
use App\Entity\Role;
use App\Enum\ContentType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $genres = [
            'Science-fiction' => $this->createGenre('Science-fiction'),
            'Action' => $this->createGenre('Action'),
            'Thriller' => $this->createGenre('Thriller'),
            'Drame' => $this->createGenre('Drame'),
            'Crime' => $this->createGenre('Crime'),
            'Aventure' => $this->createGenre('Aventure'),
            'Fantastique' => $this->createGenre('Fantastique'),
        ];

        foreach ($genres as $genre) {
            $manager->persist($genre);
        }

        $nolan = $this->createDirector('Christopher', 'Nolan', 'male', '1970-07-30');
        $wachowski = $this->createDirector('Lana', 'Wachowski', 'female', '1965-06-21');
        $darabont = $this->createDirector('Frank', 'Darabont', 'male', '1959-01-28');
        $jackson = $this->createDirector('Peter', 'Jackson', 'male', '1961-10-31');

        foreach ([$nolan, $wachowski, $darabont, $jackson] as $director) {
            $manager->persist($director->getPerson());
            $manager->persist($director);
        }

        $actors = [
            'leonardo_dicaprio' => $this->createActor('Leonardo', 'DiCaprio', 'male', '1974-11-11'),
            'joseph_gordon_levitt' => $this->createActor('Joseph', 'Gordon-Levitt', 'male', '1981-02-17'),
            'elliot_page' => $this->createActor('Elliot', 'Page', 'non-binary', '1987-02-21'),
            'keanu_reeves' => $this->createActor('Keanu', 'Reeves', 'male', '1964-09-02'),
            'carrie_anne_moss' => $this->createActor('Carrie-Anne', 'Moss', 'female', '1967-08-21'),
            'laurence_fishburne' => $this->createActor('Laurence', 'Fishburne', 'male', '1961-07-30'),
            'tim_robbins' => $this->createActor('Tim', 'Robbins', 'male', '1958-10-16'),
            'morgan_freeman' => $this->createActor('Morgan', 'Freeman', 'male', '1937-06-01'),
            'bob_gunton' => $this->createActor('Bob', 'Gunton', 'male', '1945-11-15'),
            'elijah_wood' => $this->createActor('Elijah', 'Wood', 'male', '1981-01-28'),
            'ian_mckellen' => $this->createActor('Ian', 'McKellen', 'male', '1939-05-25'),
            'viggo_mortensen' => $this->createActor('Viggo', 'Mortensen', 'male', '1958-10-20'),
        ];

        foreach ($actors as $actor) {
            $manager->persist($actor->getPerson());
            $manager->persist($actor);
        }

        $inception = $this->createFilm(
            title: 'Inception',
            director: $nolan,
            type: ContentType::MOVIE,
            releasedAt: '2010-07-16',
            imgLink: 'https://image.tmdb.org/t/p/w500/oYuLEt3zVCKq57qu2F8dT7NIa6f.jpg',
            videoLink: 'https://www.imdb.com/video/vi2861040665/',
            duration: 148,
            synopsis: "Dom Cobb est un voleur spécialiste de l'extraction de secrets dans les rêves et accepte une mission d'inception réputée impossible.",
            rate: 5,
            genres: [$genres['Science-fiction'], $genres['Action'], $genres['Thriller']],
        );

        $matrix = $this->createFilm(
            title: 'The Matrix',
            director: $wachowski,
            type: ContentType::MOVIE,
            releasedAt: '1999-03-31',
            imgLink: 'https://image.tmdb.org/t/p/w500/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg',
            videoLink: 'https://www.imdb.com/video/vi3203793177/',
            duration: 136,
            synopsis: "Neo découvre que la réalité est une simulation et rejoint la résistance pour combattre les machines.",
            rate: 5,
            genres: [$genres['Science-fiction'], $genres['Action']],
        );

        $shawshank = $this->createFilm(
            title: 'The Shawshank Redemption',
            director: $darabont,
            type: ContentType::MOVIE,
            releasedAt: '1994-09-23',
            imgLink: 'https://image.tmdb.org/t/p/w500/9cqNxx0GxF0bflZmeSMuL5tnGzr.jpg',
            videoLink: 'https://www.imdb.com/video/vi3877612057/',
            duration: 142,
            synopsis: "Condamné à perpétuité, Andy Dufresne noue une profonde amitié avec Red et garde espoir malgré les années de prison.",
            rate: 5,
            genres: [$genres['Drame'], $genres['Crime']],
        );

        $lotr = $this->createFilm(
            title: 'The Lord of the Rings: The Fellowship of the Ring',
            director: $jackson,
            type: ContentType::MOVIE,
            releasedAt: '2001-12-19',
            imgLink: 'https://image.tmdb.org/t/p/w500/6oom5QYQ2yQTMJIbnvbkBL9cHo6.jpg',
            videoLink: 'https://www.imdb.com/title/tt0120737/trailers/',
            duration: 178,
            synopsis: "Frodon hérite de l'Anneau Unique et quitte la Comté avec la Communauté pour empêcher Sauron de régner sur la Terre du Milieu.",
            rate: 5,
            genres: [$genres['Aventure'], $genres['Fantastique'], $genres['Action']],
        );

        $films = [$inception, $matrix, $shawshank, $lotr];
        foreach ($films as $film) {
            $manager->persist($film);
            $manager->persist($this->createPoster($film, $film->getImgLink()));
        }

        $plays = [
            $this->createPlay($inception, $actors['leonardo_dicaprio'], 'Dom', 'Cobb'),
            $this->createPlay($inception, $actors['joseph_gordon_levitt'], 'Arthur'),
            $this->createPlay($inception, $actors['elliot_page'], 'Ariadne'),
            $this->createPlay($matrix, $actors['keanu_reeves'], 'Neo'),
            $this->createPlay($matrix, $actors['carrie_anne_moss'], 'Trinity'),
            $this->createPlay($matrix, $actors['laurence_fishburne'], 'Morpheus'),
            $this->createPlay($shawshank, $actors['tim_robbins'], 'Andy', 'Dufresne'),
            $this->createPlay($shawshank, $actors['morgan_freeman'], 'Ellis', 'Redding'),
            $this->createPlay($shawshank, $actors['bob_gunton'], 'Warden', 'Norton'),
            $this->createPlay($lotr, $actors['elijah_wood'], 'Frodo', 'Baggins'),
            $this->createPlay($lotr, $actors['ian_mckellen'], 'Gandalf'),
            $this->createPlay($lotr, $actors['viggo_mortensen'], 'Aragorn'),
        ];

        foreach ($plays as $play) {
            $manager->persist($play->getRole());
            $manager->persist($play);
        }

        $manager->flush();
    }

    private function createGenre(string $name): Genre
    {
        $genre = new Genre();
        $genre->setName($name);

        return $genre;
    }

    private function createPerson(string $firstName, string $lastName, string $gender, string $birthday): Person
    {
        $person = new Person();
        $person->setFirstName($firstName);
        $person->setLastName($lastName);
        $person->setGender($gender);
        $person->setBirthday(new \DateTimeImmutable($birthday));

        return $person;
    }

    private function createDirector(string $firstName, string $lastName, string $gender, string $birthday): Director
    {
        $director = new Director();
        $director->setPerson($this->createPerson($firstName, $lastName, $gender, $birthday));

        return $director;
    }

    private function createActor(string $firstName, string $lastName, string $gender, string $birthday): Actor
    {
        $actor = new Actor();
        $actor->setPerson($this->createPerson($firstName, $lastName, $gender, $birthday));

        return $actor;
    }

    /**
     * @param Genre[] $genres
     */
    private function createFilm(
        string $title,
        Director $director,
        ContentType $type,
        string $releasedAt,
        string $imgLink,
        string $videoLink,
        int $duration,
        string $synopsis,
        ?int $rate,
        array $genres,
    ): Film {
        $film = new Film();
        $film->setTitle($title);
        $film->setDirector($director);
        $film->setType($type);
        $film->setReleasedAt(new \DateTimeImmutable($releasedAt));
        $film->setImgLink($imgLink);
        $film->setVideoLink($videoLink);
        $film->setDuration($duration);
        $film->setSynopsis($synopsis);
        $film->setRate($rate);

        foreach ($genres as $genre) {
            $film->addGenre($genre);
        }

        return $film;
    }

    private function createPoster(Film $film, string $url): Poster
    {
        $poster = new Poster();
        $poster->setFilm($film);
        $poster->setUrl($url);

        return $poster;
    }

    private function createPlay(Film $film, Actor $actor, string $characterFirstName, ?string $characterLastName = null): Play
    {
        $role = new Role();
        $role->setCharacterFirstName($characterFirstName);
        $role->setCharacterLastName($characterLastName);

        $play = new Play();
        $play->setFilm($film);
        $play->setActor($actor);
        $play->setRole($role);

        return $play;
    }
}
