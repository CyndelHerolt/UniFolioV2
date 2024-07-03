<?php

namespace App\Repository;

use App\Entity\Page;
use App\Entity\Trace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trace>
 *
 * @method Trace|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trace|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trace[]    findAll()
 * @method Trace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TraceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trace::class);
    }

    public function save(Trace $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function delete(Trace $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByCompetence(array $competences): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.traceCompetences', 'tc')
            ->leftJoin('tc.apcNiveau', 'n')
            ->leftJoin('tc.apcApprentissageCritique', 'a')
            ->where('n.id IN (:competences)')
            ->orWhere('a.id IN (:competences)')
            ->setParameter('competences', $competences)
            ->getQuery()
            ->getResult();
    }

    public function findNotInPage(Page $page, Collection $biblio)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.tracePages', 'tp', 'WITH', 'tp.page = :page')
            ->where('t.bibliotheque IN (:biblio)')
            ->andWhere('tp.page IS NULL')
            ->setParameter('biblio', $biblio)
            ->setParameter('page', $page)
            ->getQuery()
            ->getResult();
    }

    public function findInPage(Page $page)
    {
        // écrire une requête qui récupère les traces qui ont pour tracePage.page = page par ordre croissant
        return $this->createQueryBuilder('t')
            ->leftJoin('t.tracePages', 'tp')
            ->where('tp.page = :page')
            ->setParameter('page', $page)
            ->orderBy('tp.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByPortfolio($portfolio)
    {
        return $this->createQueryBuilder('t')
            ->join('t.t.traceCompetences', 'tc')
            ->join('tc.portfolio', 'p')
            ->where('p = :portfolio')
            ->setParameter('portfolio', $portfolio)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Trace[] Returns an array of Trace objects
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

//    public function findOneBySomeField($value): ?Trace
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
