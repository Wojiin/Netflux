<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\PosterOutput;
use App\Repository\PosterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(SearchFilter::class, properties: ['film.id' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'url', 'film.title'])]
#[ApiResource(
    description: 'Représente une affiche associée à un film.',
    paginationEnabled: true,
    paginationItemsPerPage: 10,
    paginationClientItemsPerPage: true,
    paginationMaximumItemsPerPage: 100,
    normalizationContext: ['groups' => ['poster:read']],
    denormalizationContext: ['groups' => ['poster:write']],
    operations: [
        new Get(
            output: PosterOutput::class,
            description: "Retourne le détail d'une affiche.",
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut consulter une affiche.'
        ),
        new GetCollection(
            output: PosterOutput::class,
            description: 'Retourne la liste des affiches.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut consulter les affiches.'
        ),
        new Post(
            description: 'Crée une nouvelle affiche dans le catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut créer une affiche.'
        ),
        new Put(
            description: 'Remplace complètement une affiche existante.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier une affiche.'
        ),
        new Patch(
            description: 'Modifie partiellement une affiche existante.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut modifier une affiche.'
        ),
        new Delete(
            description: 'Supprime une affiche du catalogue.',
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Seul un administrateur peut supprimer une affiche.'
        ),
    ]
)]
#[ORM\Entity(repositoryClass: PosterRepository::class)]
class Poster
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['poster:read', 'movie:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 1024)]
    #[ApiProperty(openapiContext: ['example' => 'https://image.tmdb.org/t/p/w500/qmDpIHrmpJINaRKAfWQfftjCdyi.jpg'])]
    #[Groups(['poster:read', 'poster:write', 'movie:read'])]
    #[Assert\NotBlank(message: "L'URL de l'affiche est obligatoire.")]
    #[Assert\Url(message: "L'URL de l'affiche doit être valide.")]
    #[Assert\Length(max: 1024, maxMessage: "L'URL de l'affiche ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'posters')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(
        description: 'Film associé à cette affiche.',
        openapiContext: ['example' => '/api/movies/1']
    )]
    #[Groups(['poster:write'])]
    private ?Film $film = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

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
}
