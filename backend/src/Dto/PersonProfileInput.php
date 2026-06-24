<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final class PersonProfileInput
{
    #[Groups(['actor:write', 'director:write'])]
    #[Assert\NotNull(message: 'La personne est obligatoire.')]
    #[Assert\Positive(message: 'La personne sélectionnée est invalide.')]
    public ?int $personId = null;
}
