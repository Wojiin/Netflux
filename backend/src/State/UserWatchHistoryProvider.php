<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UserWatchHistoryProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour consulter votre historique.');
        }

        $userId = (int) ($uriVariables['id'] ?? 0);

        if ($currentUser->getId() !== $userId && !$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException('Vous ne pouvez consulter que votre propre historique.');
        }

        $user = $this->userRepository->find($userId);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('Utilisateur introuvable.');
        }

        return $user->getWatchedFilms()->toArray();
    }
}
