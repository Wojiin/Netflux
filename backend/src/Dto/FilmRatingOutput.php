<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\Groups;

final class FilmRatingOutput
{
    #[Groups(['rating:read'])]
    public ?string $status = null;

    #[Groups(['rating:read'])]
    public ?int $movieId = null;

    #[Groups(['rating:read'])]
    public ?int $rate = null;

    #[Groups(['rating:read'])]
    public ?float $averageRate = null;

    #[Groups(['rating:read'])]
    public int $ratingsCount = 0;
}
