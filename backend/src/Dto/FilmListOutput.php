<?php

namespace App\Dto;

use App\Entity\Film;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: Film::class)]
final class FilmListOutput
{
    #[Groups(['movie:list:read'])]
    public ?int $id = null;

    #[Groups(['movie:list:read'])]
    public ?string $title = null;

    #[Groups(['movie:list:read'])]
    public ?string $type = null;

    #[Groups(['movie:list:read'])]
    public ?\DateTimeImmutable $releasedAt = null;

    #[Groups(['movie:list:read'])]
    public ?string $imgLink = null;

    #[Groups(['movie:list:read'])]
    public ?int $duration = null;

    #[Groups(['movie:list:read'])]
    public ?int $rate = null;

    #[Groups(['movie:list:read'])]
    public ?float $averageRate = null;

    #[Groups(['movie:list:read'])]
    public int $ratingsCount = 0;

    #[Groups(['movie:list:read'])]
    public ?string $directorName = null;

    /**
     * @var list<string>
     */
    #[Groups(['movie:list:read'])]
    public array $directorNames = [];

    /**
     * @var list<string>
     */
    #[Groups(['movie:list:read'])]
    public array $genreNames = [];
}
