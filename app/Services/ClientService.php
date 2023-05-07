<?php

declare(strict_types = 1);

namespace App\Services;

use App\DataObjects\ClientData;
use App\DataObjects\DataTableQueryParams;
use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ClientService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function create(ClientData $data, User $user): Client
    {
        $client = new Client();

        return $this->update($client, $data, $user);
    }

    public function getPaginatedClients(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Client::class)
            ->createQueryBuilder('c')
            ->select('c', 'j')
            ->leftJoin('c.jobs', 'j')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['name', 'city', 'state', 'country', 'createdAt']) ? $params->orderBy : 'name';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('c.name LIKE :param or c.phoneNumber LIKE :param or c.city LIKE :param')->setParameter('param', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        $query->orderBy('c.' . $orderBy, $orderDir);

        return new Paginator($query);
    }

    public function delete(int $id): void
    {
        $client = $this->entityManager->find(Client::class, $id);

        $this->entityManager->remove($client);
        $this->entityManager->flush();
    }

    public function getById(int $id): ?Client
    {
        return $this->entityManager->find(Client::class, $id);
    }

    public function update(Client $client, ClientData $data, User $user): Client
    {
        $client->setName($data->name);
        $client->setPhoneNumber($data->number);
        $client->setEmail($data->email);
        $client->setCity($data->city);
        $client->setState($data->state);
        $client->setCountry($data->country);

        $client->setUser($user);

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        return $client;
    }

    public function getAll(): array
    {
        return $this->entityManager
            ->getRepository(Client::class)
            ->createQueryBuilder('c')
            ->select('c.id', 'c.name', 'c.phoneNumber')
            ->orderBy('c.' . 'name', 'asc')
            ->getQuery()
            ->getArrayResult();
    }
}