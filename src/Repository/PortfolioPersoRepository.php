<?php

namespace App\Repository;

use App\Entity\PortfolioPerso;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PortfolioPerso>
 *
 * @method PortfolioPerso|null find($id, $lockMode = null, $lockVersion = null)
 * @method PortfolioPerso|null findOneBy(array $criteria, array $orderBy = null)
 * @method PortfolioPerso[]    findAll()
 * @method PortfolioPerso[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PortfolioPersoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PortfolioPerso::class);
    }

//    /**
//     * @return PortfolioPerso[] Returns an array of PortfolioPerso objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PortfolioPerso
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
