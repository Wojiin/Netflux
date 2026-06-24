<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Actor;
use App\Entity\Film;
use App\Entity\Play;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @implements ProcessorInterface<Play, Play>
 */
final class PlayWriteProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Play) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        $film = $this->entityManager->getRepository(Film::class)->find($data->getFilmId());
        $actor = $this->entityManager->getRepository(Actor::class)->find($data->getActorId());
        $role = $this->entityManager->getRepository(Role::class)->find($data->getRoleId());

        if (!$film) {
            throw new BadRequestHttpException('Film introuvable.');
        }

        if (!$actor) {
            throw new BadRequestHttpException('Acteur introuvable.');
        }

        if (!$role) {
            throw new BadRequestHttpException('Rôle introuvable.');
        }

        $data->setFilm($film);
        $data->setActor($actor);
        $data->setRole($role);

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
