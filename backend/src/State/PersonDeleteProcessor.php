<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Actor;
use App\Entity\Director;
use App\Entity\Person;
use App\Entity\Play;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProcessorInterface<Person, void>
 */
final class PersonDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: RemoveProcessor::class)]
        private ProcessorInterface $removeProcessor,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param Person $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Person) {
            return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
        }

        $actorProfile = $data->getActorProfile();
        $directorProfile = $data->getDirectorProfile();

        $this->cleanupActorDependencies($actorProfile);
        $this->cleanupDirectorDependencies($directorProfile);

        if ($actorProfile instanceof Actor) {
            $this->entityManager->remove($actorProfile);
        }

        if ($directorProfile instanceof Director) {
            $this->entityManager->remove($directorProfile);
        }

        return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function cleanupActorDependencies(?Actor $actor): void
    {
        if (null === $actor) {
            return;
        }

        foreach ($actor->getPlays()->toArray() as $play) {
            if (!$play instanceof Play) {
                continue;
            }

            $role = $play->getRole();

            $this->entityManager->remove($play);

            if ($role instanceof Role && 1 >= $role->getPlays()->count()) {
                $this->entityManager->remove($role);
            }
        }
    }

    private function cleanupDirectorDependencies(?Director $director): void
    {
        if (null === $director) {
            return;
        }

        foreach ($director->getFilms()->toArray() as $film) {
            $film->removeDirector($director);
        }
    }
}
