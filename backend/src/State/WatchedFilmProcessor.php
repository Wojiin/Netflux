<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\WatchedFilmOutput;
use App\Entity\Film;
use App\Entity\User;
use App\Repository\FilmRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class WatchedFilmProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
        private readonly FilmRepository $filmRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    // Ce processor gère la route /users/{id}/watched/{movieId}. Il ajoute un
    // film à l'historique sans dupliquer une entrée déjà présente.
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): WatchedFilmOutput
    {
        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour mettre à jour votre historique.');
        }

        $userId = (int) ($uriVariables['id'] ?? 0);

        if ($currentUser->getId() !== $userId && !$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException('Vous ne pouvez modifier que votre propre historique.');
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

        $output = new WatchedFilmOutput();

        if ($user->getWatchedFilms()->contains($film)) {
            $output->status = 'already_marked';
        } else {
            $user->addWatchedFilm($film);
            $this->entityManager->flush();
            $output->status = 'added';
        }

        $output->watchedTitles = $user->getWatchedFilms()
            ->map(static fn (Film $watchedFilm) => $watchedFilm->getTitle())
            ->toArray();

        return $output;
    }
}
