<?php

namespace App\Controller\Etudiant;

use App\Components\Trace\Form\TraceAbstractType;
use App\Components\Trace\TraceRegistry;
use App\Components\Trace\TypeTrace\TraceImage;
use App\Components\Trace\TypeTrace\TraceLien;
use App\Components\Trace\TypeTrace\TracePdf;
use App\Components\Trace\TypeTrace\TraceVideo;
use App\Controller\BaseController;
use App\Entity\Trace;
use App\Entity\Validation;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcCompetenceRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\PageRepository;
use App\Repository\PortfolioUnivRepository;
use App\Repository\TraceRepository;
use App\Repository\ValidationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etudiant')]
class TraceController extends BaseController
{

    public function __construct(
        private readonly ApcCompetenceRepository            $competenceRepository,
        private readonly ApcNiveauRepository                $apcNiveauRepository,
        private readonly ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        private readonly TraceRegistry                      $traceRegistry,
        private readonly TraceLien                          $traceLien,
        private readonly TraceImage                         $traceImage,
        private readonly TracePdf                           $tracePdf,
        private readonly TraceVideo                         $traceVideo,
        private readonly TraceRepository                    $traceRepository,
        private readonly ValidationRepository               $validationRepository,
        private readonly BibliothequeRepository             $bibliothequeRepository,
        private readonly PortfolioUnivRepository            $portfolioUnivRepository,
        private readonly PageRepository                     $pageRepository,
    )
    {
    }

    #[Route('/trace/show/{id}', name: 'app_trace_show')]
    public function show(?int $id, Request $request): Response
    {
        $trace = $this->traceRepository->find($id);

        $portfolio = $this->portfolioUnivRepository->findOneBy(['id' => $request->query->get('portfolio')]);
        $page = $this->pageRepository->findOneBy(['id' => $request->query->get('page')]);

        return $this->render('trace/show.html.twig', [
            'trace' => $trace,
            'portfolio' => $portfolio ?? null,
            'page' => $page ?? null,
        ]);
    }

