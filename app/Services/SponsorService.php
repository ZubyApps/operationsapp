<?php

declare(strict_types = 1);

namespace App\Services;


use App\DataObjects\DataTableQueryParams;
use App\Entity\Sponsor;
use App\Enum\Flag;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class SponsorService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function create(array $data): Sponsor
    {
        $sponsor = new Sponsor();

        return $this->update($sponsor, $data);
    }

    public function getPaginatedSponsors(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Sponsor::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.expenses', 'e')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['name', 'createdAt']) ? $params->orderBy : 'createdAt';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('s.name LIKE :param')->setParameter('param', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        $query->orderBy('s.' . $orderBy, $orderDir);

        return new Paginator($query);
    }

    public function delete(int $id): void
    {
        $sponsor = $this->entityManager->find(Sponsor::class, $id);

        $this->entityManager->remove($sponsor);
        $this->entityManager->flush();
    }

    public function getById(int $id): ?Sponsor
    {
        return $this->entityManager->find(Sponsor::class, $id);
    }

    public function update(Sponsor $sponsor, array $data): Sponsor
    {
        $sponsor->setName($data['name']);
        $sponsor->setDescription($data['description']);
        $sponsor->setFlag(Flag::from((int)$data['flag']));

        $this->entityManager->persist($sponsor);
        $this->entityManager->flush();

        return $sponsor;
    }

    public function getSponsors(): array
    {
        return $this->entityManager
            ->getRepository(Sponsor::class)
            ->createQueryBuilder('s')
            ->select('s.id', 's.name')
            ->orderBy('s.' . 'name', 'asc')
            ->getQuery()
            ->getArrayResult();
    }
}
