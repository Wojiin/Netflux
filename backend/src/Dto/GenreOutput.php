<?php

namespace App\Dto;

use App\Entity\Genre;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: Genre::class)]
final class GenreOutput
{
    #[Groups(['genre:read'])]
    public ?string $name = null;

    /**
     * @var list<string>
     */
    #[Groups(['genre:read'])]
    public array $filmTitles = [];

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return list<string>
     */
    public function getFilmTitles(): array
    {
        return $this->filmTitles;
    }
}
