<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApiLogoutController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    public function __invoke(): Response
    {
        // Le contrôleur est appelé par /api/token/invalidate. Il centralise le
        // nettoyage serveur de la session longue durée.
        $currentUser = $this->security->getUser();

        if ($currentUser instanceof User) {
            // Les access tokens sont des JWT stateless : au logout, on revoque donc
            // les refresh tokens persistants et on laisse l'access token courant
            // expirer naturellement.
            $this->entityManager->getConnection()->executeStatement(
                'DELETE FROM refresh_tokens WHERE username = :username',
                ['username' => $currentUser->getUserIdentifier()],
            );
        }

        $response = new JsonResponse(null, Response::HTTP_NO_CONTENT);
        $response->headers->clearCookie('refresh_token', '/', null, false, true, 'lax');

        return $response;
    }
}
