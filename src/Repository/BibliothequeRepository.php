<?php

namespace App\Repository;

use App\Entity\Annee;
use App\Entity\Bibliotheque;
use App\Entity\Etudiant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bibliotheque>
 *
 * @method Bibliotheque|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bibliotheque|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bibliotheque[]    findAll()
 * @method Bibliotheque[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BibliothequeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bibliotheque::class);
    }

    public function save(Bibliotheque $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByEtudiantAnnee(?Etudiant $etudiant, ?Annee $annee)
    {
        return $this->createQueryBuilder('b')
            ->where('b.etudiant = :etudiant')
            ->andWhere('b.annee = :annee')
            ->setParameter('annee', $annee)
            ->setParameter('etudiant', $etudiant)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

//    /**
//     * @return Bibliotheque[] Returns an array of Bibliotheque objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Bibliotheque
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
