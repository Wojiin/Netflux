<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
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
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof User) {
            return $data;
        }

        $plainPassword = $data->getPlainPassword();

        if ($plainPassword !== null && $plainPassword !== '') {
            $data->setPassword($this->passwordHasher->hashPassword($data, $plainPassword));
        }

        $data->setPlainPassword(null);

        if ('/register' === $operation->getUriTemplate()) {
            $data->setRoles(['ROLE_USER']);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
