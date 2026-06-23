<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\FavoriteToggleOutput;
use App\Entity\Film;
use App\Entity\User;
use App\Repository\FilmRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class FavoriteToggleProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
        private readonly FilmRepository $filmRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    // Ce processor porte la logique métier de la route /users/{id}/favorites/{movieId}.
    // Il sert à la fois de garde d'identité et de couche d'écriture Doctrine.
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): FavoriteToggleOutput
    {
        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour modifier vos favoris.');
        }

        $userId = (int) ($uriVariables['id'] ?? 0);

        // Même si l'endpoint porte un id utilisateur en URL, on revalide toujours
        // l'identité côté serveur pour empêcher la modification des favoris d'autrui.
        if ($currentUser->getId() !== $userId && !$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException('Vous ne pouvez modifier que vos propres favoris.');
        }

        $user = $this->userRepository->find($userId);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('Utilisateur introuvable.');
        }

        $movieId = (int) ($uriVariables['movieId'] ?? 0);
        $film = $movieId > 0 ? $this->filmRepository->find($movieId) : null;

        if (!$film instanceof Film) {
            throw new NotFoundHttpException('Film introuvable.');
        }

        $output = new FavoriteToggleOutput();

        // Les favoris sont stockés dans une relation ManyToMany entre user et film.
        // L'API expose volontairement une route de toggle au lieu de demander au
        // frontend de renvoyer toute la collection à chaque changement.
        if ($user->getLikedFilms()->contains($film)) {
            $user->removeLikedFilm($film);
            $output->status = 'removed';
        } else {
            $user->addLikedFilm($film);
            $output->status = 'added';
        }

        $this->entityManager->flush();

        // La réponse renvoie la collection à jour pour permettre au frontend de
        // resynchroniser son état sans refaire immédiatement un GET complet.
        $output->favoriteTitles = $user->getLikedFilms()
            ->map(static fn (Film $favoriteFilm) => $favoriteFilm->getTitle())
            ->toArray();

        return $output;
    }
}
