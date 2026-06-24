<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PersonProfileInput;
use App\Entity\Actor;
use App\Entity\Director;
use App\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<PersonProfileInput, Actor|Director>
 */
final class PersonProfileWriteProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof PersonProfileInput) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        $resource = $this->resolveResource($operation->getClass(), $uriVariables);
        $personId = $data->personId;

        if (!$personId) {
            throw new BadRequestHttpException('Personne introuvable.');
        }

        $person = $this->entityManager->getRepository(Person::class)->find($personId);

        if (!$person instanceof Person) {
            throw new BadRequestHttpException('Personne introuvable.');
        }

        $existingProfile = $this->entityManager
            ->getRepository($resource::class)
            ->findOneBy(['person' => $person]);

        if (
            ($existingProfile instanceof Actor || $existingProfile instanceof Director)
            && $existingProfile->getId() !== $resource->getId()
        ) {
            throw new BadRequestHttpException($resource instanceof Director
                ? 'Cette personne est déjà associée à un réalisateur.'
                : 'Cette personne est déjà associée à un acteur.');
        }

        $resource->setPerson($person);

        return $this->persistProcessor->process($resource, $operation, $uriVariables, $context);
    }

    /**
     * @param array<string, mixed> $uriVariables
     */
    private function resolveResource(?string $resourceClass, array $uriVariables): Actor|Director
    {
        if (Actor::class !== $resourceClass && Director::class !== $resourceClass) {
            throw new BadRequestHttpException('Type de profil non pris en charge.');
        }

        $resourceId = isset($uriVariables['id']) ? (int) $uriVariables['id'] : null;

        if (!$resourceId) {
            return new $resourceClass();
        }

        $resource = $this->entityManager->getRepository($resourceClass)->find($resourceId);

        if ($resource instanceof Actor || $resource instanceof Director) {
            return $resource;
        }

        throw new NotFoundHttpException('Profil introuvable.');
    }
}
