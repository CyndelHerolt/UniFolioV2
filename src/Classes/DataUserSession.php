<?php

namespace App\Classes;

use App\Repository\DepartementEnseignantRepository;
use App\Repository\DepartementRepository;
use App\Repository\EnseignantRepository;
use App\Repository\EtudiantRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class DataUserSession
{
    private $departement;
    private $departements;
    private $enseignant;
    private $etudiant;

    public function __construct(
        private DepartementRepository           $departementRepository,
        private DepartementEnseignantRepository $departementEnseignantRepository,
        private EnseignantRepository            $enseignantRepository,
        private EtudiantRepository              $etudiantRepository,
        private RequestStack                    $requestStack,
        private Security                        $security,
    )
    {
    }

    public function getSession()
    {
        return $this->requestStack->getSession();
    }

    public function getEnseignant()
    {
        $this->enseignant = $this->enseignantRepository->findOneBy(['username' => $this->security->getUser()->getUsername()]);

        return $this->enseignant;
    }

    public function getEtudiant()
    {
        $this->etudiant = $this->etudiantRepository->findOneBy(['username' => $this->security->getUser()->getUsername()]);

        return $this->etudiant;
    }

    public function setDepartement($departement)
    {
        $this->departement = $departement;
    }

    public function getDepartementsEnseignant()
    {
        $this->departements = $this->departementEnseignantRepository->findBy(['enseignant' => $this->getEnseignant()]);
        return $this->departements;
    }

    public function getDepartementDefaut()
    {
        $deptEnseignant = $this->departementEnseignantRepository->findOneBy([
            'enseignant' => $this->getEnseignant(),
            'defaut' => true
        ]);
        $this->departement = $deptEnseignant->getDepartement();
        return $this->departement;
    }

    public function getDepartementsNotDefaut()
    {
        $deptEnseignant = $this->departementEnseignantRepository->findBy([
            'enseignant' => $this->getEnseignant(),
            'defaut' => false
        ]);
        $this->departements = [];
        foreach ($deptEnseignant as $dept) {
            $this->departements[] = $dept->getDepartement();
        }

        return $this->departements;
    }

}