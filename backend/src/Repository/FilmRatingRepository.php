<?php

namespace App\Repository;

use App\Entity\Film;
use App\Entity\FilmRating;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FilmRating>
 */
class FilmRatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FilmRating::class);
    }

    public function findOneByUserAndFilm(User $user, Film $film): ?FilmRating
    {
        return $this->findOneBy([
            'user' => $user,
            'film' => $film,
        ]);
    }
}
