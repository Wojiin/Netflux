<?php

namespace App\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class RefreshTokenLoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::AUTHENTICATION_SUCCESS => ['onAuthenticationSuccess', 10],
        ];
    }

    // Ce hook s'exécute juste après une authentification réussie. Il cible
    // spécifiquement /api/login pour repartir d'un état cookie propre avant que
    // la réponse finale ne pose le nouveau refresh token.
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        if ('/api/login' !== $request->getPathInfo()) {
            return;
        }

        $request->cookies->remove('refresh_token');
    }
}
