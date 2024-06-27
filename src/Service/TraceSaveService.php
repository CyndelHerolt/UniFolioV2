<?php

namespace App\Service;

use App\Components\Trace\TraceRegistry;
use App\Controller\BaseController;
use App\Entity\Trace;
use App\Entity\TraceCompetence;
use App\Entity\TracePage;
use App\Entity\Validation;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\PageRepository;
use App\Repository\PortfolioUnivRepository;
use App\Repository\TraceCompetenceRepository;
use App\Repository\TracePageRepository;
use App\Repository\TraceRepository;
use App\Repository\ValidationRepository;
use Symfony\Component\HttpFoundation\Request;

class TraceSaveService extends BaseController
{
    public function __construct(
        private readonly BibliothequeRepository             $bibliothequeRepository,
        private readonly TraceRegistry                      $traceRegistry,
        private readonly TraceRepository                    $traceRepository,
        private readonly ApcNiveauRepository                $apcNiveauRepository,
        private readonly ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        private readonly TraceCompetenceRepository          $traceCompetenceRepository,
        private readonly PortfolioUnivRepository            $portfolioUnivRepository,
        private readonly PageRepository                     $pageRepository,
        private readonly TracePageRepository                $tracePageRepository,
        private readonly DataUserSessionService             $dataUserSessionService,
    )
    {
        parent::__construct(
            $this->dataUserSessionService,
        );
    }


    public function save(?Trace $trace, Request $request)
    {
        $etudiant = $this->getUser()->getEtudiant();
        $bibliotheque = $this->bibliothequeRepository->findOneBy(['etudiant' => $etudiant, 'actif' => true]);

        $data = $request->request->all();
        $files = $request->files->all();
        $formDatas = $request->request->all()['trace_abstract'];

        // on réunit les clés des tableaux $data et $files
        $typeDatas = array_merge(array_keys($data), array_keys($files));

        // on récupère la clé du type de trace
        $typeTraceForm = array_diff($typeDatas, ['trace_abstract']);
//        dd($typeTraceForm);

        if (!empty($typeTraceForm)) {
            // on récupère la première clé du tableau
            $key = $typeTraceForm[array_key_first($typeTraceForm)];

            // on récupère le type de trace correspondant à la clé
            $typeTrace = $this->traceRegistry->getTypeTraceFromForm($key);

            if (isset($data[$key]['contenu']) || isset($files[$key]['contenu'])) {
                // on récupère le contenu du type de trace
                $contenu = $files == [] ? $data[$key]['contenu'] : $files[$key]['contenu'];

                $existingContenu = $data[$key] ?? null;
                dump($existingContenu);
                $sauvegarde = $typeTrace->sauvegarde($contenu, $existingContenu);
                $content = $sauvegarde['contenu'];

            } else {
                if (isset($data[$key])) {
                    $content = $data[$key];
                } else {
                    $content = [];
                }
            }
            $trace->setType($typeTrace::class);

        } elseif($trace->getType() !== null){
            $typeTrace = $trace->getType();
//            $content = [];
            $trace->setType($typeTrace);
        } else {
//            $content = [];
            $trace->setType(null);
        }

        $trace->setContenu($content ?? []);
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

        // récupérer les compétences stockées dans la session à la construction du formulaire
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


            $traceCompetences = $this->traceCompetenceRepository->findBy(['trace' => $trace]);

            foreach ($traceCompetences as $traceCompetence) {
                if ($traceCompetence->getApcNiveau()) {
                    // Check if the competence id is not in the list of submitted competence ids
                    if (!in_array($traceCompetence->getApcNiveau()->getId(), $submittedCompetenceIds)) {
                        // Delete the TraceCompetence entity
                        $this->traceCompetenceRepository->remove($traceCompetence, true);
                    }
                } elseif ($traceCompetence->getApcApprentissageCritique()) {
                    // Check if the competence id is not in the list of submitted competence ids
                    if (!in_array($traceCompetence->getApcApprentissageCritique()->getId(), $submittedCompetenceIds)) {
                        // Delete the TraceCompetence entity
                        $this->traceCompetenceRepository->remove($traceCompetence, true);
                    }
                }
            }

            $annee = $etudiant->getSemestre()->getAnnee();
            $portfolio = $this->portfolioUnivRepository->findOneBy(['etudiant' => $etudiant, 'annee' => $annee]);
            foreach ($selectedCompetences as $id => $libelle) {
                // vérifier si un ApcNiveau existe avec l'id et le libellé
                $apcNiveau = $this->apcNiveauRepository->findOneBy(['id' => $id, 'libelle' => $libelle]);
                if ($apcNiveau) {
                    // si il n'existe pas déjà un traceCompetence lié a l'apcNiveau et à la trace
                    if (!$this->traceCompetenceRepository->findOneBy(['apcNiveau' => $apcNiveau, 'trace' => $trace])) {
                        $apcNiveaux[] = $apcNiveau;
                        $traceCompetence = new TraceCompetence();
                        $traceCompetence->setApcNiveau($apcNiveau);
                        $traceCompetence->setTrace($trace);
                        $this->traceCompetenceRepository->save($traceCompetence, true);

                        $page = $this->pageRepository->findOneBy(['portfolio' => $portfolio, 'apc_niveau' => $apcNiveau]);

                        $tracePage = new TracePage();
                        $tracePage->setPage($page);
                        $tracePage->setTrace($trace);
                        $tracePage->setOrdre(count($page->getTracePages()) + 1);
                        $this->tracePageRepository->save($tracePage, true);
                    }
                } else {
                    // vérifier si un ApcApprentissageCritique existe avec l'id et le libellé
                    $apcApprentissageCritique = $this->apcApprentissageCritiqueRepository->findOneBy(['id' => $id, 'libelle' => $libelle]);
                    if ($apcApprentissageCritique) {
                        // si il n'existe pas déjà un traceCompetence lié a l'apcApprentissageCritique et à la trace
                        if (!$this->traceCompetenceRepository->findOneBy(['apcApprentissageCritique' => $apcApprentissageCritique, 'trace' => $trace])) {
                            $apcApprentissageCritiques[] = $apcApprentissageCritique;
                            $traceCompetence = new TraceCompetence();
                            $traceCompetence->setApcApprentissageCritique($apcApprentissageCritique);
                            $traceCompetence->setTrace($trace);
                            $this->traceCompetenceRepository->save($traceCompetence, true);

                            $page = $this->pageRepository->findOneBy(['portfolio' => $portfolio, 'apc_niveau' => $apcNiveau]);

                            $tracePage = new TracePage();
                            $tracePage->setPage($page);
                            $tracePage->setTrace($trace);
                            $tracePage->setOrdre(count($page->getTracePages()) + 1);
                            $this->tracePageRepository->save($tracePage, true);
                        }
                    }
                }
            }
        }
        return [$error ?? null, $trace];
    }
}