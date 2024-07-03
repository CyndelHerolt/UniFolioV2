<?php

/*
 * Copyright (c) 2023. | Cyndel Herolt | IUT de Troyes  - All Rights Reserved
 * @author cyndelherolt
 * @project UniFolio
 */

namespace App\Controller\SynchroIntranet;

use App\Entity\ApcNiveau;
use App\Entity\Bibliotheque;
use App\Entity\CritereApprentissageCritique;
use App\Entity\CritereNiveau;
use App\Entity\DepartementEnseignant;
use App\Entity\Etudiant;
use App\Entity\Enseignant;
use App\Entity\Page;
use App\Entity\PortfolioUniv;
use App\Repository\AnneeUniversitaireRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\CritereApprentissageCritiqueRepository;
use App\Repository\CritereNiveauRepository;
use App\Repository\CriteresRepository;
use App\Repository\DepartementRepository;
use App\Repository\EnseignantRepository;
use App\Repository\EtudiantRepository;
use App\Repository\GroupeRepository;
use App\Repository\PageRepository;
use App\Repository\PortfolioUnivRepository;
use App\Repository\SemestreRepository;
use App\Service\CompetencesService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class UserSynchro extends AbstractController
{
    public function __construct(
        private readonly AnneeUniversitaireRepository $anneeUniversitaireRepository,
        private readonly CompetencesService           $competencesService,
        private readonly PortfolioUnivRepository      $portfolioUnivRepository,
        private readonly PageRepository               $pageRepository,
        private readonly CriteresRepository           $criteresRepository,
        private readonly DepartementRepository        $departementRepository,
        private readonly CritereNiveauRepository                $critereNiveauRepository,
        private readonly CritereApprentissageCritiqueRepository $critereApprentissageCritiqueRepository,
    )
    {
    }

    #[Route('/api/intranet/etudiant', name: 'app_email_intranet_etudiant')]
    public function CheckEmailEtudiant(
        $login,
        HttpClientInterface $client,
        MailerInterface $mailer,
        VerifyEmailHelperInterface $verifyEmailHelper,
    )
    {

        $response = $client->request(
            'GET',
            $_ENV['API_URL'] . 'unifolio/etudiant',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-api-key' => $_ENV['API_KEY']
                ],
                'query' => [
                    'username' => $login
                ]
            ]
        );

        $response = $response->toArray();

        if (in_array($login, array_column($response, 'username'))) {
            //Sélectionner l'etudiant dans le tableau
            $etudiant = array_filter($response, function ($etudiant) use ($login) {
                return $etudiant['username'] === $login;
            });
            foreach ($etudiant as $data) {
                $mailEtudiant = $data['mail_univ'];

                $signatureComponents = $verifyEmailHelper->generateSignature(
                    'app_verify_email',
                    $data['username'],
                    $data['mail_univ'],
                    ['id' => $data['username']]
                );
                $email = (new TemplatedEmail())
                    ->from(new Address('portfolio.iut-troyes@univ-reims.fr', 'UniFolio Mail Bot'))
                    ->to($mailEtudiant)
                    ->subject('UniFolio - Vérification de compte - Action Requise')
                    ->htmlTemplate('email.html.twig')
                    ->context([
                        'confirm_link' => $signatureComponents->getSignedUrl(),
                        'user' => null,
                        'email_subject' => 'Vérification de votre compte',
                        'email_message' => '<p>Pour accéder à votre compte, il est impératif de vérifier votre adresse mail. Cela garantit la sécurité de vos données personnelles.</p>
                                    <p>Pour ce faire, cliquez sur le bouton ci-dessous.</p>
                                    <p>Si vous n\'êtes pas à l\'origine de cette demande, que vous rencontrez des difficultés ou que vous avez des questions, merci de ne pas cliquer sur le bouton et de nous contacter à portfolio.iut-troyes@univ-reims.fr.</p>
                                    <p>Cordialement,</p>',
                        'email_button' => 'confirm_email'
                    ]);
                $mailer->send($email);
            }
            return true;
        }
        return false;
    }

    #[Route('/api/intranet/etudiant', name: 'app_get_email_intranet_etudiant')]
    public function getEmailEtudiant(
        $login,
        HttpClientInterface $client,
    )
    {

        $response = $client->request(
            'GET',
            $_ENV['API_URL'] . 'unifolio/etudiant',
            //            'https://intranetv3.iut-troyes.univ-reims.fr/fr/api/unifolio/etudiant',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-api-key' => $_ENV['API_KEY']
                ],
                'query' => [
                    'username' => $login
                ]
            ]
        );

        $response = $response->toArray();

        if (in_array($login, array_column($response, 'username'))) {
            //Sélectionner l'etudiant dans le tableau
            $etudiant = array_filter($response, function ($etudiant) use ($login) {
                return $etudiant['username'] === $login;
            });
            foreach ($etudiant as $data) {
                // Créer un nouvel etudiant dans la base de données avec les données de $etudiant
                $mailEtudiant = $data['mail_univ'];
            }
            return $mailEtudiant;
        }
        return false;
    }

    #[Route('/api/intranet/etudiant', name: 'app_synchro_intranet_etudiant')]
    public function synchroEtudiant(
        $login,
        $user,
        HttpClientInterface $client,
        EtudiantRepository $etudiantRepository,
        BibliothequeRepository $bibliothequeRepository,
        GroupeRepository $groupeRepository,
        SemestreRepository $semestreRepository,
    )
    {

        $response = $client->request(
            'GET',
            $_ENV['API_URL'] . 'unifolio/etudiant',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-api-key' => $_ENV['API_KEY']
                ],
                'query' => [
                    'username' => $login
                ]
            ]
        );

        $response = $response->toArray();

        if (in_array($login, array_column($response, 'username'))) {
            //Sélectionner l'etudiant dans le tableau
            $etudiant = array_filter($response, function ($etudiant) use ($login) {
                return $etudiant['username'] === $login;
            });
            foreach ($etudiant as $data) {
                // vérifier si l'étudiant existe déjà dans la base de données
                $existingEtudiant = $etudiantRepository->findOneBy(['username' => $data['username']]);
                if (!$existingEtudiant) {
                    $semestre = $semestreRepository->findOneBy(['id' => $data['semestre']]);
                    // Créer un nouvel etudiant dans la base de données avec les données de $etudiant
                    $newEtudiant = new Etudiant();
                    $newEtudiant->setUser($user);
                    $biblio = new Bibliotheque();
                    $biblio->setEtudiant($newEtudiant);
                    $biblio->setAnnee($semestre->getAnnee());
                    $biblio->setAnneeUniversitaire($this->anneeUniversitaireRepository->findOneBy(['active' => true]));
                    $biblio->setActif(true);
                    $newEtudiant->setNom($data['nom']);
                    $newEtudiant->setPrenom($data['prenom']);
                    $newEtudiant->setUsername($data['username']);
                    $newEtudiant->setMailUniv($data['mail_univ']);
                    $newEtudiant->setMailPerso($data['mail_perso']);
                    $newEtudiant->setSemestre($semestre);
                    foreach ($data['groupes'] as $groupe) {
                        $groupe = $groupeRepository->findOneBy(['id' => $groupe]);
                        $newEtudiant->addGroupe($groupe);
                    }
                    $portfolio = new PortfolioUniv();
                    $portfolio->setEtudiant($newEtudiant);
                    $portfolio->setAnnee($semestre->getAnnee());
                    $portfolio->setAnneeUniv($this->anneeUniversitaireRepository->findOneBy(['active' => true]));
                    $portfolio->setLibelle('Portfolio de ' . $newEtudiant->getNom() . ' ' . $newEtudiant->getPrenom());
                    $portfolio->setBanniere('/files_directory/banniere.jpg');
                    $portfolio->setDescription('Modifier la description de votre portfolio');
                    $portfolio->setDateCreation(new \DateTime());
                    $portfolio->setDateModification(new \DateTime());

                    $competences = $this->competencesService->getCompetencesEtudiant($user);

                    $competences = $competences['apcNiveaux'] ?? $competences['apcApprentissagesCritiques'];
                    $this->portfolioUnivRepository->save($portfolio, true);

                    foreach ($competences as $competence) {
                        $page = new Page();
                        $page->setPortfolio($portfolio);
                        $page->setLibelle($competence->getLibelle());
                        if ($competence instanceof ApcNiveau) {
                            $page->setApcNiveau($competence);
                        } else {
                            $page->setApcApprentissageCritique($competence);
                        }

                        $this->pageRepository->save($page, true);
                    }

                    $departement = $this->departementRepository->findDepartementEtudiant($newEtudiant);
                    $criteres = $this->criteresRepository->findByDepartement($departement->getId());

                    $pages = $this->pageRepository->findBy(['portfolio' => $portfolio]);

                    foreach ($pages as $page) {
                        $competence = $page->getApcNiveau() ?? $page->getApcApprentissageCritique();
                        foreach ($criteres as $critere) {
                            if ($competence instanceof ApcNiveau) {
                                $eval = new CritereNiveau();
                                $eval->setCritere($critere);
                                $eval->setPage($page);
                                $eval->setApcNiveau($competence);
                                $eval->setValeur(null);
                                $this->critereNiveauRepository->save($eval, true);
                            } else {
                                $eval = new CritereApprentissageCritique();
                                $eval->setCritere($critere);
                                $eval->setPage($page);
                                $eval->setApprentissageCritique($competence);
                                $eval->setValeur(null);
                                $this->critereApprentissageCritiqueRepository->save($eval, true);
                            }
                        }
                    }

                    $etudiantRepository->save($newEtudiant, true);
                    $bibliothequeRepository->save($biblio, true);
                }
            }
            return true;
        } else {
            return false;
        }
    }

    #[Route('/api/intranet/enseignant', name: 'app_email_intranet_enseignant')]
    public function checkEmailEnseignant(
        $login,
        HttpClientInterface $client,
        MailerInterface $mailer,
        VerifyEmailHelperInterface $verifyEmailHelper
    )
    {

        $response = $client->request(
            'GET',
            $_ENV['API_URL'] . 'unifolio/enseignant',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-api-key' => $_ENV['API_KEY']
                ],
                'query' => [
                    'username' => $login
                ]
            ]
        );

        $response = $response->toArray();

        if (in_array($login, array_column($response, 'username'))) {
            //Sélectionner l'enseignant dans le tableau
            $enseignant = array_filter($response, function ($enseignant) use ($login) {
                return $enseignant['username'] === $login;
            });
            foreach ($enseignant as $data) {
                $mailEnseignant = $data['mail_univ'];

                $signatureComponents = $verifyEmailHelper->generateSignature(
                    'app_verify_email',
                    $data['username'],
                    $data['mail_univ'],
                    ['id' => $data['username']]
                );
                $email = (new TemplatedEmail())
                    ->from(new Address('portfolio.iut-troyes@univ-reims.fr', 'UniFolio Mail Bot'))
                    ->to($mailEnseignant)
                    ->subject('UniFolio - Confirmation de votre compte')
                    ->htmlTemplate('email.html.twig')
                    ->context([
                        'confirm_link' => $signatureComponents->getSignedUrl(),
                        'user' => null,
                        'email_subject' => 'Confirmation de votre compte',
                        'email_message' => '<p>Afin de vérifier votre compte, , cliquez sur le bouton ci-dessous.</p>
                                    <p>Si vous n\'êtes pas à l\'origine de cette demande, merci de ne pas cliquer sur le bouton et de contacter l\'administrateur du site.</p>',
                        'email_button' => 'confirm_email'
                    ]);
                $mailer->send($email);
            }
            return true;
        }
        return false;
    }

    #[Route('/api/intranet/enseignant', name: 'app_get_email_intranet_enseignant')]
    public function getEmailEnseignant(
        $login,
        HttpClientInterface $client,
    )
    {

        $response = $client->request(
            'GET',
            $_ENV['API_URL'] . 'unifolio/enseignant',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-api-key' => $_ENV['API_KEY']
                ],
                'query' => [
                    'username' => $login
                ]
            ]
        );

        $response = $response->toArray();

        if (in_array($login, array_column($response, 'username'))) {
            //Sélectionner l'etudiant dans le tableau
            $enseignant = array_filter($response, function ($enseignant) use ($login) {
                return $enseignant['username'] === $login;
            });
            foreach ($enseignant as $data) {
                // Créer un nouvel etudiant dans la base de données avec les données de $etudiant
                $mailEnseignant = $data['mail_univ'];
            }
            return $mailEnseignant;
        }
        return false;
    }


    #[Route('/api/intranet/enseignant', name: 'app_synchro_intranet_enseignant')]
    public function synchroEnseignant(
        $login,
        $user,
        HttpClientInterface $client,
        EnseignantRepository $enseignantRepository,
        DepartementRepository $departementRepository,
    )
    {

        $response = $client->request(
            'GET',
            $_ENV['API_URL'] . 'unifolio/enseignant',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'x-api-key' => $_ENV['API_KEY']
                ],
                'query' => [
                    'username' => $login
                ]
            ]
        );

        $response = $response->toArray();

        if (in_array($login, array_column($response, 'username'))) {
            //Sélectionner l'enseignant dans le tableau
            $enseignant = array_filter($response, function ($enseignant) use ($login) {
                return $enseignant['username'] === $login;
            });
            foreach ($enseignant as $data) {
                // vérifier si l'étudiant existe déjà dans la base de données
                $existingEnseignant = $enseignantRepository->findOneBy(['username' => $data['username']]);
                if (!$existingEnseignant) {
                    // Créer un nouvel enseignant dans la base de données avec les données de $enseignant
                    $newEnseignant = new Enseignant();
                    $newEnseignant->setUser($user);
                    $newEnseignant->setNom($data['nom']);
                    $newEnseignant->setPrenom($data['prenom']);
                    $newEnseignant->setUsername($data['username']);
                    $newEnseignant->setMailUniv($data['mail_univ']);
                    $newEnseignant->setMailPerso($data['mail_perso']);
                    foreach ($data['departements'] as $departement) {
                        $departement = $departementRepository->findOneBy(['libelle' => $departement]);
                        if ($departement) {
                            $newDepartementEnseignant = new DepartementEnseignant($newEnseignant, $departement);
                            $newEnseignant->AddDepartementEnseignant($newDepartementEnseignant);
                        }
                    }
                    $enseignantRepository->save($newEnseignant, true);
                }
            }
            return true;
        } else {
            return false;
        }
    }

    #[Route('/api/intranet/etudiant/update', name: 'app_update_intranet_etudiant')]
    public function updateEtudiant(
        HttpClientInterface $client,
        EtudiantRepository  $etudiantRepository,
        GroupeRepository    $groupeRepository,
        SemestreRepository  $semestreRepository,
    )
    {
        $etudiants = $etudiantRepository->findAll();

        foreach ($etudiants as $etudiant) {
            $login = $etudiant->getUsername();

            if ($login !== 'etudiant') {
                $response = $client->request(
                    'GET',
                    $_ENV['API_URL'] . 'unifolio/etudiant',
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'x-api-key' => $_ENV['API_KEY']
                        ],
                        'query' => [
                            'username' => $login
                        ]
                    ]
                );

                $response = $response->toArray();

                if (in_array($login, array_column($response, 'username'))) {
                    //Sélectionner l'etudiant dans le tableau
                    $etudiantSelected = array_filter($response, function ($etudiantSelected) use ($login) {
                        return $etudiantSelected['username'] === $login;
                    });
                    foreach ($etudiantSelected as $data) {
                        $semestre = $semestreRepository->findOneBy(['id' => $data['semestre']]);
                        // update etudiant dans la base de données avec les données de $etudiantSelected
                        $etudiant->setNom($data['nom']);
                        $etudiant->setPrenom($data['prenom']);
                        $etudiant->setUsername($data['username']);
                        $etudiant->setMailUniv($data['mail_univ']);
                        $etudiant->setMailPerso($data['mail_perso']);
                        $etudiant->setSemestre($semestre);
                        $etudiant->setAnneeSortie($data['annee_sortie']);
                        // retirer les groupes de l'étudiant
                        foreach ($etudiant->getGroupe() as $grp) {
                            $etudiant->removeGroupe($grp);
                        }
                        foreach ($data['groupes'] as $groupe) {
                            $groupe = $groupeRepository->findOneBy(['id' => $groupe]);
                            $etudiant->addGroupe($groupe);
                        }
                        $etudiantRepository->save($etudiant, true);
                    }
                }
            }
        }
        $this->addFlash('success', 'Les données ont bien été mises à jour.');
