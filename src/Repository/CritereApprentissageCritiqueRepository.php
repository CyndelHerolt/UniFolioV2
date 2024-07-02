<?php

namespace App\Repository;

use App\Entity\CritereApprentissageCritique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CritereApprentissageCritique>
 *
 * @method CritereApprentissageCritique|null find($id, $lockMode = null, $lockVersion = null)
 * @method CritereApprentissageCritique|null findOneBy(array $criteria, array $orderBy = null)
 * @method CritereApprentissageCritique[]    findAll()
 * @method CritereApprentissageCritique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CritereApprentissageCritiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CritereApprentissageCritique::class);
    }

    public function save(CritereApprentissageCritique $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByPage(?int $pageId) {
        return $this->createQueryBuilder('c')
            ->andWhere('c.page = :pageId')
            ->setParameter('pageId', $pageId)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return CritereApprentissageCritique[] Returns an array of CritereApprentissageCritique objects
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

    //    public function findOneBySomeField($value): ?CritereApprentissageCritique
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
