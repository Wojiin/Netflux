<?php

namespace App\Dto;

use App\Entity\Film;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: Film::class)]
final class FilmOutput
{
    #[Groups(['movie:read'])]
    public ?string $title = null;

    #[Groups(['movie:read'])]
    public ?string $type = null;

    #[Groups(['movie:read'])]
    public ?\DateTimeImmutable $releasedAt = null;

    #[Groups(['movie:read'])]
    public ?string $imgLink = null;

    #[Groups(['movie:read'])]
    public ?int $duration = null;

    #[Groups(['movie:read'])]
    public ?string $synopsis = null;

    #[Groups(['movie:read'])]
    public ?int $rate = null;

    #[Groups(['movie:read'])]
    public ?string $directorName = null;

    /**
     * @var list<string>
     */
    #[Groups(['movie:read'])]
    public array $genreNames = [];

    /**
     * @var list<string>
     */
    #[Groups(['movie:read'])]
    public array $posterUrls = [];

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getReleasedAt(): ?\DateTimeImmutable
    {
        return $this->releasedAt;
    }

    public function getImgLink(): ?string
    {
        return $this->imgLink;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function getDirectorName(): ?string
    {
        return $this->directorName;
    }

    /**
     * @return list<string>
     */
    public function getGenreNames(): array
    {
        return $this->genreNames;
    }

    /**
     * @return list<string>
     */
    public function getPosterUrls(): array
    {
        return $this->posterUrls;
    }
}