//        return $this->redirectToRoute('app_dashboard');
        return $this->redirectToRoute('admin');
    }

    #[Route('/api/intranet/enseignant/update', name: 'app_update_intranet_enseignant')]
    public function updateEnseignant(
        HttpClientInterface   $client,
        EnseignantRepository  $enseignantRepository,
        DepartementRepository $departementRepository,
    )
    {
        $enseignants = $enseignantRepository->findAll();

        foreach ($enseignants as $enseignant) {
            $login = $enseignant->getUsername();

            if ($login !== 'enseignant') {
                $response = $client->request(
                    'GET',
                    $_ENV['API_URL'] . 'unifolio/enseignant',
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'x-api-key' => $_ENV['API_KEY']
                        ],
                        'query' => [
                            'username' => $login
                        ]
                    ]
                );

                $response = $response->toArray();

                if (in_array($login, array_column($response, 'username'))) {
                    //Sélectionner l'enseignant dans le tableau
                    $enseignantSelected = array_filter($response, function ($enseignantSelected) use ($login) {
                        return $enseignantSelected['username'] === $login;
                    });
                    foreach ($enseignantSelected as $data) {
                        $enseignant->setNom($data['nom']);
                        $enseignant->setPrenom($data['prenom']);
                        $enseignant->setUsername($data['username']);
                        $enseignant->setMailUniv($data['mail_univ']);
                        $enseignant->setMailPerso($data['mail_perso']);
                        foreach ($data['departements'] as $departement) {
                            $departement = $departementRepository->findOneBy(['libelle' => $departement]);
                            if ($departement) {
                                // retirer les departements de l'enseignant
                                // todo: à revoir
//                                foreach ($enseignant->getDepartementEnseignants() as $dept) {
//                                    $enseignant->removeDepartementEnseignant($dept);
//                                }
                                $enseignantDepartement = new DepartementEnseignant($enseignant, $departement);
                                $enseignant->AddDepartementEnseignant($enseignantDepartement);
                            }
                        }
                        $enseignantRepository->save($enseignant, true);
                    }
                }
            }
        }
        $this->addFlash('success', 'Les données ont bien été mises à jour.');
//        return $this->redirectToRoute('app_dashboard');
        return $this->redirectToRoute('admin');
    }
}
