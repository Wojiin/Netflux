<?php

namespace App\Dto;

use App\Entity\Poster;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: Poster::class)]
final class PosterOutput
{
    #[Groups(['poster:read'])]
    public ?string $url = null;

    #[Map(source: 'film.title')]
    #[Groups(['poster:read'])]
    public ?string $filmTitle = null;

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getFilmTitle(): ?string
    {
        return $this->filmTitle;
    }
}
