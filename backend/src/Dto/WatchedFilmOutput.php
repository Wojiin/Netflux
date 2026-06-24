<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\Groups;

final class WatchedFilmOutput
{
    #[Groups(['watched:read'])]
    public ?string $status = null;

    /**
     * @var list<string>
     */
    #[Groups(['watched:read'])]
    public array $watchedTitles = [];

    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return list<string>
     */
    public function getWatchedTitles(): array
    {
        return $this->watchedTitles;
    }
}
