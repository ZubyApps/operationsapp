<?php

declare(strict_types = 1);

namespace App\Services;

use App\Contracts\UserInterface;
use App\Contracts\UserProviderServiceInterface;
use App\DataObjects\RegisterUserData;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class UserProviderService implements UserProviderServiceInterface
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function getById(int $userId): ?UserInterface
    {
        return $this->entityManager->find(User::class, $userId);
    }

    public function getByCredentials(array $credentials): ?UserInterface
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
    }

    public function createUser(RegisterUserData $data): UserInterface
    {
        $user = new User();

        $user->setfirstName($data->firstname);
        $user->setlastName($data->lastname);
        $user->setEmail($data->email);
        $user->setPhoneNumber($data->phonenumber);
        $user->setPassword(password_hash($data->password, PASSWORD_BCRYPT, ['cost' => 12]));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function getUsers(): array
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('u.id', 'u.firstname', 'u.lastname')
            ->orderBy('u.' . 'firstname', 'asc')
            ->getQuery()
            ->getArrayResult();
    }
}
