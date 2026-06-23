<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @implements ProviderInterface<User>
 */
final class CurrentUserProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    // Ce provider alimente la route /me : au lieu de relire un id dans l'URL,
    // il retourne directement l'utilisateur authentifié par Symfony Security.
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): User
    {
        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour accéder à votre profil.');
        }

        return $currentUser;
    }
}
