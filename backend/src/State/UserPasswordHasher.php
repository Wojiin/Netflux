<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @implements ProcessorInterface<User, User>
 */
final class UserPasswordHasher implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private UserPasswordHasherInterface $passwordHasher,
        private Security $security,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof User) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        $authenticatedUser = $this->security->getUser();
        $previousData = $context['previous_data'] ?? null;
        $targetUser = $data;
        $previousEmail = null;
        $nextEmail = null;

        if (
            $operation instanceof HttpOperation
            && '/me' === $operation->getUriTemplate()
            && $authenticatedUser instanceof User
        ) {
            // PATCH /me doit modifier directement l'entité authentifiée, pas une
            // copie dénormalisée détachée, sinon les changements d'email ou de mot
            // de passe se désynchronisent de l'identité de session.
            $previousEmail = $authenticatedUser->getEmail();
            $nextEmail = $data->getEmail() ?? $authenticatedUser->getEmail() ?? '';
            $authenticatedUser->setEmail($nextEmail);
            $authenticatedUser->setPlainPassword($data->getPlainPassword());
            $targetUser = $authenticatedUser;
            $previousData = $authenticatedUser;
        }

        if ($operation instanceof HttpOperation && '/register' === $operation->getUriTemplate()) {
            // Une inscription publique ne doit jamais pouvoir s'auto-attribuer
            // ROLE_ADMIN via le payload entrant.
            $targetUser->setRoles(['ROLE_USER']);
        }

        if (
            $authenticatedUser instanceof User
            && $previousData instanceof User
            && $authenticatedUser === $previousData
            && !$this->security->isGranted('ROLE_ADMIN')
        ) {
            // Un utilisateur standard peut modifier son profil, mais pas
            // s'élever en privilèges en envoyant un tableau roles personnalisé.
            $targetUser->setRoles($previousData->getRoles());
        }

        if ($targetUser->getPlainPassword()) {
            // Le mot de passe en clair n'est conserve que le temps du hash puis
            // purge de l'entite avant persistence.
            $targetUser->setPassword($this->passwordHasher->hashPassword($targetUser, $targetUser->getPlainPassword()));
            $targetUser->setPlainPassword(null);
        }

        $result = $this->persistProcessor->process($targetUser, $operation, $uriVariables, $context);

        if (
            $previousEmail
            && $nextEmail
            && $previousEmail !== $nextEmail
        ) {
            // Les refresh tokens sont reliés au username/email dans la table
            // refresh_tokens. Quand l'email change, il faut garder ces lignes
            // synchronisées pour que le refresh continue de fonctionner.
            $this->entityManager->getConnection()->executeStatement(
                'UPDATE refresh_tokens SET username = :nextEmail WHERE username = :previousEmail',
                [
                    'nextEmail' => $nextEmail,
                    'previousEmail' => $previousEmail,
                ],
            );
        }

        return $result;
    }
}
