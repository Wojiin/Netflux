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
        $genres = $this->createGenres($manager);

        $peopleData = $this->getPeopleData();
        $people = [];

        foreach ($peopleData as $key => $data) {
            $people[$key] = $this->createPerson(
                firstName: $data['firstName'],
                lastName: $data['lastName'],
                gender: $data['gender'],
                birthday: $data['birthday'],
            );

            $people[$key]->setPortraitLink($this->buildPortraitUrl($key, $data['firstName'], $data['lastName']));
            $manager->persist($people[$key]);
        }

        $directors = [];
        foreach ($this->getDirectorKeys() as $key) {
            $director = new Director();
            $director->setPerson($people[$key]);
            $directors[$key] = $director;
            $manager->persist($director);
        }

        $actors = [];
        foreach ($this->getActorKeys() as $key) {
            $actor = new Actor();
            $actor->setPerson($people[$key]);
            $actors[$key] = $actor;
            $manager->persist($actor);
        }

        $works = $this->getWorks();

        foreach ($works as $work) {
            $film = $this->createFilm(
                title: $work['title'],
                directors: array_map(static fn (string $key): Director => $directors[$key], $work['directors']),
                type: $work['type'],
                releasedAt: $work['releasedAt'],
                imgLink: $this->buildPosterUrl($work['title']),
                videoLink: $this->buildTrailerUrl($work['title']),
                duration: $work['duration'],
                synopsis: $work['synopsis'],
                rate: $work['rate'],
                genre: $genres[$work['genre']],
            );

            $manager->persist($film);
            $manager->persist($this->createPoster($film, $film->getImgLink()));

            foreach ($work['cast'] ?? [] as $cast) {
                $play = $this->createPlay(
                    film: $film,
                    actor: $actors[$cast['actor']],
                    characterFirstName: $cast['first'],
                    characterLastName: $cast['last'] ?? null,
                );

                $manager->persist($play->getRole());
                $manager->persist($play);
            }
        }

        $manager->flush();
    }

    /**
     * @return array<string, Genre>
     */
    private function createGenres(ObjectManager $manager): array
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

        return $genres;
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

    /**
     * @param array<int, Director> $directors
     */
    private function createFilm(
        string $title,
        array $directors,
        ContentType $type,
        string $releasedAt,
        string $imgLink,
        string $videoLink,
        int $duration,
        string $synopsis,
        ?int $rate,
        Genre $genre,
    ): Film {
        $film = new Film();
        $film->setTitle($title);
        $film->setType($type);
        $film->setReleasedAt(new \DateTimeImmutable($releasedAt));
        $film->setImgLink($imgLink);
        $film->setVideoLink($videoLink);
        $film->setDuration($duration);
        $film->setSynopsis($synopsis);
        $film->setRate($rate);

        foreach ($directors as $director) {
            $film->addDirector($director);
        }

        $film->setGenre($genre);

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

    private function buildPosterUrl(string $title): string
    {
        $posters = [
            'Inception' => 'https://upload.wikimedia.org/wikipedia/en/2/2e/Inception_%282010%29_theatrical_poster.jpg',
            'The Matrix' => 'https://upload.wikimedia.org/wikipedia/en/d/db/The_Matrix.png',
            'The Shawshank Redemption' => 'https://upload.wikimedia.org/wikipedia/en/8/81/ShawshankRedemptionMoviePoster.jpg',
            'The Lord of the Rings: The Fellowship of the Ring' => 'https://upload.wikimedia.org/wikipedia/en/f/fb/Lord_Rings_Fellowship_Ring.jpg',
            'Interstellar' => 'https://upload.wikimedia.org/wikipedia/en/b/bc/Interstellar_film_poster.jpg',
            'The Dark Knight' => 'https://upload.wikimedia.org/wikipedia/en/1/1c/The_Dark_Knight_%282008_film%29.jpg',
            'Se7en' => 'https://upload.wikimedia.org/wikipedia/en/6/68/Seven_%28movie%29_poster.jpg',
            'Argo' => 'https://upload.wikimedia.org/wikipedia/en/e/e1/Argo2012Poster.jpg',
            'Dune' => 'https://upload.wikimedia.org/wikipedia/en/8/8e/Dune_%282021_film%29.jpg',
            'Mad Max: Fury Road' => 'https://upload.wikimedia.org/wikipedia/en/6/6e/Mad_Max_Fury_Road.jpg',
            'The Departed' => 'https://upload.wikimedia.org/wikipedia/en/5/50/Departed234.jpg',
            'No Country for Old Men' => 'https://upload.wikimedia.org/wikipedia/en/8/8b/No_Country_for_Old_Men_poster.jpg',
            'Breaking Bad' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/77/Breaking_Bad_logo.svg/500px-Breaking_Bad_logo.svg.png',
            'Stranger Things' => 'https://upload.wikimedia.org/wikipedia/commons/3/38/Stranger_Things_logo.png',
            'True Detective' => 'https://upload.wikimedia.org/wikipedia/en/5/5a/True_Detective_2014_Intertitle.jpg',
            'Free Solo' => 'https://upload.wikimedia.org/wikipedia/en/9/9c/Free_Solo.png',
            'My Octopus Teacher' => 'https://upload.wikimedia.org/wikipedia/en/0/06/My_Octopus_Teacher_poster.jpg',
            'Tartuffe' => 'https://upload.wikimedia.org/wikipedia/commons/d/d2/Tartuffe.jpg',
            'Othello' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4b/Othello_et_Desd%C3%A9mone_%C3%A0_Venise_-_Th%C3%A9odore_Chass%C3%A9riau_-_Mus%C3%A9e_du_Louvre_Peintures_RF_3897.jpg/3840px-Othello_et_Desd%C3%A9mone_%C3%A0_Venise_-_Th%C3%A9odore_Chass%C3%A9riau_-_Mus%C3%A9e_du_Louvre_Peintures_RF_3897.jpg',
        ];

        return $posters[$title] ?? sprintf('https://picsum.photos/seed/%s/800/1200', rawurlencode('netflux-'.$title));
    }

    private function buildTrailerUrl(string $title): string
    {
        $trailers = [
            'Inception' => 'https://www.youtube.com/watch?v=YoHD9XEInc0',
            'The Matrix' => 'https://www.youtube.com/watch?v=m8e-FF8MsqU',
            'Interstellar' => 'https://www.youtube.com/watch?v=zSWdZVtXT7E',
            'The Dark Knight' => 'https://www.youtube.com/watch?v=EXeTwQWrcwY',
            'Dune' => 'https://www.youtube.com/watch?v=n9xhJrPXop4',
            'Mad Max: Fury Road' => 'https://www.youtube.com/watch?v=hEJnMQG9ev8',
            'Breaking Bad' => 'https://www.youtube.com/watch?v=HhesaQXLuRY',
            'Stranger Things' => 'https://www.youtube.com/watch?v=b9EkMc79ZSU',
            'Dark' => 'https://www.youtube.com/watch?v=rrwycJ08PSA',
            'Chernobyl' => 'https://www.youtube.com/watch?v=s9APLXM9Ei8',
            'The Last of Us' => 'https://www.youtube.com/watch?v=uLtkt8BonwM',
            'Severance' => 'https://www.youtube.com/watch?v=xEQP4VVuyrY',
            'Free Solo' => 'https://www.youtube.com/watch?v=urRVZ4SW7WU',
        ];

        if (isset($trailers[$title])) {
            return $trailers[$title];
        }

        return sprintf(
            'https://www.youtube.com/results?search_query=%s',
            rawurlencode($title.' official trailer')
        );
    }

    private function buildPortraitUrl(string $key, string $firstName, string $lastName): string
    {
        $fullName = trim($firstName.' '.$lastName);
        $portraitByKey = [
            'christopher_nolan' => 'https://upload.wikimedia.org/wikipedia/commons/4/49/ChrisNolanBFI150224_%2810_of_12%29_%2853532289710%29_%28cropped2%29.jpg',
            'lana_wachowski' => 'https://upload.wikimedia.org/wikipedia/commons/5/55/Lana_Wachowski-2787_%283x4_cropped%29.jpg',
            'lilly_wachowski' => 'https://upload.wikimedia.org/wikipedia/commons/5/55/Lana_Wachowski-2787_%283x4_cropped%29.jpg',
            'ben_affleck' => 'https://upload.wikimedia.org/wikipedia/commons/b/b6/Ben_Affleck_on_the_Red_Carpet%2C_SXSW_2023_%28cropped%29.jpg',
            'denis_villeneuve' => 'https://upload.wikimedia.org/wikipedia/commons/5/5a/DVilleneuveRFH121024_%2812_of_23%29_%2854061976489%29_%28cropped%29.jpg',
            'martin_scorsese' => 'https://upload.wikimedia.org/wikipedia/commons/2/20/Martin_Scorsese_Berlinale_2010.jpg',
            'vince_gilligan' => 'https://upload.wikimedia.org/wikipedia/commons/5/59/Vince_Gilligan_by_Gage_Skidmore_2.jpg',
            'cary_joji_fukunaga' => 'https://upload.wikimedia.org/wikipedia/commons/6/6e/Cary_Fukunaga_2015.jpg',
            'susanne_bier' => 'https://upload.wikimedia.org/wikipedia/commons/7/72/Susanne_Bier_Berlinale_2018.jpg',
            'ben_stiller' => 'https://upload.wikimedia.org/wikipedia/commons/6/66/Ben_Stiller_2010.jpg',
        ];

        $portraits = [
            'Javier Bardem' => 'https://upload.wikimedia.org/wikipedia/commons/7/7e/Javier_Bardem_Cannes_2018.jpg',
            'Zendaya Coleman' => 'https://upload.wikimedia.org/wikipedia/commons/2/28/Zendaya_-_2019_by_Glenn_Francis.jpg',
            'Denis Podalydès' => 'https://upload.wikimedia.org/wikipedia/commons/a/ac/Denis_Podalydes_Cannes_2018.jpg',
            'Elijah Wood' => 'https://upload.wikimedia.org/wikipedia/commons/9/95/Elijah_Wood_at_the_2025_Sundance_Film_Festival_%28cropped%292.jpg',
            'Elizabeth Chai Vasarhelyi' => 'https://upload.wikimedia.org/wikipedia/commons/1/11/Elizabeth_Chai_Vasarhelyi_at_Sundance_2015_%28cropped%29.jpg',
            'David S. Goyer' => 'https://upload.wikimedia.org/wikipedia/commons/d/dd/David_Goyer.jpg',
            'Lana Wachowski' => 'https://upload.wikimedia.org/wikipedia/commons/5/55/Lana_Wachowski-2787_%283x4_cropped%29.jpg',
            'Matt Damon' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b4/MattDamon-byPhilipRomano.jpg/3840px-MattDamon-byPhilipRomano.jpg',
            'Christopher Nolan' => 'https://upload.wikimedia.org/wikipedia/commons/4/49/ChrisNolanBFI150224_%2810_of_12%29_%2853532289710%29_%28cropped2%29.jpg',
            'Ben Affleck' => 'https://upload.wikimedia.org/wikipedia/commons/b/b6/Ben_Affleck_on_the_Red_Carpet%2C_SXSW_2023_%28cropped%29.jpg',
            'Morgan Freeman' => 'https://upload.wikimedia.org/wikipedia/commons/4/42/Morgan_Freeman_at_The_Pentagon_on_2_August_2023_-_230802-D-PM193-3363_%28cropped%29.jpg',
            'Tom Hardy' => 'https://upload.wikimedia.org/wikipedia/commons/1/14/Tom_Hardy_by_Gage_Skidmore_in_2018.jpg',
            'Ian McKellen' => 'https://upload.wikimedia.org/wikipedia/commons/1/15/SDCC13_-_Ian_McKellen.jpg',
            'Denis Villeneuve' => 'https://upload.wikimedia.org/wikipedia/commons/5/5a/DVilleneuveRFH121024_%2812_of_23%29_%2854061976489%29_%28cropped%29.jpg',
            'Kate Winslet' => 'https://upload.wikimedia.org/wikipedia/commons/5/53/KateWinslet_%28cropped%29.jpg',
            'Asif Kapadia' => 'https://upload.wikimedia.org/wikipedia/commons/f/ff/Montclair-Film-Festival_2024_Cheryl-Corman-00374_Cheryl_Corman_%2854118679732%29_%28cropped%29.jpg',
            'Christian Bale' => 'https://upload.wikimedia.org/wikipedia/commons/0/0a/Christian_Bale-7837.jpg',
            'Timothée Chalamet' => 'https://upload.wikimedia.org/wikipedia/commons/5/5c/Timoth%C3%A9e_Chalamet-63482_%28cropped%29.jpg',
            'Ava DuVernay' => 'https://upload.wikimedia.org/wikipedia/commons/1/13/Ava_DuVernay_at_the_2026_Sundance_Film_Festival_012326_%28cropped%29.jpg',
            'Jimmy Chin' => 'https://upload.wikimedia.org/wikipedia/commons/1/18/Jimmy_Chin_Spoke_at_University_of_Michigan.jpg',
            'Morgan Neville' => 'https://upload.wikimedia.org/wikipedia/commons/1/12/Morgan_Neville_Deauville_2013.jpg',
            'Bryan Fogel' => 'https://upload.wikimedia.org/wikipedia/commons/4/41/Bryan-Fogel-Wiki-Photo.jpg',
            'Ethan Coen' => 'https://upload.wikimedia.org/wikipedia/commons/b/b5/Ethan_Coen_%28Berlin_Film_Festival_2011%29.jpg',
            'Lilly Wachowski' => 'https://upload.wikimedia.org/wikipedia/commons/5/55/Lana_Wachowski-2787_%283x4_cropped%29.jpg',
        ];

        return $portraitByKey[$key] ?? $portraits[$fullName] ?? sprintf(
            'https://picsum.photos/seed/%s/800/1200',
            rawurlencode('netflux-person-'.$key)
        );
    }

    /**
     * @return list<string>
     */
    private function getDirectorKeys(): array
    {
        return [
            'christopher_nolan',
            'lana_wachowski',
            'lilly_wachowski',
            'frank_darabont',
            'peter_jackson',
            'ben_affleck',
            'denis_villeneuve',
            'george_miller',
            'martin_scorsese',
            'joel_coen',
            'ethan_coen',
            'vince_gilligan',
            'cary_joji_fukunaga',
            'the_duffer_brothers',
            'baran_bo_odar',
            'johan_renck',
            'craig_mazin',
            'charlie_brooker',
            'pascal_charrue',
            'susanne_bier',
            'ben_stiller',
            'david_s_goedman',
            'mark_fergus',
            'jimmy_chin',
            'elizabeth_chai_vasarhelyi',
            'asif_kapadia',
            'ava_duvernay',
            'bryan_fogel',
            'james_marsh',
            'david_gelb',
            'luc_jacquet',
            'louie_psihoyos',
            'morgan_neville',
            'pippa_ehrlich',
            'joshua_oppenheimer',
            'kenneth_branagh',
            'joel_coen_stage',
            'nicholas_hytner',
            'trevor_nunn',
            'thomas_jolly',
            'ivo_van_hove',
            'denis_podalydes',
            'catherine_hiegel',
            'patrice_chereau',
            'declan_donnellan',
            'roger_blin',
            'claude_regy',
        ];
    }

    /**
     * @return list<string>
     */
    private function getActorKeys(): array
    {
        return [
            'leonardo_dicaprio',
            'joseph_gordon_levitt',
            'elliot_page',
            'keanu_reeves',
            'carrie_anne_moss',
            'laurence_fishburne',
            'tim_robbins',
            'morgan_freeman',
            'bob_gunton',
            'elijah_wood',
            'ian_mckellen',
            'viggo_mortensen',
            'matthew_mcconaughey',
            'anne_hathaway',
            'jessica_chastain',
            'christian_bale',
            'heath_ledger',
            'gary_oldman',
            'brad_pitt',
            'gwyneth_paltrow',
            'ben_affleck',
            'bryan_cranston',
            'alan_arkin',
            'timothee_chalamet',
            'zendaya',
            'rebecca_ferguson',
            'tom_hardy',
            'charlize_theron',
            'nicholas_hoult',
            'matt_damon',
            'jack_nicholson',
            'josh_brolin',
            'javier_bardem',
            'tommy_lee_jones',
            'kenneth_branagh',
            'kate_winslet',
            'derek_jacobi',
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function getPeopleData(): array
    {
        return [
            'christopher_nolan' => ['firstName' => 'Christopher', 'lastName' => 'Nolan', 'gender' => 'male', 'birthday' => '1970-07-30'],
            'lana_wachowski' => ['firstName' => 'Lana', 'lastName' => 'Wachowski', 'gender' => 'female', 'birthday' => '1965-06-21'],
            'lilly_wachowski' => ['firstName' => 'Lilly', 'lastName' => 'Wachowski', 'gender' => 'female', 'birthday' => '1967-12-29'],
            'frank_darabont' => ['firstName' => 'Frank', 'lastName' => 'Darabont', 'gender' => 'male', 'birthday' => '1959-01-28'],
            'peter_jackson' => ['firstName' => 'Peter', 'lastName' => 'Jackson', 'gender' => 'male', 'birthday' => '1961-10-31'],
            'ben_affleck' => ['firstName' => 'Ben', 'lastName' => 'Affleck', 'gender' => 'male', 'birthday' => '1972-08-15'],
            'denis_villeneuve' => ['firstName' => 'Denis', 'lastName' => 'Villeneuve', 'gender' => 'male', 'birthday' => '1967-10-03'],
            'george_miller' => ['firstName' => 'George', 'lastName' => 'Miller', 'gender' => 'male', 'birthday' => '1945-03-03'],
            'martin_scorsese' => ['firstName' => 'Martin', 'lastName' => 'Scorsese', 'gender' => 'male', 'birthday' => '1942-11-17'],
            'joel_coen' => ['firstName' => 'Joel', 'lastName' => 'Coen', 'gender' => 'male', 'birthday' => '1954-11-29'],
            'ethan_coen' => ['firstName' => 'Ethan', 'lastName' => 'Coen', 'gender' => 'male', 'birthday' => '1957-09-21'],
            'vince_gilligan' => ['firstName' => 'Vince', 'lastName' => 'Gilligan', 'gender' => 'male', 'birthday' => '1967-02-10'],
            'cary_joji_fukunaga' => ['firstName' => 'Cary', 'lastName' => 'Fukunaga', 'gender' => 'male', 'birthday' => '1977-07-10'],
            'the_duffer_brothers' => ['firstName' => 'Matt & Ross', 'lastName' => 'Duffer', 'gender' => 'male', 'birthday' => '1984-02-15'],
            'baran_bo_odar' => ['firstName' => 'Baran', 'lastName' => 'bo Odar', 'gender' => 'male', 'birthday' => '1978-04-18'],
            'johan_renck' => ['firstName' => 'Johan', 'lastName' => 'Renck', 'gender' => 'male', 'birthday' => '1966-12-05'],
            'craig_mazin' => ['firstName' => 'Craig', 'lastName' => 'Mazin', 'gender' => 'male', 'birthday' => '1971-04-08'],
            'charlie_brooker' => ['firstName' => 'Charlie', 'lastName' => 'Brooker', 'gender' => 'male', 'birthday' => '1971-03-03'],
            'pascal_charrue' => ['firstName' => 'Pascal', 'lastName' => 'Charrue', 'gender' => 'male', 'birthday' => '1987-01-01'],
            'susanne_bier' => ['firstName' => 'Susanne', 'lastName' => 'Bier', 'gender' => 'female', 'birthday' => '1960-04-15'],
            'ben_stiller' => ['firstName' => 'Ben', 'lastName' => 'Stiller', 'gender' => 'male', 'birthday' => '1965-11-30'],
            'david_s_goedman' => ['firstName' => 'David', 'lastName' => 'S. Goyer', 'gender' => 'male', 'birthday' => '1965-12-22'],
            'mark_fergus' => ['firstName' => 'Mark', 'lastName' => 'Fergus', 'gender' => 'male', 'birthday' => '1966-02-28'],
            'jimmy_chin' => ['firstName' => 'Jimmy', 'lastName' => 'Chin', 'gender' => 'male', 'birthday' => '1973-10-12'],
            'elizabeth_chai_vasarhelyi' => ['firstName' => 'Elizabeth Chai', 'lastName' => 'Vasarhelyi', 'gender' => 'female', 'birthday' => '1978-01-01'],
            'asif_kapadia' => ['firstName' => 'Asif', 'lastName' => 'Kapadia', 'gender' => 'male', 'birthday' => '1972-01-01'],
            'ava_duvernay' => ['firstName' => 'Ava', 'lastName' => 'DuVernay', 'gender' => 'female', 'birthday' => '1972-08-24'],
            'bryan_fogel' => ['firstName' => 'Bryan', 'lastName' => 'Fogel', 'gender' => 'male', 'birthday' => '1973-01-01'],
            'james_marsh' => ['firstName' => 'James', 'lastName' => 'Marsh', 'gender' => 'male', 'birthday' => '1963-04-30'],
            'david_gelb' => ['firstName' => 'David', 'lastName' => 'Gelb', 'gender' => 'male', 'birthday' => '1983-09-16'],
            'luc_jacquet' => ['firstName' => 'Luc', 'lastName' => 'Jacquet', 'gender' => 'male', 'birthday' => '1967-12-05'],
            'louie_psihoyos' => ['firstName' => 'Louie', 'lastName' => 'Psihoyos', 'gender' => 'male', 'birthday' => '1957-02-14'],
            'morgan_neville' => ['firstName' => 'Morgan', 'lastName' => 'Neville', 'gender' => 'male', 'birthday' => '1967-10-10'],
            'pippa_ehrlich' => ['firstName' => 'Pippa', 'lastName' => 'Ehrlich', 'gender' => 'female', 'birthday' => '1977-01-01'],
            'joshua_oppenheimer' => ['firstName' => 'Joshua', 'lastName' => 'Oppenheimer', 'gender' => 'male', 'birthday' => '1974-09-23'],
            'kenneth_branagh' => ['firstName' => 'Kenneth', 'lastName' => 'Branagh', 'gender' => 'male', 'birthday' => '1960-12-10'],
            'joel_coen_stage' => ['firstName' => 'Joel', 'lastName' => 'Coen', 'gender' => 'male', 'birthday' => '1954-11-29'],
            'nicholas_hytner' => ['firstName' => 'Nicholas', 'lastName' => 'Hytner', 'gender' => 'male', 'birthday' => '1956-05-07'],
            'trevor_nunn' => ['firstName' => 'Trevor', 'lastName' => 'Nunn', 'gender' => 'male', 'birthday' => '1940-01-14'],
            'thomas_jolly' => ['firstName' => 'Thomas', 'lastName' => 'Jolly', 'gender' => 'male', 'birthday' => '1982-02-01'],
            'ivo_van_hove' => ['firstName' => 'Ivo', 'lastName' => 'van Hove', 'gender' => 'male', 'birthday' => '1958-01-01'],
            'denis_podalydes' => ['firstName' => 'Denis', 'lastName' => 'Podalydès', 'gender' => 'male', 'birthday' => '1963-04-22'],
            'catherine_hiegel' => ['firstName' => 'Catherine', 'lastName' => 'Hiegel', 'gender' => 'female', 'birthday' => '1946-12-10'],
            'patrice_chereau' => ['firstName' => 'Patrice', 'lastName' => 'Chéreau', 'gender' => 'male', 'birthday' => '1944-11-02'],
            'declan_donnellan' => ['firstName' => 'Declan', 'lastName' => 'Donnellan', 'gender' => 'male', 'birthday' => '1953-08-04'],
            'roger_blin' => ['firstName' => 'Roger', 'lastName' => 'Blin', 'gender' => 'male', 'birthday' => '1907-03-22'],
            'claude_regy' => ['firstName' => 'Claude', 'lastName' => 'Régy', 'gender' => 'male', 'birthday' => '1923-05-01'],
            'leonardo_dicaprio' => ['firstName' => 'Leonardo', 'lastName' => 'DiCaprio', 'gender' => 'male', 'birthday' => '1974-11-11'],
            'joseph_gordon_levitt' => ['firstName' => 'Joseph', 'lastName' => 'Gordon-Levitt', 'gender' => 'male', 'birthday' => '1981-02-17'],
            'elliot_page' => ['firstName' => 'Elliot', 'lastName' => 'Page', 'gender' => 'non-binary', 'birthday' => '1987-02-21'],
            'keanu_reeves' => ['firstName' => 'Keanu', 'lastName' => 'Reeves', 'gender' => 'male', 'birthday' => '1964-09-02'],
            'carrie_anne_moss' => ['firstName' => 'Carrie-Anne', 'lastName' => 'Moss', 'gender' => 'female', 'birthday' => '1967-08-21'],
            'laurence_fishburne' => ['firstName' => 'Laurence', 'lastName' => 'Fishburne', 'gender' => 'male', 'birthday' => '1961-07-30'],
            'tim_robbins' => ['firstName' => 'Tim', 'lastName' => 'Robbins', 'gender' => 'male', 'birthday' => '1958-10-16'],
            'morgan_freeman' => ['firstName' => 'Morgan', 'lastName' => 'Freeman', 'gender' => 'male', 'birthday' => '1937-06-01'],
            'bob_gunton' => ['firstName' => 'Bob', 'lastName' => 'Gunton', 'gender' => 'male', 'birthday' => '1945-11-15'],
            'elijah_wood' => ['firstName' => 'Elijah', 'lastName' => 'Wood', 'gender' => 'male', 'birthday' => '1981-01-28'],
            'ian_mckellen' => ['firstName' => 'Ian', 'lastName' => 'McKellen', 'gender' => 'male', 'birthday' => '1939-05-25'],
            'viggo_mortensen' => ['firstName' => 'Viggo', 'lastName' => 'Mortensen', 'gender' => 'male', 'birthday' => '1958-10-20'],
            'matthew_mcconaughey' => ['firstName' => 'Matthew', 'lastName' => 'McConaughey', 'gender' => 'male', 'birthday' => '1969-11-04'],
            'anne_hathaway' => ['firstName' => 'Anne', 'lastName' => 'Hathaway', 'gender' => 'female', 'birthday' => '1982-11-12'],
            'jessica_chastain' => ['firstName' => 'Jessica', 'lastName' => 'Chastain', 'gender' => 'female', 'birthday' => '1977-03-24'],
            'christian_bale' => ['firstName' => 'Christian', 'lastName' => 'Bale', 'gender' => 'male', 'birthday' => '1974-01-30'],
            'heath_ledger' => ['firstName' => 'Heath', 'lastName' => 'Ledger', 'gender' => 'male', 'birthday' => '1979-04-04'],
            'gary_oldman' => ['firstName' => 'Gary', 'lastName' => 'Oldman', 'gender' => 'male', 'birthday' => '1958-03-21'],
            'brad_pitt' => ['firstName' => 'Brad', 'lastName' => 'Pitt', 'gender' => 'male', 'birthday' => '1963-12-18'],
            'gwyneth_paltrow' => ['firstName' => 'Gwyneth', 'lastName' => 'Paltrow', 'gender' => 'female', 'birthday' => '1972-09-27'],
            'bryan_cranston' => ['firstName' => 'Bryan', 'lastName' => 'Cranston', 'gender' => 'male', 'birthday' => '1956-03-07'],
            'alan_arkin' => ['firstName' => 'Alan', 'lastName' => 'Arkin', 'gender' => 'male', 'birthday' => '1934-03-26'],
            'timothee_chalamet' => ['firstName' => 'Timothée', 'lastName' => 'Chalamet', 'gender' => 'male', 'birthday' => '1995-12-27'],
            'zendaya' => ['firstName' => 'Zendaya', 'lastName' => 'Coleman', 'gender' => 'female', 'birthday' => '1996-09-01'],
            'rebecca_ferguson' => ['firstName' => 'Rebecca', 'lastName' => 'Ferguson', 'gender' => 'female', 'birthday' => '1983-10-19'],
            'tom_hardy' => ['firstName' => 'Tom', 'lastName' => 'Hardy', 'gender' => 'male', 'birthday' => '1977-09-15'],
            'charlize_theron' => ['firstName' => 'Charlize', 'lastName' => 'Theron', 'gender' => 'female', 'birthday' => '1975-08-07'],
            'nicholas_hoult' => ['firstName' => 'Nicholas', 'lastName' => 'Hoult', 'gender' => 'male', 'birthday' => '1989-12-07'],
            'matt_damon' => ['firstName' => 'Matt', 'lastName' => 'Damon', 'gender' => 'male', 'birthday' => '1970-10-08'],
            'jack_nicholson' => ['firstName' => 'Jack', 'lastName' => 'Nicholson', 'gender' => 'male', 'birthday' => '1937-04-22'],
            'josh_brolin' => ['firstName' => 'Josh', 'lastName' => 'Brolin', 'gender' => 'male', 'birthday' => '1968-02-12'],
            'javier_bardem' => ['firstName' => 'Javier', 'lastName' => 'Bardem', 'gender' => 'male', 'birthday' => '1969-03-01'],
            'tommy_lee_jones' => ['firstName' => 'Tommy Lee', 'lastName' => 'Jones', 'gender' => 'male', 'birthday' => '1946-09-15'],
            'kate_winslet' => ['firstName' => 'Kate', 'lastName' => 'Winslet', 'gender' => 'female', 'birthday' => '1975-10-05'],
            'derek_jacobi' => ['firstName' => 'Derek', 'lastName' => 'Jacobi', 'gender' => 'male', 'birthday' => '1938-10-22'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getWorks(): array
    {
        return [
            ['title' => 'Inception', 'type' => ContentType::MOVIE, 'releasedAt' => '2010-07-16', 'duration' => 148, 'synopsis' => "Dom Cobb accepte une mission d'inception qui consiste à implanter une idée dans l'esprit d'une cible.", 'rate' => 5, 'genre' => 'Science-fiction', 'directors' => ['christopher_nolan'], 'cast' => [['actor' => 'leonardo_dicaprio', 'first' => 'Dom', 'last' => 'Cobb'], ['actor' => 'joseph_gordon_levitt', 'first' => 'Arthur'], ['actor' => 'elliot_page', 'first' => 'Ariadne']]],
            ['title' => 'The Matrix', 'type' => ContentType::MOVIE, 'releasedAt' => '1999-03-31', 'duration' => 136, 'synopsis' => "Neo découvre que la réalité n'est qu'une simulation et rejoint la résistance humaine.", 'rate' => 5, 'genre' => 'Science-fiction', 'directors' => ['lana_wachowski', 'lilly_wachowski'], 'cast' => [['actor' => 'keanu_reeves', 'first' => 'Neo'], ['actor' => 'carrie_anne_moss', 'first' => 'Trinity'], ['actor' => 'laurence_fishburne', 'first' => 'Morpheus']]],
            ['title' => 'The Shawshank Redemption', 'type' => ContentType::MOVIE, 'releasedAt' => '1994-09-23', 'duration' => 142, 'synopsis' => "Andy Dufresne garde espoir pendant sa longue peine et se lie d'amitié avec Red.", 'rate' => 5, 'genre' => 'Drame', 'directors' => ['frank_darabont'], 'cast' => [['actor' => 'tim_robbins', 'first' => 'Andy', 'last' => 'Dufresne'], ['actor' => 'morgan_freeman', 'first' => 'Red'], ['actor' => 'bob_gunton', 'first' => 'Warden', 'last' => 'Norton']]],
            ['title' => 'The Lord of the Rings: The Fellowship of the Ring', 'type' => ContentType::MOVIE, 'releasedAt' => '2001-12-19', 'duration' => 178, 'synopsis' => "Frodon quitte la Comté avec la Communauté pour détruire l'Anneau Unique.", 'rate' => 5, 'genre' => 'Aventure', 'directors' => ['peter_jackson'], 'cast' => [['actor' => 'elijah_wood', 'first' => 'Frodo', 'last' => 'Baggins'], ['actor' => 'ian_mckellen', 'first' => 'Gandalf'], ['actor' => 'viggo_mortensen', 'first' => 'Aragorn']]],
            ['title' => 'Interstellar', 'type' => ContentType::MOVIE, 'releasedAt' => '2014-11-05', 'duration' => 169, 'synopsis' => "Une équipe d'explorateurs franchit un trou de ver pour trouver un nouveau foyer à l'humanité.", 'rate' => 5, 'genre' => 'Science-fiction', 'directors' => ['christopher_nolan'], 'cast' => [['actor' => 'matthew_mcconaughey', 'first' => 'Cooper'], ['actor' => 'anne_hathaway', 'first' => 'Brand'], ['actor' => 'jessica_chastain', 'first' => 'Murph']]],
            ['title' => 'The Dark Knight', 'type' => ContentType::MOVIE, 'releasedAt' => '2008-07-18', 'duration' => 152, 'synopsis' => "Batman affronte le Joker, un criminel qui plonge Gotham dans le chaos.", 'rate' => 5, 'genre' => 'Action', 'directors' => ['christopher_nolan'], 'cast' => [['actor' => 'christian_bale', 'first' => 'Bruce', 'last' => 'Wayne'], ['actor' => 'heath_ledger', 'first' => 'Joker'], ['actor' => 'gary_oldman', 'first' => 'Jim', 'last' => 'Gordon']]],
            ['title' => 'Se7en', 'type' => ContentType::MOVIE, 'releasedAt' => '1995-09-22', 'duration' => 127, 'synopsis' => "Deux inspecteurs traquent un tueur qui met en scène les sept péchés capitaux.", 'rate' => 5, 'genre' => 'Thriller', 'directors' => ['frank_darabont'], 'cast' => [['actor' => 'brad_pitt', 'first' => 'David', 'last' => 'Mills'], ['actor' => 'morgan_freeman', 'first' => 'William', 'last' => 'Somerset'], ['actor' => 'gwyneth_paltrow', 'first' => 'Tracy', 'last' => 'Mills']]],
            ['title' => 'Argo', 'type' => ContentType::MOVIE, 'releasedAt' => '2012-10-12', 'duration' => 120, 'synopsis' => "Un agent de la CIA monte un faux tournage pour exfiltrer des diplomates américains d'Iran.", 'rate' => 4, 'genre' => 'Thriller', 'directors' => ['ben_affleck'], 'cast' => [['actor' => 'ben_affleck', 'first' => 'Tony', 'last' => 'Mendez'], ['actor' => 'bryan_cranston', 'first' => 'Jack', 'last' => 'O’Donnell'], ['actor' => 'alan_arkin', 'first' => 'Lester', 'last' => 'Siegel']]],
            ['title' => 'Dune', 'type' => ContentType::MOVIE, 'releasedAt' => '2021-09-15', 'duration' => 155, 'synopsis' => "Paul Atreides rejoint Arrakis, planète désertique au cœur d'un conflit galactique.", 'rate' => 4, 'genre' => 'Science-fiction', 'directors' => ['denis_villeneuve'], 'cast' => [['actor' => 'timothee_chalamet', 'first' => 'Paul', 'last' => 'Atreides'], ['actor' => 'zendaya', 'first' => 'Chani'], ['actor' => 'rebecca_ferguson', 'first' => 'Lady', 'last' => 'Jessica']]],
            ['title' => 'Mad Max: Fury Road', 'type' => ContentType::MOVIE, 'releasedAt' => '2015-05-15', 'duration' => 120, 'synopsis' => "Max et Furiosa tentent d'échapper à un tyran dans un désert post-apocalyptique.", 'rate' => 4, 'genre' => 'Action', 'directors' => ['george_miller'], 'cast' => [['actor' => 'tom_hardy', 'first' => 'Max', 'last' => 'Rockatansky'], ['actor' => 'charlize_theron', 'first' => 'Imperator', 'last' => 'Furiosa'], ['actor' => 'nicholas_hoult', 'first' => 'Nux']]],
            ['title' => 'The Departed', 'type' => ContentType::MOVIE, 'releasedAt' => '2006-10-06', 'duration' => 151, 'synopsis' => "Un policier infiltré et une taupe de la mafia tentent de préserver leur couverture à Boston.", 'rate' => 5, 'genre' => 'Crime', 'directors' => ['martin_scorsese'], 'cast' => [['actor' => 'leonardo_dicaprio', 'first' => 'Billy', 'last' => 'Costigan'], ['actor' => 'matt_damon', 'first' => 'Colin', 'last' => 'Sullivan'], ['actor' => 'jack_nicholson', 'first' => 'Frank', 'last' => 'Costello']]],
            ['title' => 'No Country for Old Men', 'type' => ContentType::MOVIE, 'releasedAt' => '2007-11-21', 'duration' => 122, 'synopsis' => "Après avoir trouvé une mallette de billets, Llewelyn Moss devient la cible d'un tueur implacable.", 'rate' => 5, 'genre' => 'Crime', 'directors' => ['joel_coen', 'ethan_coen'], 'cast' => [['actor' => 'josh_brolin', 'first' => 'Llewelyn', 'last' => 'Moss'], ['actor' => 'javier_bardem', 'first' => 'Anton', 'last' => 'Chigurh'], ['actor' => 'tommy_lee_jones', 'first' => 'Ed', 'last' => 'Bell']]],

            ['title' => 'Breaking Bad', 'type' => ContentType::SERIES, 'releasedAt' => '2008-01-20', 'duration' => 47, 'synopsis' => "Un professeur de chimie devenu trafiquant de méthamphétamine bascule dans le crime organisé.", 'rate' => 5, 'genre' => 'Crime', 'directors' => ['vince_gilligan']],
            ['title' => 'True Detective', 'type' => ContentType::SERIES, 'releasedAt' => '2014-01-12', 'duration' => 55, 'synopsis' => "Deux inspecteurs rouvrent une affaire ancienne qui les hante depuis des années.", 'rate' => 5, 'genre' => 'Crime', 'directors' => ['cary_joji_fukunaga']],
            ['title' => 'Stranger Things', 'type' => ContentType::SERIES, 'releasedAt' => '2016-07-15', 'duration' => 50, 'synopsis' => "Dans une petite ville, des enfants affrontent des créatures venues d'une autre dimension.", 'rate' => 4, 'genre' => 'Science-fiction', 'directors' => ['the_duffer_brothers']],
            ['title' => 'Dark', 'type' => ContentType::SERIES, 'releasedAt' => '2017-12-01', 'duration' => 55, 'synopsis' => "La disparition d'un enfant révèle une boucle temporelle entre plusieurs générations.", 'rate' => 5, 'genre' => 'Science-fiction', 'directors' => ['baran_bo_odar']],
            ['title' => 'Chernobyl', 'type' => ContentType::SERIES, 'releasedAt' => '2019-05-06', 'duration' => 60, 'synopsis' => "La mini-série retrace la catastrophe nucléaire de 1986 et ses conséquences humaines.", 'rate' => 5, 'genre' => 'Drame', 'directors' => ['johan_renck']],
            ['title' => 'The Last of Us', 'type' => ContentType::SERIES, 'releasedAt' => '2023-01-15', 'duration' => 60, 'synopsis' => "Joel et Ellie traversent une Amérique dévastée par une pandémie fongique.", 'rate' => 5, 'genre' => 'Science-fiction', 'directors' => ['craig_mazin']],
            ['title' => 'Black Mirror', 'type' => ContentType::SERIES, 'releasedAt' => '2011-12-04', 'duration' => 60, 'synopsis' => "Chaque épisode imagine une dérive technologique proche de notre réalité.", 'rate' => 4, 'genre' => 'Science-fiction', 'directors' => ['charlie_brooker']],
            ['title' => 'Arcane', 'type' => ContentType::SERIES, 'releasedAt' => '2021-11-06', 'duration' => 42, 'synopsis' => "Deux sœurs se retrouvent dans une guerre opposant magie, science et lutte des classes.", 'rate' => 5, 'genre' => 'Science-fiction', 'directors' => ['pascal_charrue']],
            ['title' => 'Band of Brothers', 'type' => ContentType::SERIES, 'releasedAt' => '2001-09-09', 'duration' => 60, 'synopsis' => "La Easy Company traverse l'Europe pendant la Seconde Guerre mondiale.", 'rate' => 5, 'genre' => 'Action', 'directors' => ['susanne_bier']],
            ['title' => 'Severance', 'type' => ContentType::SERIES, 'releasedAt' => '2022-02-18', 'duration' => 50, 'synopsis' => "Des employés séparent chirurgicalement leur vie personnelle de leur vie professionnelle.", 'rate' => 5, 'genre' => 'Science-fiction', 'directors' => ['ben_stiller']],
            ['title' => 'Foundation', 'type' => ContentType::SERIES, 'releasedAt' => '2021-09-24', 'duration' => 55, 'synopsis' => "Des scientifiques tentent de préserver le savoir face à l'effondrement d'un empire galactique.", 'rate' => 4, 'genre' => 'Science-fiction', 'directors' => ['david_s_goedman']],
            ['title' => 'The Expanse', 'type' => ContentType::SERIES, 'releasedAt' => '2015-12-14', 'duration' => 45, 'synopsis' => "L'équilibre entre Terre, Mars et la Ceinture est menacé par une conspiration interplanétaire.", 'rate' => 5, 'genre' => 'Science-fiction', 'directors' => ['mark_fergus']],

            ['title' => 'Free Solo', 'type' => ContentType::DOCUMENTARY, 'releasedAt' => '2018-08-31', 'duration' => 100, 'synopsis' => "Le documentaire suit Alex Honnold dans sa tentative d'ascension sans corde d'El Capitan.", 'rate' => 5, 'genre' => 'Aventure', 'directors' => ['jimmy_chin', 'elizabeth_chai_vasarhelyi']],
            ['title' => 'Amy', 'type' => ContentType::DOCUMENTARY, 'releasedAt' => '2015-07-03', 'duration' => 128, 'synopsis' => "Portrait intime d'Amy Winehouse à partir d'archives et d'enregistrements personnels.", 'rate' => 5, 'genre' => 'Drame', 'directors' => ['asif_kapadia']],
            ['title' => '13th', 'type' => ContentType::DOCUMENTARY, 'releasedAt' => '2016-10-07', 'duration' => 100, 'synopsis' => "Ava DuVernay analyse le système carcéral américain et son héritage historique.", 'rate' => 5, 'genre' => 'Crime', 'directors' => ['ava_duvernay']],
            ['title' => 'Icarus', 'type' => ContentType::DOCUMENTARY, 'releasedAt' => '2017-08-04', 'duration' => 121, 'synopsis' => "Une enquête sur le dopage sportif révèle un vaste scandale d'État en Russie.", 'rate' => 4, 'genre' => 'Thriller', 'directors' => ['bryan_fogel']],
            ['title' => 'Man on Wire', 'type' => ContentType::DOCUMENTARY, 'releasedAt' => '2008-08-01', 'duration' => 94, 'synopsis' => "Le film revient sur la traversée illégale des tours du World Trade Center par Philippe Petit.", 'rate' => 5, 'genre' => 'Aventure', 'directors' => ['james_marsh']],
            ['title' => 'Jiro Dreams of Sushi', 'type' => ContentType::DOCUMENTARY, 'releasedAt' => '2011-11-08', 'duration' => 81, 'synopsis' => "Le maître sushi Jiro Ono consacre sa vie à la recherche du geste parfait.", 'rate' => 4, 'genre' => 'Drame', 'directors' => ['david_gelb']],
            ['title' => 'March of the Penguins', 'type' => ContentType::DOCUMENTARY, 'releasedAt' => '2005-01-26', 'duration' => 80, 'synopsis' => "Des manchots empereurs affrontent l'Antarctique pour assurer la survie de leur espèce.", 'rate' => 4, 'genre' => 'Aventure', 'directors' => ['luc_jacquet']],
            ['title' => 'The Cove', 'type' => ContentType::DOCUMENTARY, 'releasedAt' => '2009-07-31', 'duration' => 92, 'synopsis' => "Des militants documentent les captures de dauphins à Taiji au Japon.", 'rate' => 4, 'genre' => 'Crime', 'directors' => ['louie_psihoyos']],
            ['title' => 'Won’t You Be My Neighbor?', 'type' => ContentType::DOCUMENTARY, 'releasedAt' => '2018-06-08', 'duration' => 94, 'synopsis' => "Le documentaire retrace l'impact culturel et moral de Fred Rogers.", 'rate' => 4, 'genre' => 'Drame', 'directors' => ['morgan_neville']],
            ['title' => 'My Octopus Teacher', 'type' => ContentType::DOCUMENTARY, 'releasedAt' => '2020-09-07', 'duration' => 85, 'synopsis' => "Un cinéaste noue une relation bouleversante avec une pieuvre dans les forêts de kelp.", 'rate' => 4, 'genre' => 'Drame', 'directors' => ['pippa_ehrlich']],
            ['title' => 'The Act of Killing', 'type' => ContentType::DOCUMENTARY, 'releasedAt' => '2012-11-01', 'duration' => 159, 'synopsis' => "D'anciens bourreaux rejouent leurs crimes dans des mises en scène hallucinées.", 'rate' => 5, 'genre' => 'Crime', 'directors' => ['joshua_oppenheimer']],
            ['title' => 'Senna', 'type' => ContentType::DOCUMENTARY, 'releasedAt' => '2010-10-07', 'duration' => 106, 'synopsis' => "Archives et témoignages retracent la carrière et la personnalité d'Ayrton Senna.", 'rate' => 5, 'genre' => 'Action', 'directors' => ['asif_kapadia']],

            ['title' => 'Hamlet', 'type' => ContentType::THEATER, 'releasedAt' => '1996-12-25', 'duration' => 242, 'synopsis' => "Le prince du Danemark cherche à venger son père tout en sombrant dans le doute.", 'rate' => 5, 'genre' => 'Drame', 'directors' => ['kenneth_branagh'], 'cast' => [['actor' => 'kenneth_branagh', 'first' => 'Hamlet'], ['actor' => 'kate_winslet', 'first' => 'Ophelia'], ['actor' => 'derek_jacobi', 'first' => 'Claudius']]],
            ['title' => 'Macbeth', 'type' => ContentType::THEATER, 'releasedAt' => '2010-09-20', 'duration' => 150, 'synopsis' => "La prophétie des sorcières précipite Macbeth dans la soif de pouvoir et le crime.", 'rate' => 4, 'genre' => 'Drame', 'directors' => ['trevor_nunn']],
            ['title' => 'Othello', 'type' => ContentType::THEATER, 'releasedAt' => '2015-09-26', 'duration' => 180, 'synopsis' => "Manipulé par Iago, Othello s'abandonne à la jalousie destructrice.", 'rate' => 4, 'genre' => 'Drame', 'directors' => ['nicholas_hytner']],
            ['title' => 'King Lear', 'type' => ContentType::THEATER, 'releasedAt' => '2018-09-27', 'duration' => 170, 'synopsis' => "Lear abdique et plonge son royaume dans le chaos familial et politique.", 'rate' => 4, 'genre' => 'Drame', 'directors' => ['ivo_van_hove']],
            ['title' => 'Romeo and Juliet', 'type' => ContentType::THEATER, 'releasedAt' => '2015-10-01', 'duration' => 135, 'synopsis' => "Deux jeunes amants s'aiment envers et contre la haine qui divise leurs familles.", 'rate' => 4, 'genre' => 'Drame', 'directors' => ['thomas_jolly']],
            ['title' => 'Tartuffe', 'type' => ContentType::THEATER, 'releasedAt' => '2022-01-01', 'duration' => 135, 'synopsis' => "Molière démonte l'hypocrisie religieuse à travers l'emprise de Tartuffe sur Orgon.", 'rate' => 4, 'genre' => 'Drame', 'directors' => ['catherine_hiegel']],
            ['title' => 'The Misanthrope', 'type' => ContentType::THEATER, 'releasedAt' => '2016-01-01', 'duration' => 140, 'synopsis' => "Alceste rejette les compromis du monde social et se heurte à ses propres contradictions.", 'rate' => 4, 'genre' => 'Drame', 'directors' => ['denis_podalydes']],
            ['title' => 'Cyrano de Bergerac', 'type' => ContentType::THEATER, 'releasedAt' => '2013-01-01', 'duration' => 165, 'synopsis' => "Cyrano aime Roxane en secret et prête sa plume à un rival plus séduisant.", 'rate' => 4, 'genre' => 'Drame', 'directors' => ['patrice_chereau']],
            ['title' => 'Phèdre', 'type' => ContentType::THEATER, 'releasedAt' => '2003-01-01', 'duration' => 120, 'synopsis' => "Phèdre est consumée par une passion interdite qui la mène à la catastrophe.", 'rate' => 4, 'genre' => 'Drame', 'directors' => ['declan_donnellan']],
            ['title' => 'Antigone', 'type' => ContentType::THEATER, 'releasedAt' => '2015-01-01', 'duration' => 110, 'synopsis' => "Antigone brave l'autorité de Créon pour honorer son frère au nom de sa conscience.", 'rate' => 4, 'genre' => 'Drame', 'directors' => ['joel_coen_stage']],
            ['title' => 'Waiting for Godot', 'type' => ContentType::THEATER, 'releasedAt' => '1953-01-05', 'duration' => 120, 'synopsis' => "Deux vagabonds attendent un mystérieux Godot qui ne vient jamais.", 'rate' => 4, 'genre' => 'Drame', 'directors' => ['roger_blin']],
            ['title' => 'No Exit', 'type' => ContentType::THEATER, 'releasedAt' => '1944-05-27', 'duration' => 105, 'synopsis' => "Trois morts se découvrent condamnés à cohabiter éternellement dans une pièce close.", 'rate' => 4, 'genre' => 'Drame', 'directors' => ['claude_regy']],
        ];
    }
}
