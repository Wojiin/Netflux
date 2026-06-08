<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\Groups;

final class FavoriteToggleOutput
{
    #[Groups(['favorite:read'])]
    public ?string $status = null;

    /**
     * @var list<string>
     */
    #[Groups(['favorite:read'])]
    public array $favoriteTitles = [];

    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return list<string>
     */
    public function getFavoriteTitles(): array
    {
        return $this->favoriteTitles;
    }
}
