<?php

namespace App\Repository;

use App\Entity\PortfolioUniv;
use App\Entity\Semestre;
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

    public function remove(PortfolioUniv $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByFilters($dept, Semestre $semestre = null, array $groupes = [], array $etudiants = [], array $competences = []): array
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.pages', 'pa')
            ->innerJoin('pa.tracePages', 'tp')
            ->innerJoin('tp.trace', 't')
            ->innerJoin('t.traceCompetences', 'tc')
            ->innerJoin('tc.apcNiveau', 'c')
            ->innerJoin('p.etudiant', 'e')
            ->innerJoin('e.groupe', 'g')
            ->innerJoin('e.semestre', 's')
            ->innerJoin('s.annee', 'a')
            ->innerJoin('a.diplome', 'd')
            ->innerJoin('d.departement', 'dep')
            ->where('dep.id = :departement')
            ->setParameter('departement', $dept);
        if (!empty($semestre)) {
            $qb->andWhere('s.id IN (:semestre)')
                ->setParameter('semestre', $semestre->getId());
        }
        if (!empty($groupes)) {
            $qb->andWhere('g.id IN (:groupes)')
                ->setParameter('groupes', $groupes);
        }
        if (!empty($competences)) {
            $qb->andWhere('c.id IN (:competences)')
                ->setParameter('competences', $competences);
        }
        if (!empty($etudiants)) {
            $qb->andWhere('e.id IN (:etudiants)')
                ->setParameter('etudiants', $etudiants);
        }
        $qb->distinct('p.id');

        return $qb->getQuery()->getResult();
    }

    public function findByDepartement($dept): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.etudiant', 'e')
            ->innerJoin('e.semestre', 's')
            ->innerJoin('s.annee', 'a')
            ->innerJoin('a.diplome', 'd')
            ->innerJoin('d.departement', 'dep')
            ->where('dep.id = :departement')
            ->setParameter('departement', $dept)
            ->getQuery()
            ->getResult();
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
