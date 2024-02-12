<?php

namespace App\Repository;

use App\Entity\ApcReferentiel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApcReferentiel>
 *
 * @method ApcReferentiel|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcReferentiel|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcReferentiel[]    findAll()
 * @method ApcReferentiel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcReferentielRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcReferentiel::class);
    }

    public function save(ApcReferentiel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ApcReferentiel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function truncate(): void
    {
        $this->getEntityManager()->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        $this->createQueryBuilder('r')
            ->delete()
            ->getQuery()
            ->execute();
        $this->getEntityManager()->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS=1');
    }

//    /**
//     * @return ApcReferentiel[] Returns an array of ApcReferentiel objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ApcReferentiel
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
