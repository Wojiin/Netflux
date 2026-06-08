<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\FavoriteToggleInput;
use App\Dto\FavoriteToggleOutput;
use App\Entity\Film;
use App\Entity\User;
use App\Repository\FilmRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): FavoriteToggleOutput
    {
        if (!$data instanceof FavoriteToggleInput) {
            throw new BadRequestHttpException('Le corps de la requête est invalide.');
        }

        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour modifier vos favoris.');
        }

        $userId = (int) ($uriVariables['id'] ?? 0);

        if ($currentUser->getId() !== $userId && !$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException('Vous ne pouvez modifier que vos propres favoris.');
        }

        $user = $this->userRepository->find($userId);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('Utilisateur introuvable.');
        }

        $movieId = $data->movieId;
        $film = $movieId !== null ? $this->filmRepository->find($movieId) : null;

        if (!$film instanceof Film) {
            throw new NotFoundHttpException('Film introuvable.');
        }

        $output = new FavoriteToggleOutput();

        if ($user->getLikedFilms()->contains($film)) {
            $user->removeLikedFilm($film);
            $output->status = 'removed';
        } else {
            $user->addLikedFilm($film);
            $output->status = 'added';
        }

        $this->entityManager->flush();

        $output->favoriteTitles = $user->getLikedFilms()
            ->map(static fn (Film $favoriteFilm) => $favoriteFilm->getTitle())
            ->toArray();

        return $output;
    }
}
