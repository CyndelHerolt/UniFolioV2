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
        private readonly CriteresRepository       $criteresRepository,
        protected DepartementRepository           $departementRepository,
        protected EnseignantRepository            $enseignantRepository,
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

    // Récupérer les critères existants
    $criteres = $this->criteresRepository->findBy(['departement' => $departementDefaut]);

    // Définir les libellés et valeurs par défaut
    $defaultLibelles = [
        'Pertinence des medias',
        'Pertinence des argumentaires',
        'Cohérence avec la compétence visée',
        'Diversité des traces'
    ];
    $defaultValeurs = [
        [4 => 'Adaptée', 2 => 'Adéquate', 1 => 'A améliorer', 0 => 'Non pertinente'],
        [4 => 'Solide', 2 => 'Clair', 1 => 'Confus', 0 => 'Inapproprié'],
        [4 => 'En accord total', 2 => 'En accord partiel', 1 => 'En désaccord partiel', 0 => 'En désaccord total'],
        [4 => 'Très diversifiées', 2 => 'Diversifiées', 1 => 'Peu diversifiées', 0 => 'Non diversifiées']
    ];

    // Parcourir les critères existants et les mettre à jour
    foreach ($criteres as $critere) {
        $critere->setLibelle(array_shift($defaultLibelles));
        $critere->setValeurs(array_shift($defaultValeurs));
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
        // on modifie le département par défaut de l'enseignant
        $departementDefaut = $this->departementEnseignantRepository->findOneBy(['enseignant' => $enseignant, 'defaut' => true]);
        $departementDefaut->setDefaut(false);
        $this->departementEnseignantRepository->save($departementDefaut, true);

        $departementEnseignant = $this->departementEnseignantRepository->findOneBy(['departement' => $departement, 'enseignant' => $enseignant]);
        $departementEnseignant->setDefaut(true);
        $this->departementEnseignantRepository->save($departementEnseignant, true);

        return $this->redirectToRoute('app_home_panel');
    }
}
