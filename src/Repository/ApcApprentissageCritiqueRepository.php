<?php

namespace App\Repository;

use App\Entity\ApcApprentissageCritique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApcApprentissageCritique>
 *
 * @method ApcApprentissageCritique|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcApprentissageCritique|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcApprentissageCritique[]    findAll()
 * @method ApcApprentissageCritique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcApprentissageCritiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcApprentissageCritique::class);
    }

    public function save(ApcApprentissageCritique $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ApcApprentissageCritique $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function truncate(): void
    {
        $this->getEntityManager()->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        $this->createQueryBuilder('a')
            ->delete()
            ->getQuery()
            ->execute();
        $this->getEntityManager()->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS=1');
    }

    public function findByApcNiveau($niveau): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.apcNiveau = :niveau')
            ->setParameter('niveau', $niveau)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return ApcApprentissageCritique[] Returns an array of ApcApprentissageCritique objects
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

//    public function findOneBySomeField($value): ?ApcApprentissageCritique
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
