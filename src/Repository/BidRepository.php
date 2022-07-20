<?php

namespace App\Repository;

use App\Entity\Bid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bid>
 *
 * @method Bid|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bid|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bid[]    findAll()
 * @method Bid[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BidRepository extends ServiceEntityRepository implements FilterableRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bid::class);
    }

    public function add(Bid $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Bid $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function customCount(array $filters): mixed
    {
        $qb = $this->createQueryBuilder('b')
            ->select('count (b.id) as totalResults');

        if (!isset($filters['status'])) {
            $qb->andWhere('b.status != :invalidStatus')
                ->setParameter('invalidStatus', Bid::STATUS_INVALID);
        }

        if (isset($filters['auctionId'])) {
            $qb->join('b.auction', 'a')
                ->andWhere('a.id = :auctionId')
                ->setParameter('auctionId', $filters['auctionId']);

            unset($filters['auctionId']);
        }

        foreach ($filters as $field => $value) {
            $qb->andWhere('b.' . $field . ' = :' . $field)
                ->setParameter($field, $value);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function customFindAll(array $filters, array $order, int $limit, ?int $offset): array
    {
        $qb = $this->createQueryBuilder('b');

        if (!isset($filters['status'])) {
            $qb->andWhere('b.status != :invalidStatus')
                ->setParameter('invalidStatus', Bid::STATUS_INVALID);
        }

        if (isset($filters['auctionId'])) {
            $qb->join('b.auction', 'a')
                ->andWhere('a.id = :auctionId')
                ->setParameter('auctionId', $filters['auctionId']);

            unset($filters['auctionId']);
        }

        if (count($order) > 0) {
            $qb->orderBy('b.' . key($order), $order[key($order)]);
        }

        foreach ($filters as $field => $value) {
            $qb->andWhere('b.' . $field . ' = :' . $field)
                ->setParameter($field, strtolower($value));
        }

        $qb->setMaxResults($limit);

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return (array)$qb->getQuery()->getResult();
    }
}
