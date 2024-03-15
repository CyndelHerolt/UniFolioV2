<?php

namespace App\Controller\Enseignant;

use App\Controller\BaseController;
use App\Entity\Criteres;
use App\Entity\Departement;
use App\Form\CritereType;
use App\Repository\CriteresRepository;
use App\Repository\DepartementEnseignantRepository;
use App\Repository\DepartementRepository;
use App\Repository\EnseignantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/enseignant')]
class SettingsController extends BaseController
{
    public function __construct(
        private CriteresRepository    $criteresRepository,
        protected DepartementRepository $departementRepository,
        protected EnseignantRepository  $enseignantRepository,
        protected DepartementEnseignantRepository $departementEnseignantRepository,
    )
    {
    }

    #[Route('/settings', name: 'app_settings')]
    public function index(Request $request): Response
    {
        $edit = false;

        $error = $request->query->get('error');
        $error = json_decode($error, true);

        $enseignant = $this->getUser()->getEnseignant();
        $departementDefaut = $this->departementRepository->findDepartementEnseignantDefaut($enseignant);
        $criteres = $this->criteresRepository->findBy(['departement' => $departementDefaut]);

        $formCriteres = [];
        foreach ($criteres as $critere) {
            $formCriteres[$critere->getId()] = $this->createForm(CritereType::class, $critere)->createView();
        }

        $editCritereId = $request->query->get('critereId');

        $request->query->get('edit') ? $edit = true : $edit = false;

        return $this->render('settings/index.html.twig', [
            'error' => $error ?? null,
            'formCriteres' => $formCriteres ?? null,
            'editCritereId' => $editCritereId,
            'criteres' => $criteres ?? null,
            'edit' => $edit,
        ]);
    }

    #[Route('/settings/criteres/defaut', name: 'app_settings_criteres_defaut')]
    public function criteres(): Response
    {
        $enseignant = $this->getUser()->getEnseignant();
        $departementDefaut = $this->departementRepository->findDepartementEnseignantDefaut($enseignant);

        // si il existe déjà des critères qui ont pour département le département par défaut de l'enseignant on les supprime
        $criteres = $this->criteresRepository->findBy(['departement' => $departementDefaut]);
        if ($criteres) {
            foreach ($criteres as $critere) {
                $this->criteresRepository->remove($critere, true);
            }
        }

        // on crée les critères par défaut pour le département de l'enseignant
        for ($i = 0; $i < 4; $i++) {
            $critere = new Criteres();
            if ($i === 0) {
                $critere->setLibelle('Pertinence du ou des media.s');
                $critere->setValeurs([4 => 'Adaptée', 2 => 'Adéquate', 1 => 'A améliorer', 0 => 'Non pertinente']);
            } elseif ($i === 1) {
                $critere->setLibelle('Pertinence de l\'argumentaire');
                $critere->setValeurs([4 => 'Solide', 2 => 'Clair', 1 => 'Confus', 0 => 'Inapproprié']);
            } elseif ($i === 2) {
                $critere->setLibelle('Cohérence avec la compétence visée');
                $critere->setValeurs([4 => 'En accord total', 2 => 'En accord partiel', 1 => 'En désaccord partiel', 0 => 'En désaccord total']);
            } elseif ($i === 3) {
                $critere->setLibelle('Qualité de la rédaction');
                $critere->setValeurs([4 => 'Précise', 2 => 'Soignée', 1 => 'Approximative', 0 => 'Brouillone']);
            }
            $critere->setDepartement($departementDefaut);

            $this->criteresRepository->save($critere, true);
        }

        return $this->redirectToRoute('app_settings');
    }

    #[Route('/settings/criteres/edit/{id}', name: 'app_settings_criteres_edit')]
    public function editCriteres(Request $request, ?int $id): Response
    {
        $critere = $this->criteresRepository->find($id);

        $form = $this->createForm(CritereType::class, $critere);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all();

            // Récupérer les clés et les valeurs
            $keys = [];
            $values = $data['critere']['valeurs'];
            foreach ($data as $key => $value) {
                if ($key !== 'critere') {
                    $keys[] = $value;
                }
            }
            // si dans le tableau keys on a des valeurs identiques on redirige vers la page d'édition
            if (count($keys) !== count(array_unique($keys))) {
                $error = ['message' => 'Les valeurs ne peuvent pas être identiques', 'where' => 'bareme'];
                $error = json_encode($error);
                return $this->redirectToRoute('app_settings', ['edit' => true, 'critereId' => $id, 'error' => $error]);
            }

            // Assembler les clés et les valeurs
            $transformedValues = array_combine($keys, $values);
            // Remplacer les valeurs du critère par les valeurs transformées
            $critere->setValeurs($transformedValues);

            $this->criteresRepository->save($critere, true);
        } else {
            return $this->redirectToRoute('app_settings', ['edit' => true, 'critereId' => $id]);
        }
        return $this->redirectToRoute('app_settings');
    }

    #[Route('/settings/choix_departement', name: 'app_settings_choix_departement')]
    public function choixDepartement(Request $request): Response
    {
        $enseignant = $this->getUser()->getEnseignant();
        $departementsEnseignant = $enseignant->getDepartementEnseignants();

        $departements = [];
        foreach ($departementsEnseignant as $departementEnseignant) {
            $departements[] = $departementEnseignant->getDepartement();
        }

        return $this->render('settings/choix_departement.html.twig', [
            'departements' => $departements,
            'enseignant' => $enseignant,
        ]);
    }

#[Route('/settings/choix_departement/save/{id}', name: 'app_settings_choix_departement_save')]
    public function choixDepartementSave(?int $id): Response
    {
        $enseignant = $this->getUser()->getEnseignant();
        $departement = $this->departementRepository->find($id);
        $departementEnseignant = $this->departementEnseignantRepository->findOneBy(['departement' => $departement, 'enseignant' => $enseignant]);
        $departementEnseignant->setDefaut(true);
        $this->departementEnseignantRepository->save($departementEnseignant, true);

        return $this->redirectToRoute('app_home_panel');
    }
}
