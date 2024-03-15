<?php

namespace App\Controller\Enseignant;

use App\Entity\Criteres;
use App\Form\CritereType;
use App\Repository\CriteresRepository;
use App\Repository\DepartementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/enseignant')]
class SettingsController extends AbstractController
{
    public function __construct(
        private CriteresRepository    $criteresRepository,
        private DepartementRepository $departementRepository
    )
    {
    }

    #[Route('/settings', name: 'app_settings')]
    public function index(Request $request): Response
    {
        $edit = false;

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
}
