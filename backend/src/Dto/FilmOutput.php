<?php

namespace App\Dto;

use App\Entity\Film;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Serializer\Attribute\Groups;

#[Map(source: Film::class)]
final class FilmOutput
{
    #[Groups(['movie:read'])]
    public ?int $id = null;

    #[Groups(['movie:read'])]
    public ?string $title = null;

    #[Groups(['movie:read'])]
    public ?string $type = null;

    #[Groups(['movie:read'])]
    public ?\DateTimeImmutable $releasedAt = null;

    #[Groups(['movie:read'])]
    public ?string $imgLink = null;

    #[Groups(['movie:read'])]
    public ?string $videoLink = null;

    #[Groups(['movie:read'])]
    public ?int $duration = null;

    #[Groups(['movie:read'])]
    public ?string $synopsis = null;

    #[Groups(['movie:read'])]
    public ?int $rate = null;

    #[Groups(['movie:read'])]
    public ?float $averageRate = null;

    #[Groups(['movie:read'])]
    public int $ratingsCount = 0;

    #[Groups(['movie:read'])]
    public ?string $directorName = null;

    /**
     * @var list<string>
     */
    #[Groups(['movie:read'])]
    public array $directorNames = [];

    /**
     * @var list<array{id:int|null, fullName:string, portraitLink:?string}>
     */
    #[Groups(['movie:read'])]
    public array $directorDetails = [];

    /**
     * @var list<string>
     */
    #[Groups(['movie:read'])]
    public array $genreNames = [];

    /**
     * @var list<array{id:int|null, actorId:int|null, actorName:?string, portraitLink:?string, roleId:int|null, roleName:?string}>
     */
    #[Groups(['movie:read'])]
    public array $cast = [];

    /**
     * @var list<string>
     */
    #[Groups(['movie:read'])]
    public array $posterUrls = [];

    public function getId(): ?int
    {
        return $this->id;
    }

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

    public function getVideoLink(): ?string
    {
        return $this->videoLink;
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

    public function getAverageRate(): ?float
    {
        return $this->averageRate;
    }

    public function getRatingsCount(): int
    {
        return $this->ratingsCount;
    }

    public function getDirectorName(): ?string
    {
        return $this->directorName;
    }

    /**
     * @return list<string>
     */
    public function getDirectorNames(): array
    {
        return $this->directorNames;
    }

    /**
     * @return list<array{id:int|null, fullName:string, portraitLink:?string}>
     */
    public function getDirectorDetails(): array
    {
        return $this->directorDetails;
    }

    /**
     * @return list<string>
     */
    public function getGenreNames(): array
    {
        return $this->genreNames;
    }

    /**
     * @return list<array{id:int|null, actorId:int|null, actorName:?string, portraitLink:?string, roleId:int|null, roleName:?string}>
     */
    public function getCast(): array
    {
        return $this->cast;
    }

    /**
     * @return list<string>
     */
    public function getPosterUrls(): array
    {
        return $this->posterUrls;
    }
}
