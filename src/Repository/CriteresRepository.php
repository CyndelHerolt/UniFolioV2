<?php

namespace App\Repository;

use App\Entity\Criteres;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Criteres>
 *
 * @method Criteres|null find($id, $lockMode = null, $lockVersion = null)
 * @method Criteres|null findOneBy(array $criteria, array $orderBy = null)
 * @method Criteres[]    findAll()
 * @method Criteres[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CriteresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Criteres::class);
    }

    public function save(Criteres $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Criteres $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByDepartement(int $departementId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.departement = :departementId')
            ->setParameter('departementId', $departementId)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Criteres[] Returns an array of Criteres objects
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

    //    public function findOneBySomeField($value): ?Criteres
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
