<?php

namespace App\Repository;

use App\Entity\Conference;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

/**
 * @extends ServiceEntityRepository<Conference>
 */
class ConferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conference::class);
    }

    /**
     * @return list<Conference>
     */
    public function list(): array
    {
        return $this->findAll();
    }

    /**
     * @return list<Conference>
     *
     * @throws InvalidArgumentException When both dates are null.
     */
    public function searchBetweenDates(DateTimeImmutable|null $startAt = null, DateTimeImmutable|null $endAt = null): array
    {
        if (null === $startAt && null === $endAt) {
            throw new InvalidArgumentException('At least one date must be provided');
        }

        $qb = $this->createQueryBuilder('conference');

        if (null !== $startAt) {
            $qb
                ->andWhere($qb->expr()->gte('conference.startAt', ':startAt'))
                ->setParameter('startAt', $startAt)
            ;
        }

        if (null !== $endAt) {
            $qb
                ->andWhere('conference.endAt <= :endAt')
                ->setParameter('endAt', $endAt)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Conference[] Returns an array of Conference objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Conference
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
