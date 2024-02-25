<?php

namespace App\Repository;

use App\Entity\PortfolioUniv;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PortfolioUniv>
 *
 * @method PortfolioUniv|null find($id, $lockMode = null, $lockVersion = null)
 * @method PortfolioUniv|null findOneBy(array $criteria, array $orderBy = null)
 * @method PortfolioUniv[]    findAll()
 * @method PortfolioUniv[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PortfolioUnivRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PortfolioUniv::class);
    }

    public function save(PortfolioUniv $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PortfolioUniv[] Returns an array of PortfolioUniv objects
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

//    public function findOneBySomeField($value): ?PortfolioUniv
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
