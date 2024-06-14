<?php

namespace App\Service;

use App\Components\Trace\TraceRegistry;
use App\Controller\BaseController;
use App\Entity\Validation;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\TraceRepository;
use App\Repository\ValidationRepository;
use Symfony\Component\HttpFoundation\Request;

class TraceSaveService extends BaseController
{
    public function __construct(
        private BibliothequeRepository             $bibliothequeRepository,
        private TraceRegistry                      $traceRegistry,
        private TraceRepository                    $traceRepository,
        private ApcNiveauRepository                $apcNiveauRepository,
        private ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        private ValidationRepository               $validationRepository,
        private readonly DataUserSessionService    $dataUserSessionService,
    )
    {
        parent::__construct(
            $this->dataUserSessionService,
        );
    }


    public function save($trace, Request $request)
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
                $sauvegarde = $typeTrace->sauvegarde($contenu);
                if ($existingContenu) {
                    if ($sauvegarde['success'] === true) {
                        $content = array_merge($sauvegarde['contenu'], $existingContenu);
                    } else {
                        $this->addFlash('danger', $sauvegarde['error']);
                        //todo: service check referer/site domain

                        // récupérer le nom de la route émettrice
                        $referer = $request->headers->get('referer');
                        // retourner au referer
                        return $this->redirect($referer);
                    }
                } else {
                    if ($sauvegarde['success'] === true) {
                        $content = $sauvegarde['contenu'];
                    } else {
                        $this->addFlash('danger', $sauvegarde['error']);
                        // récupérer le nom de la route émettrice
                        $referer = $request->headers->get('referer');
                        $selectedTraceType = $request->getSession()->get('selected_trace_type');

                        // retourner au referer
                        return $this->redirect($referer . '?type=' . $selectedTraceType);
                    }
                }
            } else {
                if (isset($data[$key])) {
                    $content = $data[$key];
                } else {
                    $content = [];
                }
            }
            $trace->setType($typeTrace::class);

        } else {
            $typeTrace = $trace->getType();
            $content = [];
            $trace->setType($typeTrace);
        }

        $trace->setContenu($content);
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
}