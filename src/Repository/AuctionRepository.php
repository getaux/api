<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Auction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Auction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Auction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Auction[]    findAll()
 * @method Auction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuctionRepository extends ServiceEntityRepository implements FilterableRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Auction::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Auction $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Auction $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function customCount(array $filters): mixed
    {
        $qb = $this->createQueryBuilder('a')
            ->select('count (a.id) as totalResults');

        if (isset($filters['collection'])) {
            $qb->join('a.asset', 'asset')
                ->andWhere('asset.tokenAddress = :collection')
                ->setParameter('collection', $filters['collection']);

            unset($filters['collection']);
        }

        foreach ($filters as $field => $value) {
            $qb->andWhere('a.' . $field . ' = :' . $field)
                ->setParameter($field, $value);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function customFindAll(array $filters, array $order, int $limit, ?int $offset): array
    {
        $qb = $this->createQueryBuilder('a');

        if (isset($filters['collection'])) {
            $qb->join('a.asset', 'asset')
                ->andWhere('asset.tokenAddress = :collection')
                ->setParameter('collection', $filters['collection']);

            unset($filters['collection']);
        }

        foreach ($filters as $field => $value) {
            $qb->andWhere('a.' . $field . ' = :' . $field)
                ->setParameter($field, $value);
        }

        if (count($order) > 0) {
            $qb->orderBy('a.' . key($order), $order[key($order)]);
        }

        $qb->setMaxResults($limit);

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return (array)$qb->getQuery()->getResult();
    }

    public function findEndedAuctions(\DateTime $endAt): array
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.endAt <= :endAt')
            ->setParameter('endAt', $endAt)
            ->andWhere('a.status = :status')
            ->setParameter('status', Auction::STATUS_ACTIVE);

        return (array)$qb->getQuery()->getResult();
    }
}
