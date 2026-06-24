<?php

namespace App\Dto;

use App\Entity\FilmRating;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: FilmRating::class)]
final class UserFilmRatingOutput
{
    #[Groups(['rating:list:read'])]
    public ?int $movieId = null;

    #[Groups(['rating:list:read'])]
    public ?string $movieTitle = null;

    #[Groups(['rating:list:read'])]
    public ?int $rate = null;
}
