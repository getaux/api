<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Asset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Asset|null find($id, $lockMode = null, $lockVersion = null)
 * @method Asset|null findOneBy(array $criteria, array $orderBy = null)
 * @method Asset[]    findAll()
 * @method Asset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetRepository extends ServiceEntityRepository implements FilterableRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asset::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Asset $entity, bool $flush = true): void
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
    public function remove(Asset $entity, bool $flush = true): void
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
            $qb->join('a.collection', 'c')
                ->andWhere('c.address = :address')
                ->setParameter('address', $filters['collection']);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function customFindAll(array $filters, array $order, int $limit, ?int $offset): array
    {
        $qb = $this->createQueryBuilder('a');

        if (isset($filters['collection'])) {
            $qb->join('a.collection', 'c')
                ->andWhere('c.address = :address')
                ->setParameter('address', $filters['collection']);
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
}
