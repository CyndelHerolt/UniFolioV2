<?php

/*
 * Copyright (c) 2023. | Cyndel Herolt | IUT de Troyes  - All Rights Reserved
 * @author cyndelherolt
 * @project UniFolio
 */
namespace App\Controller\SynchroIntranet;

use App\Entity\Annee;
use App\Entity\ApcParcours;
use App\Entity\Departement;
use App\Entity\Diplome;
use App\Entity\Etudiant;
use App\Entity\Groupe;
use App\Entity\Semestre;
use App\Entity\TypeGroupe;
use App\Repository\AnneeRepository;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\ApcParcoursRepository;
use App\Repository\ApcReferentielRepository;
use App\Repository\ApcCompetenceRepository;
use App\Repository\DepartementRepository;
use App\Repository\DiplomeRepository;
use App\Repository\EnseignantRepository;
use App\Repository\EtudiantRepository;
use App\Repository\GroupeRepository;
use App\Repository\SemestreRepository;
use App\Repository\TypeGroupeRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StructureSynchro extends AbstractController
{
    #[Route('/api/intranet/structure', name: 'app_synchro_intranet_structure')]
    public function index(
        HttpClientInterface $client,
        DepartementRepository $departementRepository,
        DiplomeRepository $diplomeRepository,
        AnneeRepository $anneeRepository,
        SemestreRepository $semestreRepository,
        TypeGroupeRepository $typeGroupeRepository,
        GroupeRepository $groupeRepository,
        ApcParcoursRepository $apcParcoursRepository,
        ApcReferentielRepository $apcReferentielRepository,
        ApcCompetenceRepository $apCcompetenceRepository,
        ApcNiveauRepository $niveauRepository,
        EtudiantRepository $etudiantRepository,
        ApcApprentissageCritiqueRepository $apprentissageCritiqueRepository,
        EnseignantRepository $enseignantRepository,
        UserRepository $usersRepository
    ): Response {
        //http://symfony.com/doc/current/http_client.html



        if ($departementRepository->findOneBy(['libelle' => 'Fixtures'])) {
            $etudiant = $etudiantRepository->findOneBy(['username' => 'etudiant']);
            $etudiantRepository->remove($etudiant, true);
            $enseignant = $enseignantRepository->findOneBy(['username' => 'enseignant']);
            $enseignantRepository->remove($enseignant, true);

            // Vide les tables
            $apcReferentielRepository->truncate();
            $apCcompetenceRepository->truncate();
            $niveauRepository->truncate();
            $apprentissageCritiqueRepository->truncate();
            $apcParcoursRepository->truncate();
            $groupeRepository->truncate();
            $typeGroupeRepository->truncate();
            $semestreRepository->truncate();
            $anneeRepository->truncate();
            $diplomeRepository->truncate();
            $departementRepository->truncate();
        }

        //-------------------------------------------------------------------------------------------------------
        //-----------------------------------------DEPARTEMENTS--------------------------------------------------
        //-------------------------------------------------------------------------------------------------------

        $departements = $client->request(
            'GET',
            $_ENV['API_URL'] . 'unifolio/departement',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-api-key' => $_ENV['API_KEY']
                ]
            ]
        );

        $departements = $departements->toArray();
        foreach ($departements as $departement) {
            //Si il existe un tableau contenant 'id'=>4 dans le tableau $departement['typeD']
            if (in_array(['id' => 4], $departement['typeD'])) {
                $existingDepartement = $departementRepository->findOneBy(['id' => $departement['id']]);
                //Vérifier si l'id' du département existe déjà en base de données
                if ($existingDepartement) {
                    $existingDepartement->setId($departement['id']);
                    $existingDepartement->setLibelle($departement['libelle']);
                    $existingDepartement->setLogoName($departement['logo']);
                    $existingDepartement->setCouleur($departement['couleur']);
                    $existingDepartement->setDescription($departement['description']);
                    $departementRepository->save($existingDepartement, true);
                } else {
                    //Sinon, on le crée
                    $newDepartement = new Departement();
                    $newDepartement->setId($departement['id']);
                    $newDepartement->setLibelle($departement['libelle']);
                    $newDepartement->setLogoName($departement['logo']);
                    $newDepartement->setCouleur($departement['couleur']);
                    $newDepartement->setDescription($departement['description']);
                    $departementRepository->save($newDepartement, true);
                }
            }
        }


        //-------------------------------------------------------------------------------------------------------
        //-----------------------------------------DIPLOMES------------------------------------------------------
        //-------------------------------------------------------------------------------------------------------

        $diplomes = $client->request(
            'GET',
            $_ENV['API_URL'] . 'unifolio/diplome',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-api-key' => $_ENV['API_KEY']
                ]
            ]
        );

        $diplomes = $diplomes->toArray();

        foreach ($diplomes as $diplome) {
            //Récupérer le libellé du département du diplôme
            $dept = $departementRepository->find($diplome['departement']);

            $existingDiplome = $diplomeRepository->findOneBy(['id' => $diplome['id']]);
            if ($diplome['type'] === 4) {
//                dd($parcours);
                //Vérifier si le libelle du département existe déjà en base de données
                if ($existingDiplome) {
                    $existingDiplome->setId($diplome['id']);
                    $existingDiplome->setLibelle($diplome['libelle']);
                    $existingDiplome->setSigle($diplome['sigle']);
                    $existingDiplome->setDepartement($dept);
                    foreach ($diplome['parcours'] as $parcours) {
                        $parcours = $apcParcoursRepository->findOneBy(['id' => $parcours['id']]);
                        $existingDiplome->setApcParcours($parcours);
                    }
                    $diplomeRepository->save($existingDiplome, true);
                } else {
                    //Sinon, on le crée
                    $newDiplome = new Diplome();
                    $newDiplome->setId($diplome['id']);
                    $newDiplome->setLibelle($diplome['libelle']);
                    $newDiplome->setSigle($diplome['sigle']);
                    $newDiplome->setDepartement($dept);
                    foreach ($diplome['parcours'] as $parcours) {
                        $parcours = $apcParcoursRepository->findOneBy(['id' => $parcours['id']]);
                        $newDiplome->setApcParcours($parcours);
                    }
                    $diplomeRepository->save($newDiplome, true);
                }
            }
        }


        //-------------------------------------------------------------------------------------------------------
        //-----------------------------------------ANNEES------------------------------------------------------
        //-------------------------------------------------------------------------------------------------------

        $annees = $client->request(
            'GET',
            $_ENV['API_URL'] . 'unifolio/annee',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-api-key' => $_ENV['API_KEY']
                ]
            ]
        );

        $annees = $annees->toArray();
        foreach ($annees as $annee) {
            $diplome = $diplomeRepository->findOneBy(['id' => $annee['diplome']]);

            if ($diplome) {
                $existingAnnee = $anneeRepository->findOneBy(['id' => $annee['id']]);
                //Vérifier si le libelle du département existe déjà en base de données
                if ($existingAnnee) {
                    $existingAnnee->setId($annee['id']);
                    $existingAnnee->setLibelle($annee['libelle']);
                    $existingAnnee->setOrdre($annee['ordre']);
                    $existingAnnee->setLibelleLong($annee['libelle_long']);
                    $existingAnnee->setOptAlternance($annee['opt_alternance']);
                    $existingAnnee->setActif($annee['actif']);
                    $existingAnnee->setDiplome($diplome);
                    $anneeRepository->save($existingAnnee, true);
                } else {
                    //Sinon, on le crée
                    $newAnnee = new Annee();
                    $newAnnee->setId($annee['id']);
                    $newAnnee->setLibelle($annee['libelle']);
                    $newAnnee->setOrdre($annee['ordre']);
                    $newAnnee->setLibelleLong($annee['libelle_long']);
                    $newAnnee->setOptAlternance($annee['opt_alternance']);
                    $newAnnee->setActif($annee['actif']);
                    $newAnnee->setDiplome($diplome);
                    $anneeRepository->save($newAnnee, true);
                }
            }
        }

        //-------------------------------------------------------------------------------------------------------
        //-----------------------------------------SEMESTRES-----------------------------------------------------
        //-------------------------------------------------------------------------------------------------------

        $semestres = $client->request(
            'GET',
            $_ENV['API_URL'] . 'unifolio/semestre',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-api-key' => $_ENV['API_KEY']
                ]
            ],
        );

        $semestres = $semestres->toArray();
        foreach ($semestres as $semestre) {
            $annee = $anneeRepository->findOneBy(['id' => $semestre['annee']]);

            if ($annee) {
                $existingSemestre = $semestreRepository->findOneBy(['id' => $semestre['id']]);
                //Vérifier si le libelle du département existe déjà en base de données
                if ($existingSemestre) {
                    $existingSemestre->setId($semestre['id']);
                    $existingSemestre->setLibelle($semestre['libelle']);
                    $existingSemestre->setOrdreAnnee($semestre['ordreAnnee']);
                    $existingSemestre->setOrdreLmd($semestre['ordreLmd']);
                    $existingSemestre->setCodeElement($semestre['code']);
                    $existingSemestre->setActif($semestre['actif']);
                    $existingSemestre->setAnnee($annee);
                    $semestreRepository->save($existingSemestre, true);
                } else {
                    //Sinon, on le crée
                    $newSemestre = new Semestre();
                    $newSemestre->setId($semestre['id']);
                    $newSemestre->setLibelle($semestre['libelle']);
                    $newSemestre->setOrdreAnnee($semestre['ordreAnnee']);
                    $newSemestre->setOrdreLmd($semestre['ordreLmd']);
                    $newSemestre->setCodeElement($semestre['code']);
                    $newSemestre->setActif($semestre['actif']);
                    $newSemestre->setAnnee($annee);
                    $semestreRepository->save($newSemestre, true);
                }
            }
        }

        //-------------------------------------------------------------------------------------------------------
        //-----------------------------------------TYPES GROUPES-------------------------------------------------
        //-------------------------------------------------------------------------------------------------------

        $typesGroupes = $client->request(
            'GET',
            $_ENV['API_URL'] . 'unifolio/type_groupe',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-api-key' => $_ENV['API_KEY']
                ]
            ]
        );

        $typesGroupes = $typesGroupes->toArray();

        foreach ($typesGroupes as $typeGroupe) {
            $existingTypeGroupe = $typeGroupeRepository->findOneBy(['id' => $typeGroupe['id']]);
            if ($existingTypeGroupe) {
                $existingTypeGroupe->setId($typeGroupe['id']);
                $existingTypeGroupe->setLibelle($typeGroupe['libelle']);
                $existingTypeGroupe->setOrdreSemestre($typeGroupe['ordre']);
                $existingTypeGroupe->setType($typeGroupe['type']);
                if ($typeGroupe['semestres'] != null) {
                    foreach ($typeGroupe['semestres'] as $semestre) {
                        $semestre = $semestreRepository->findOneBy(['id' => $semestre['id']]);
                        if ($semestre) {
                            $existingTypeGroupe->addSemestre($semestre);
                        }
//                        else {
//                            $typeGroupeRepository->remove($existingTypeGroupe, true);
//                        };
                    }
                }
                $typeGroupeRepository->save($existingTypeGroupe, true);
            } else {
                //Sinon, on le crée
                $newTypeGroupe = new TypeGroupe();
                $newTypeGroupe->setId($typeGroupe['id']);
                $newTypeGroupe->setLibelle($typeGroupe['libelle']);
                $newTypeGroupe->setOrdreSemestre($typeGroupe['ordre']);
                $newTypeGroupe->setType($typeGroupe['type']);
                if ($typeGroupe['semestres'] != null) {
                    foreach ($typeGroupe['semestres'] as $semestre) {
                        $semestre = $semestreRepository->findOneBy(['code_element' => $semestre['code']]);
                        if ($semestre) {
                            $newTypeGroupe->addSemestre($semestre);
                        }
                    }
                }
                $typeGroupeRepository->save($newTypeGroupe, true);
            }
        }

        //-------------------------------------------------------------------------------------------------------
        //-----------------------------------------------GROUPES-------------------------------------------------
        //-------------------------------------------------------------------------------------------------------

        $groupes = $client->request(
            'GET',
            $_ENV['API_URL'] . 'unifolio/groupe',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-api-key' => $_ENV['API_KEY']
                ]
            ]
        );

        $groupes = $groupes->toArray();

        foreach ($groupes as $groupe) {
//            $semestre = $semestreRepository->findOneBy(['code_element' => $groupe['semestre']]);
            $semestres = [];
            foreach ($groupe['semestres'] as $semestre) {
                $semestre = $semestreRepository->findOneBy(['code_element' => $semestre['code']]);
                if ($semestre) {
                    $semestres[] = $semestre;
                }
            }

//            if ($semestre) {
            if ($semestres != null) {
                $existingGroupe = $groupeRepository->findOneBy(['id' => $groupe['id']]);
                //Vérifier si l'id du département existe déjà en base de données
                if ($existingGroupe) {
                    $existingGroupe->setId($groupe['id']);
                    $existingGroupe->setLibelle($groupe['libelle']);
                    $existingGroupe->setOrdre($groupe['ordre']);
                    $existingGroupe->setCodeApogee($groupe['code']);
                    foreach ($groupe['type'] as $typeGroupe) {
                        if ($typeGroupe) {
                            $typeGroupe = $typeGroupeRepository->findOneBy(['id' => $typeGroupe['id']]);
                            $existingGroupe->setTypeGroupe($typeGroupe);
                        }
                    }
                    foreach ($groupe['parcours'] as $parcours) {
                        $parcours = $apcParcoursRepository->findOneBy(['id' => $parcours['id']]);
                        $existingGroupe->setApcParcours($parcours);
                    }
                    $groupeRepository->save($existingGroupe, true);
                } else {
                    //Sinon, on le crée
                    $newGroupe = new Groupe();
                    $newGroupe->setId($groupe['id']);
                    $newGroupe->setLibelle($groupe['libelle']);
                    $newGroupe->setOrdre($groupe['ordre']);
                    $newGroupe->setCodeApogee($groupe['code']);
                    foreach ($groupe['type'] as $typeGroupe) {
                        if ($typeGroupe) {
                            $typeGroupe = $typeGroupeRepository->findOneBy(['id' => $typeGroupe['id']]);
                            $newGroupe->setTypeGroupe($typeGroupe);
                        }
                    }
                    foreach ($groupe['parcours'] as $parcours) {
                        $parcours = $apcParcoursRepository->findOneBy(['id' => $parcours['id']]);
                        $newGroupe->setApcParcours($parcours);
                    }
                    $groupeRepository->save($newGroupe, true);
                }
            }
        }


        $this->addFlash('success', 'Les données ont bien été importées.');

//        return $this->redirectToRoute('app_dashboard', [
//            'departements' => $departements
//        ]);
        return $this->redirectToRoute('admin');
    }
}
