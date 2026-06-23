<?php

namespace App\Entity;

use App\Repository\FilmRatingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FilmRatingRepository::class)]
#[ORM\Table(name: 'film_rating')]
#[ORM\UniqueConstraint(name: 'uniq_user_film_rating', columns: ['user_id', 'film_id'])]
class FilmRating
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'filmRatings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'filmRatings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Map(target: 'movieId', transform: [self::class, 'toMovieId'])]
    #[Map(target: 'movieTitle', transform: [self::class, 'toMovieTitle'])]
    private ?Film $film = null;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 5, notInRangeMessage: 'La note doit etre comprise entre {{ min }} et {{ max }}.')]
    private ?int $rate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getFilm(): ?Film
    {
        return $this->film;
    }

    public function setFilm(?Film $film): static
    {
        $this->film = $film;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public static function toMovieId(?Film $film): ?int
    {
        return $film?->getId();
    }

    public static function toMovieTitle(?Film $film): ?string
    {
        return $film?->getTitle();
    }
}