    #[Route('/trace/new', name: 'app_trace_new')]
    public function new(Request $request): Response
    {
        $typesTrace = $this->traceRegistry->getTypeTraces();
        $user = $this->getUser()->getEtudiant();

        $semestre = $user->getSemestre();
        $annee = $semestre->getAnnee();

        $dept = $user->getSemestre()->getAnnee()->getDiplome()->getDepartement();

        $groupe = $user->getGroupe();
        foreach ($groupe as $g) {
            if ($g->getTypeGroupe()->getType() === 'TD') {
                $parcours = $g->getApcParcours();
            }
        }

        $apcApprentissageCritiques = [];
        $apcNiveaux = [];

        if ($parcours === null) {
            // ------------récupère tous les apcNiveau de l'année -------------------------
            $referentiel = $dept->getApcReferentiels();
            $competences = $this->competenceRepository->findBy(['apcReferentiel' => $referentiel->first()]);
            $niveaux = [];
            foreach ($competences as $competence) {
                $niveaux = array_merge($niveaux, $this->apcNiveauRepository->findByAnnee($competence, $annee->getOrdre()));
            }
            // si les apcNiveaux dans niveaux ont pour actif = true
            foreach ($niveaux as $niveau) {
                if ($niveau->isActif() === true) {
                    $apcNiveaux[] = $niveau;
                } else {
                    // on stocke tous les apcNiveaux.apcApprentissageCritiques dans un tableau
                    foreach ($niveau->getApcApprentissageCritiques() as $apcApprentissageCritique) {
                        $apcApprentissageCritiques[] = $apcApprentissageCritique;
                    }
                }
            }
        } else {
            // ------------récupère tous les apcNiveau de l'année -------------------------
            $niveaux = $this->apcNiveauRepository->findByAnneeParcours($annee, $parcours);
            foreach ($niveaux as $niveau) {
                if ($niveau->isActif() === true) {
                    $apcNiveaux[] = $niveau;
                } else {
                    // on stocke tous les apcNiveaux.apcApprentissageCritiques dans un tableau
                    foreach ($niveau->getApcApprentissageCritiques() as $apcApprentissageCritique) {
                        $apcApprentissageCritiques[] = $apcApprentissageCritique;
                    }
                }
//                dd($apcApprentissageCritiques);
            }
        }

        $trace = new Trace();
        if (isset($apcNiveaux)) {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $apcNiveaux]);
        } else {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $apcApprentissageCritiques]);
        }
        // Vérifier si un type de trace est stocké dans la session
        //  $selectedTraceType = $request->getSession()->get('selected_trace_type');
        $selectedTraceType = $request->query->get('type', null);
        if ($selectedTraceType !== null) {
            $formType = $selectedTraceType::FORM;
            $formType = $this->createForm($formType, $trace);
            $formType = $formType->createView();
            $typeTrace = $selectedTraceType::TYPE;
        } else {
            $formType = null;
        }

        $groupedApprentissageCritiques = [];
        foreach ($apcApprentissageCritiques as $ac) {
            $niveauId = $ac->getApcNiveau()->getId();
            if (!isset($groupedApprentissageCritiques[$niveauId])) {
                $groupedApprentissageCritiques[$niveauId] = [
                    'niveau' => $ac->getApcNiveau(),
                    'critiques' => [],
                ];
            }
            $groupedApprentissageCritiques[$niveauId]['critiques'][] = $ac;
        }

        return $this->render('trace/form.html.twig', [
            'form' => $form->createView(),
            'typesTrace' => $typesTrace,
            'trace' => $trace,
            'type' => $typeTrace ?? null,
            'formType' => $formType ?? null,
            'selectedTraceType' => $selectedTraceType ?? null,
            'apcNiveaux' => $apcNiveaux ?? null,
            'apcApprentissageCritiques' => $apcApprentissageCritiques ?? null,
            'groupedApprentissageCritiques' => $groupedApprentissageCritiques ?? null,

        ]);
    }

    #[Route('/trace/new/{type}', name: 'app_trace_new_type')]
    public function newType($type, Request $request): Response
    {
        // Stocker le type de trace dans la session
        $request->getSession()->set('selected_trace_type', $type);

        return $this->redirectToRoute('app_trace_new', ['type' => $type]);
    }

    #[Route('/trace/edit/{id}/{type}', name: 'app_trace_edit_type')]
    public function editType(?int $id, $type, Request $request): Response
    {
        $trace = $this->traceRepository->find($id);
        $trace->setContenu([]);
        $this->traceRepository->save($trace, true);
        // Stocker le type de trace dans la session
        $request->getSession()->set('selected_trace_type', $type);

        return $this->redirectToRoute('app_trace_edit', ['type' => $type, 'id' => $id]);
    }

    #[Route('/trace/sauvegarde', name: 'app_trace_new_sauvegarde')]
    public function sauvegardeNewTrace(Request $request): Response
    {
        $data = $request->request->all();
        $files = $request->files->all();
        $formDatas = $request->request->all()['trace_abstract'];

        $etudiant = $this->getUser()->getEtudiant();
        $bibliotheque = $this->bibliothequeRepository->findOneBy(['etudiant' => $etudiant, 'actif' => true]);

        $trace = new Trace();

        if (isset($data['trace_lien'])) {
            $contenu = $data['trace_lien']['contenu'];
            $contenu = $this->traceLien->sauvegarde($contenu, null);
            $trace->setType($this->traceLien::TYPE);
        } elseif (isset($files['trace_image'])) {
            $contenu = $files['trace_image']['contenu'];
            $contenu = $this->traceImage->sauvegarde($contenu, null);
            $contenu['contenu'] = array_filter($contenu['contenu'], function ($item) {
                return is_string($item);
            });
            $trace->setType($this->traceImage::TYPE);
        } elseif (isset($data['trace_video'])) {
            $contenu = $data['trace_video']['contenu'];
            $contenu = $this->traceVideo->sauvegarde($contenu, null);
            $trace->setType($this->traceVideo::TYPE);
        } elseif (isset($files['trace_pdf'])) {
            $contenu = $files['trace_pdf']['contenu'];
            $contenu = $this->tracePdf->sauvegarde($contenu, null);
            $contenu['contenu'] = array_filter($contenu['contenu'], function ($item) {
                return is_string($item);
            });
            $trace->setType($this->tracePdf::TYPE);
        }
//        else {
//            $this->addFlash('danger', 'Type de trace inconnu');
//            return $this->redirectToRoute('app_trace_new');
//        }

        if (isset($contenu) && $contenu['success'] === false) {
            $this->addFlash('danger', $contenu['error']);
            return $this->redirectToRoute('app_trace_new');
        } else {
            if (isset($contenu)) {
                $trace->setContenu($contenu['contenu']);
            }
            $trace->setBibliotheque($bibliotheque);
            $trace->setDateCreation(new \DateTime());
            $trace->setLibelle($formDatas['libelle']);
            $trace->setContexte($formDatas['contexte']);
            if (!empty($formDatas['dateRealisation'])) {
                $trace->setDateRealisation(\DateTime::createFromFormat('m-Y', $formDatas['dateRealisation']));
            } else {
                $trace->setDateRealisation(null);
            }
            $trace->setLegende($formDatas['legende']);
            $trace->setDescription($formDatas['description']);
            $this->traceRepository->save($trace, true);

            // récupérer les compétences stockés dans la session à la construction du formulaire
            $competences = $request->getSession()->get('competences');

            if (isset($request->request->all()['trace_abstract']['competences']) && !empty($request->request->all()['trace_abstract']['competences'])) {
                // récupérer les compétences qui ont été sélectionnées dans le formulaire
                $submittedCompetenceIds = $request->request->all()['trace_abstract']['competences'];

                $selectedCompetences = [];

                $competences = array_flip($competences);

                foreach ($submittedCompetenceIds as $id) {
                    // recouper les compétences sélectionnées avec les compétences stockées dans la session pour récupérer les libellés et les id
                    if (isset($competences[$id])) {
                        $selectedCompetences[$id] = $competences[$id];
                    }
                }

                $apcNiveaux = [];
                $apcApprentissageCritiques = [];


                foreach ($selectedCompetences as $id => $libelle) {
                    // vérifier si un ApcNiveau existe avec l'id et le libellé
                    $apcNiveau = $this->apcNiveauRepository->findOneBy(['id' => $id, 'libelle' => $libelle]);
                    if ($apcNiveau) {
                        $apcNiveaux[] = $apcNiveau;
                        $validation = new Validation();
                        $validation->setApcNiveau($apcNiveau);
                        $validation->setTrace($trace);
                        $validation->setEtat(0);
                        $validation->setDateCreation(new \DateTime());
                        $this->validationRepository->save($validation, true);
                    } else {
                        // vérifier si un ApcApprentissageCritique existe avec l'id et le libellé
                        $apcApprentissageCritique = $this->apcApprentissageCritiqueRepository->findOneBy(['id' => $id, 'libelle' => $libelle]);
                        if ($apcApprentissageCritique) {
                            $apcApprentissageCritiques[] = $apcApprentissageCritique;
                            $validation = new Validation();
                            $validation->setApcApprentissageCritique($apcApprentissageCritique);
                            $validation->setTrace($trace);
                            $validation->setEtat(0);
                            $validation->setDateCreation(new \DateTime());
                            $this->validationRepository->save($validation, true);
                        }
                    }
                }
            }
        }

        return $this->redirectToRoute('app_biblio_traces');
    }

    #[Route('/trace/edit/{id}', name: 'app_trace_edit')]
    public function edit(int $id, Request $request): Response
    {
        $trace = $this->traceRepository->find($id);
        $typesTrace = $this->traceRegistry->getTypeTraces();
        $user = $this->getUser()->getEtudiant();
        $semestre = $user->getSemestre();
        $annee = $semestre->getAnnee();

        $dept = $user->getSemestre()->getAnnee()->getDiplome()->getDepartement();

        $groupe = $user->getGroupe();
        foreach ($groupe as $g) {
            if ($g->getTypeGroupe()->getType() === 'TD') {
                $parcours = $g->getApcParcours();
            }
        }

        $apcApprentissageCritiques = [];
        $apcNiveaux = [];

        if ($parcours === null) {
            // ------------récupère tous les apcNiveau de l'année -------------------------
            $referentiel = $dept->getApcReferentiels();
            $competences = $this->competenceRepository->findBy(['apcReferentiel' => $referentiel->first()]);
            $niveaux = [];
            foreach ($competences as $competence) {
                $niveaux = array_merge($niveaux, $this->apcNiveauRepository->findByAnnee($competence, $annee->getOrdre()));
            }
            // si les apcNiveaux dans niveaux ont pour actif = true
            foreach ($niveaux as $niveau) {
                if ($niveau->isActif() === true) {
                    $apcNiveaux[] = $niveau;
                } else {
                    // on stocke tous les apcNiveaux.apcApprentissageCritiques dans un tableau
                    foreach ($niveau->getApcApprentissageCritiques() as $apcApprentissageCritique) {
                        $apcApprentissageCritiques[] = $apcApprentissageCritique;
                    }
                }
            }
        } else {
            // ------------récupère tous les apcNiveau de l'année -------------------------
            $niveaux = $this->apcNiveauRepository->findByAnneeParcours($annee, $parcours);
            foreach ($niveaux as $niveau) {
                if ($niveau->isActif() === true) {
                    $apcNiveaux[] = $niveau;
                } else {
                    // on stocke tous les apcNiveaux.apcApprentissageCritiques dans un tableau
                    foreach ($niveau->getApcApprentissageCritiques() as $apcApprentissageCritique) {
                        $apcApprentissageCritiques[] = $apcApprentissageCritique;
                    }
                }
            }
        }

        if (isset($apcNiveaux)) {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $apcNiveaux]);
        } else {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $apcApprentissageCritiques]);
        }
        // Vérifier si un type de trace a été passé en paramètre
        $selectedTraceType = $request->query->get('type', null);
        if ($selectedTraceType !== null) {
            $formType = $selectedTraceType::FORM;
            $formType = $this->createForm($formType, $trace);
            $formType = $formType->createView();
            $typeTrace = $selectedTraceType::TYPE;
        } elseif ($trace->getType() !== null) {
            $selectedTraceType = $trace->getType();
            if ($selectedTraceType === 'image') {
                $selectedTraceType = $this->traceImage::CLASS_NAME;
            } elseif ($selectedTraceType === 'lien') {
                $selectedTraceType = $this->traceLien::CLASS_NAME;
            } elseif ($selectedTraceType === 'video') {
                $selectedTraceType = $this->traceVideo::CLASS_NAME;
            } elseif ($selectedTraceType === 'pdf') {
                $selectedTraceType = $this->tracePdf::CLASS_NAME;
            }
            $formType = $selectedTraceType::FORM;
            $formType = $this->createForm($formType, $trace);
            $formType = $formType->createView();
            $typeTrace = $selectedTraceType::TYPE;
        } else {
            $formType = null;
        }

        $groupedApprentissageCritiques = [];
        foreach ($apcApprentissageCritiques as $ac) {
            $niveauId = $ac->getApcNiveau()->getId();
            if (!isset($groupedApprentissageCritiques[$niveauId])) {
                $groupedApprentissageCritiques[$niveauId] = [
                    'niveau' => $ac->getApcNiveau(),
                    'critiques' => [],
                ];
            }
            $groupedApprentissageCritiques[$niveauId]['critiques'][] = $ac;
        }

        // gérer la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            dd($form->getData());
        }

        return $this->render('trace/form.html.twig', [
            'form' => $form->createView(),
            'typesTrace' => $typesTrace,
            'trace' => $trace,
            'type' => $typeTrace ?? null,
            'formType' => $formType ?? null,
            'selectedTraceType' => $selectedTraceType ?? null,
            'apcNiveaux' => $apcNiveaux ?? null,
            'apcApprentissageCritiques' => $apcApprentissageCritiques ?? null,
            'groupedApprentissageCritiques' => $groupedApprentissageCritiques ?? null,
        ]);
    }

    #[Route('/trace/{id}/sauvegarde', name: 'app_trace_edit_sauvegarde')]
    public function sauvegardeEditTrace(?int $id, Request $request): Response
    {
        $data = $request->request->all();
        $files = $request->files->all();
        $formDatas = $request->request->all()['trace_abstract'];

        $etudiant = $this->getUser()->getEtudiant();
        $bibliotheque = $this->bibliothequeRepository->findOneBy(['etudiant' => $etudiant, 'actif' => true]);

        $trace = $this->traceRepository->find($id);

        if (isset($files['trace_image']) && !isset($data['img'])) {
            $contenu = $files['trace_image']['contenu'];
            $contenu = $this->traceImage->sauvegarde($contenu, null);
            $contenu['contenu'] = array_filter($contenu['contenu'], function ($item) {
                return is_string($item);
            });
            $trace->setType($this->traceImage::TYPE);
        } elseif (isset($data['img']) && !isset($files['trace_image'])) {
            $trace->setContenu($data['img']);
            $trace->setType($this->traceImage::TYPE);
        } elseif (isset($data['img']) && isset($files['trace_image'])) {
            $contenu = $files['trace_image']['contenu'];
            $contenu = $this->traceImage->sauvegarde($contenu, null);
            $contenu['contenu'] = array_filter($contenu['contenu'], function ($item) {
                return is_string($item);
            });
            $contenu['contenu'] = array_merge($contenu['contenu'], $data['img']);
            $trace->setType($this->traceImage::TYPE);
        } elseif (isset($data['trace_lien'])) {
            $contenu = $data['trace_lien']['contenu'];
            // si une entrée est vide, on la supprime
            $contenu = array_filter($contenu, function ($item) {
                return $item !== '';
            });
            $contenu = $this->traceLien->sauvegarde($contenu, null);
            $trace->setType($this->traceLien::TYPE);
        } elseif (isset($files['trace_pdf']) && !isset($data['pdf'])) {
            $contenu = $files['trace_pdf']['contenu'];
            $contenu = $this->tracePdf->sauvegarde($contenu, null);
            $contenu['contenu'] = array_filter($contenu['contenu'], function ($item) {
                return is_string($item);
            });
            $trace->setType($this->tracePdf::TYPE);
        } elseif (isset($data['pdf']) && !isset($files['trace_pdf'])) {
            $trace->setContenu($data['pdf']);
            $trace->setType($this->tracePdf::TYPE);
        } elseif (isset($data['pdf']) && isset($files['trace_pdf'])) {
            $contenu = $files['trace_pdf']['contenu'];
            $contenu = $this->tracePdf->sauvegarde($contenu, null);
            $contenu['contenu'] = array_filter($contenu['contenu'], function ($item) {
                return is_string($item);
            });
            $contenu['contenu'] = array_merge($contenu['contenu'], $data['pdf']);
            $trace->setType($this->tracePdf::TYPE);
        } elseif (isset($data['trace_video'])) {
            $contenu = $data['trace_video']['contenu'];
            // si une entrée est vide, on la supprime
            $contenu = array_filter($contenu, function ($item) {
                return $item !== '';
            });
            $contenu = $this->traceVideo->sauvegarde($contenu, null);
            $trace->setType($this->traceVideo::TYPE);
        } else {
            $trace->setContenu([]);
        }

        if (isset($contenu) && $contenu['success'] === false) {
            $this->addFlash('danger', $contenu['error']);
            return $this->redirectToRoute('app_trace_edit', ['id' => $id]);
        } else {
            if (isset($contenu)) {
                $trace->setContenu($contenu['contenu']);
            }
            $trace->setBibliotheque($bibliotheque);
            $trace->setDateCreation(new \DateTime());
            $trace->setLibelle($formDatas['libelle']);
            $trace->setContexte($formDatas['contexte']);
            if (!empty($formDatas['dateRealisation'])) {
                $trace->setDateRealisation(\DateTime::createFromFormat('m-Y', $formDatas['dateRealisation']));
            } else {
                $trace->setDateRealisation(null);
            }
            $trace->setLegende($formDatas['legende']);
            $trace->setDescription($formDatas['description']);
            $this->traceRepository->save($trace, true);

            // récupérer les compétences stockés dans la session à la construction du formulaire
            $competences = $request->getSession()->get('competences');

            if (isset($request->request->all()['trace_abstract']['competences']) && !empty($request->request->all()['trace_abstract']['competences'])) {
                // récupérer les compétences qui ont été sélectionnées dans le formulaire
                $submittedCompetenceIds = $request->request->all()['trace_abstract']['competences'];

                // récupérer toutes les validations pour la trace actuelle
                $validations = $this->validationRepository->findBy(['trace' => $trace]);

                // parcourir chaque validation
                foreach ($validations as $validation) {
                    // obtenir la compétence associée à la validation
                    $competence = $validation->getApcNiveau() ?? $validation->getApcApprentissageCritique();

                    // vérifier si la compétence a été déselectionnée
                    if (!in_array($competence->getId(), $submittedCompetenceIds)) {
                        // si la compétence a été déselectionnée, supprimer la validation
                        $this->validationRepository->delete($validation, true);
                    }
                }

                $selectedCompetences = [];

                $competences = array_flip($competences);

                foreach ($submittedCompetenceIds as $id) {
                    // recouper les compétences sélectionnées avec les compétences stockées dans la session pour récupérer les libellés et les id
                    if (isset($competences[$id])) {
                        $selectedCompetences[$id] = $competences[$id];
                    }
                }

                $apcNiveaux = [];
                $apcApprentissageCritiques = [];


                foreach ($selectedCompetences as $id => $libelle) {
                    // vérifier si un ApcNiveau existe avec l'id et le libellé
                    $apcNiveau = $this->apcNiveauRepository->findOneBy(['id' => $id, 'libelle' => $libelle]);
                    if ($apcNiveau) {
                        // vérifier si une validation avec cette compétence existe déjà pour cette trace
                        $existingValidation = $this->validationRepository->findOneBy(['trace' => $trace, 'apcNiveau' => $apcNiveau]);
                        if (!$existingValidation) {
                            // si aucune validation existante n'est trouvée, créez une nouvelle validation
                            $validation = new Validation();
                            $validation->setApcNiveau($apcNiveau);
                            $validation->setTrace($trace);
                            $validation->setEtat(0);
                            $validation->setDateCreation(new \DateTime());
                            $this->validationRepository->save($validation, true);
                        }
                    } else {
                        // vérifier si un ApcApprentissageCritique existe avec l'id et le libellé
                        $apcApprentissageCritique = $this->apcApprentissageCritiqueRepository->findOneBy(['id' => $id, 'libelle' => $libelle]);
                        if ($apcApprentissageCritique) {
                            // vérifier si une validation avec cette compétence existe déjà pour cette trace
                            $existingValidation = $this->validationRepository->findOneBy(['trace' => $trace, 'apcApprentissageCritique' => $apcApprentissageCritique]);
                            if (!$existingValidation) {
                                // si aucune validation existante n'est trouvée, créez une nouvelle validation
                                $validation = new Validation();
                                $validation->setApcApprentissageCritique($apcApprentissageCritique);
                                $validation->setTrace($trace);
                                $validation->setEtat(0);
                                $validation->setDateCreation(new \DateTime());
                                $this->validationRepository->save($validation, true);
                            }
                        }
                    }
                }
            } else {
                $validations = $this->validationRepository->findBy(['trace' => $trace]);
                foreach ($validations as $validation) {
                    $this->validationRepository->delete($validation, true);
                }
            }
        }

        $this->addFlash('success', 'Trace modifiée avec succès');
        return $this->redirectToRoute('app_biblio_traces');
    }

    #[Route('/trace/delete/{id}', name: 'app_trace_delete')]
    public function delete(int $id): Response
    {
        $trace = $this->traceRepository->find($id);
        $this->traceRepository->delete($trace, true);

        return $this->redirectToRoute('app_biblio_traces');
    }
}
