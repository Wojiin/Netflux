<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\FilmRatingOutput;
use App\Entity\Film;
use App\Entity\FilmRating;
use App\Entity\User;
use App\Repository\FilmRatingRepository;
use App\Repository\FilmRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class FilmRatingProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
        private readonly FilmRepository $filmRepository,
        private readonly FilmRatingRepository $filmRatingRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    // Ce processor gère la notation d'un film par l'utilisateur courant. Il
    // crée ou met à jour la note puis recalcule le résumé agrégé du film.
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): FilmRatingOutput
    {
        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException('Vous devez être connecté pour noter un film.');
        }

        $userId = (int) ($uriVariables['id'] ?? 0);

        if ($currentUser->getId() !== $userId && !$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException('Vous ne pouvez noter que depuis votre propre compte.');
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

        $rate = (int) ($uriVariables['rate'] ?? -1);

        if ($rate < 0 || $rate > 5) {
            throw new BadRequestHttpException('La note doit être comprise entre 0 et 5.');
        }

        $filmRating = $this->filmRatingRepository->findOneByUserAndFilm($user, $film);
        $output = new FilmRatingOutput();

        if (!$filmRating instanceof FilmRating) {
            $filmRating = new FilmRating();
            $filmRating->setUser($user);
            $filmRating->setFilm($film);
            $user->addFilmRating($filmRating);
            $film->addFilmRating($filmRating);
            $this->entityManager->persist($filmRating);
            $output->status = 'created';
        } else {
            $output->status = 'updated';
        }

        $filmRating->setRate($rate);
        $this->refreshFilmRatingSummary($film);
        $this->entityManager->flush();

        $output->movieId = $film->getId();
        $output->rate = $filmRating->getRate();
        $output->averageRate = $film->getAverageRate();
        $output->ratingsCount = $film->getRatingsCount();

        return $output;
    }

    // Cette méthode privée maintient les champs dérivés averageRate et
    // ratingsCount pour éviter d'avoir à recalculer ces valeurs côté client.
    private function refreshFilmRatingSummary(Film $film): void
    {
        $ratings = $film->getFilmRatings();
        $ratingsCount = $ratings->count();

        $film->setRatingsCount($ratingsCount);

        if (0 === $ratingsCount) {
            $film->setAverageRate(null);

            return;
        }

        $total = array_reduce(
            $ratings->toArray(),
            static fn (int $sum, FilmRating $rating): int => $sum + ($rating->getRate() ?? 0),
            0,
        );

        $film->setAverageRate(round($total / $ratingsCount, 1));
    }
}
