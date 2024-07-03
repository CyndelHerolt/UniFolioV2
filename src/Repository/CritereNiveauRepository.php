<?php

namespace App\Repository;

use App\Entity\CritereNiveau;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CritereNiveau>
 *
 * @method CritereNiveau|null find($id, $lockMode = null, $lockVersion = null)
 * @method CritereNiveau|null findOneBy(array $criteria, array $orderBy = null)
 * @method CritereNiveau[]    findAll()
 * @method CritereNiveau[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CritereNiveauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CritereNiveau::class);
    }

    public function save(CritereNiveau $entity, bool $flush = false): void
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
    //     * @return CritereNiveau[] Returns an array of CritereNiveau objects
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

    //    public function findOneBySomeField($value): ?CritereNiveau
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
