<?php

namespace App\Repository;

use App\Entity\TracePage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TracePage>
 *
 * @method TracePage|null find($id, $lockMode = null, $lockVersion = null)
 * @method TracePage|null findOneBy(array $criteria, array $orderBy = null)
 * @method TracePage[]    findAll()
 * @method TracePage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TracePageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TracePage::class);
    }

//    /**
//     * @return TracePage[] Returns an array of TracePage objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TracePage
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
