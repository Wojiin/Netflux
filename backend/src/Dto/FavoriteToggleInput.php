<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class FavoriteToggleInput
{
    #[Assert\NotNull(message: 'Le champ movieId est obligatoire.')]
    #[Assert\Positive(message: 'Le champ movieId doit être un entier positif.')]
    public ?int $movieId = null;
}
