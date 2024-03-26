<?php

namespace App\Repository;

use App\Entity\ValidationCriteres;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ValidationCriteres>
 *
 * @method ValidationCriteres|null find($id, $lockMode = null, $lockVersion = null)
 * @method ValidationCriteres|null findOneBy(array $criteria, array $orderBy = null)
 * @method ValidationCriteres[]    findAll()
 * @method ValidationCriteres[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ValidationCriteresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ValidationCriteres::class);
    }

    //    /**
    //     * @return ValidationCriteres[] Returns an array of ValidationCriteres objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ValidationCriteres
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
