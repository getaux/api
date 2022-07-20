<?php

namespace App\Repository;

use App\Entity\Auction;
use App\Entity\Collection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Collection>
 *
 * @method Collection|null find($id, $lockMode = null, $lockVersion = null)
 * @method Collection|null findOneBy(array $criteria, array $orderBy = null)
 * @method Collection[]    findAll()
 * @method Collection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CollectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collection::class);
    }

    public function add(Collection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Collection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function customCount(): mixed
    {
        $qb = $this->createQueryBuilder('c')
            ->select('count (c.id) as totalResults');

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function customFindAll(array $order, int $limit, ?int $offset): array
    {
        $qb = $this->createQueryBuilder('c');

        if (count($order) > 0) {
            $qb->orderBy('c.' . key($order), $order[key($order)]);
        }

        $qb->setMaxResults($limit);

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return (array)$qb->getQuery()->getResult();
    }

    public function findAuctionsByCollection(): array
    {
        $qb = $this->createQueryBuilder('c', 'c.address')
            ->select('c.address, COUNT(auctions.id) as totalAuctions')
            ->join('c.assets', 'assets')
            ->join('assets.auctions', 'auctions')
            ->andWhere('auctions.status = :status')
            ->setParameter('status', Auction::STATUS_ACTIVE)
            ->groupBy('c.id');

        return (array)$qb->getQuery()->getArrayResult();
    }

    public function findAuctionsForOneCollection(Collection $collection): mixed
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(auctions.id) as totalAuctions')
            ->join('c.assets', 'assets')
            ->join('assets.auctions', 'auctions')
            ->andWhere('auctions.status = :status')
            ->setParameter('status', Auction::STATUS_ACTIVE)
            ->andWhere('c.id = :id')
            ->setParameter('id', $collection->getId())
            ->groupBy('c.id');

        return $qb->getQuery()->getSingleScalarResult();
    }
}
