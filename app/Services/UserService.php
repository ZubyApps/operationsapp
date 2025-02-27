<?php

declare(strict_types = 1);

namespace App\Services;

use App\Auth;
use App\DataObjects\DataTableQueryParams;
use App\DataObjects\UpdateUserData;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class UserService
{
    public function __construct(private readonly EntityManager $entityManager, private readonly Auth $auth)
    {
    }

    public function setRole(User $user, UserRole $role): User
    {
        $user->setUserRole($role);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function getPaginatedClients(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['firstname', 'lastname', 'department', 'role', 'createdAt']) ? $params->orderBy : 'createdAt';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('u.firstname LIKE :param or u.phoneNumber LIKE :param or u.city LIKE :param')->setParameter('param', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        $query->orderBy('u.' . $orderBy, $orderDir);

        return new Paginator($query);
    }

    public function delete(int $id): void
    {
        $user = $this->entityManager->find(User::class, $id);

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function getById(int $id): ?User
    {
        return $this->entityManager->find(User::class, $id);
    }

    public function checkLogInUserRole(string $email): bool
    {
        $user =  $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if ($user->getUserRole() === UserRole::from('Unset')) {
            return false;
        } else{ 
            return true;
        }
    }

    public function update(UpdateUserData $data, User $user): User
    {
        if ($data->password === '') {
            $user->setFirstName($data->firstname);
            $user->setLastName($data->lastname);
            $user->setEmail($data->email);
            $user->setPhoneNumber($data->phonenumber);
            $user->setDepartment($data->department);
        } else {
            $user->setFirstName($data->firstname);
            $user->setLastName($data->lastname);
            $user->setEmail($data->email);
            $user->setPhoneNumber($data->phonenumber);
            $user->setDepartment($data->department);
            $user->setPassword(password_hash($data->password, PASSWORD_BCRYPT, ['cost' => 12]));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function getAll(): array
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('u.id', 'u.firstname')
            ->orderBy('u.' . 'firstname', 'asc')
            ->getQuery()
            ->getArrayResult();
    }

    public function getActiveUserRole(): UserRole
    {
        return $this->getById($this->auth->user()->getId())->getUserRole();
    }
}