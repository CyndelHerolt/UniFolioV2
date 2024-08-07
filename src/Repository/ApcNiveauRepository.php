<?php

namespace App\Repository;

use App\Entity\ApcNiveau;
use App\Entity\Departement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApcNiveau>
 *
 * @method ApcNiveau|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApcNiveau|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApcNiveau[]    findAll()
 * @method ApcNiveau[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApcNiveauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApcNiveau::class);
    }

    public function save(ApcNiveau $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ApcNiveau $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByAnnee($competence, $annee)
    {
        return $this->createQueryBuilder('n')
            ->join('n.apcCompetence', 'c')
            ->where('c.id = :competence')
            ->andWhere('n.ordre = :ordre')
            ->setParameter('competence', $competence)
            ->setParameter('ordre', $annee)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByAnneeParcours($annee, $parcours)
    {
        return $this->createQueryBuilder('n')
            ->innerJoin('n.apcParcours', 'p')
            ->where('n.ordre = :ordre')
            ->andWhere('p.id = :parcours')
            ->setParameter('ordre', $annee->getOrdre())
            ->setParameter('parcours', $parcours->getId())
            ->getQuery()
            ->getResult();
    }

    public function findByPortfolioUniversitaire($portfolioId)
    {
        return $this->createQueryBuilder('n')
            ->distinct()
            ->join('n.pages', 'p')
            ->join('p.portfolio', 'pu')
            ->where('pu.id = :portfolioId')
            ->setParameter('portfolioId', $portfolioId)
            ->getQuery()
            ->getResult();
    }

    public function truncate(): void
    {
        $this->getEntityManager()->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        $this->createQueryBuilder('n')
            ->delete()
            ->getQuery()
            ->execute();
        $this->getEntityManager()->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS=1');
    }

    public function findByDepartement(?Departement $departement)
    {
        return $this->createQueryBuilder('n')
            ->join('n.apcParcours', 'p')
            ->join('p.apcReferentiel', 'r')
            ->join('r.departement', 'dep')
            ->where('dep = :departement')
            ->setParameter('departement', $departement)
            ->addOrderBy('n.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function createQueryBuilderForDepartement(Departement $departement)
    {
        return $this->createQueryBuilder('n')
            ->join('n.apcParcours', 'p')
            ->join('p.apcReferentiel', 'r')
            ->join('r.departement', 'dep')
            ->where('dep = :departement')
            ->setParameter('departement', $departement)
            ->addOrderBy('n.ordre', 'ASC');
    }
//    /**
//     * @return ApcNiveau[] Returns an array of ApcNiveau objects
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

//    public function findOneBySomeField($value): ?ApcNiveau
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
