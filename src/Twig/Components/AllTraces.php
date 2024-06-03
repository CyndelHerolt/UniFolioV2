<?php

namespace App\Twig\Components;

use App\Entity\Annee;
use App\Entity\Trace;
use App\Repository\AnneeRepository;
use App\Repository\ApcApprentissageCritiqueRepository;
use App\Repository\ApcCompetenceRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\TraceRepository;
use App\Service\CompetencesService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('AllTraces')]
final class AllTraces
{
    use DefaultActionTrait;

    public array $competences = [];

    #[LiveProp(writable: true)]
    public array $selectedCompetences = [];

    #[LiveProp(writable: true)]
    /** @var Trace[] */
    public array $allTraces = [];

    #[LiveProp(writable: true)]
    public string $selectedOrdreDate = '';

    #[LiveProp(writable: true)]
    public string $selectedOrdreValidation = '';

    #[LiveProp(writable: true)]
    public array $selectedTraces = [];

    #[LiveProp(writable: true)]
    public ?int $selectedAnnee = null;

    public function __construct(
        private readonly Security                    $security,
        protected ApcNiveauRepository                $apcNiveauRepository,
        protected ApcApprentissageCritiqueRepository $apcApprentissageCritiqueRepository,
        protected ApcCompetenceRepository            $apcCompetenceRepository,
        protected BibliothequeRepository             $bibliothequeRepository,
        protected TraceRepository                    $traceRepository,
        protected AnneeRepository                    $anneeRepository,
        protected CompetencesService                 $competencesService,
    )
    {
        $user = $this->security->getUser();
        $annee = $user->getEtudiant()->getSemestre()->getAnnee();

        $competences = $this->competencesService->getCompetences($user);

        if (isset($competences['apcNiveaux'])) {
            $this->competences = $competences['apcNiveaux'];
        } else {
            $this->competences = $competences['apcApprentissagesCritiques'];
        }

        $this->selectedAnnee = $annee->getId();

        $this->allTraces = $this->getAllTrace();
    }

    #[LiveAction]
    public function changeCompetences()
    {
        $this->allTraces = $this->getAllTrace();
    }

    #[LiveAction]
    public function changeOrdreDate()
    {
        $this->selectedOrdreValidation = '';
        $this->allTraces = $this->getAllTrace();
    }

    #[LiveAction]
    public function changeOrdreValidation()
    {
        $this->selectedOrdreDate = '';
        $this->allTraces = $this->getAllTrace();
    }

    #[LiveAction]
    public function changeAnnee()
    {
        $this->allTraces = $this->getAllTrace();
    }

    #[LiveAction]
    public function deleteSelectedTraces(): void
    {
        if ($this->selectedTraces === null) {
            return;
        }
        foreach ($this->selectedTraces as $trace) {
            $trace = $this->traceRepository->find($trace);
            $this->traceRepository->delete($trace, true);
        }
        $this->allTraces = $this->getAllTrace();
    }

    public function getAllTrace(): array
    {
        $annee = $this->anneeRepository->find($this->selectedAnnee);
        $bibliotheque = $this->bibliothequeRepository->findOneByEtudiantAnnee($this->security->getUser()->getEtudiant(), $annee);

        $ordreDate = $this->selectedOrdreDate != null ? $this->selectedOrdreDate : null;

        $ordreValidation = $this->selectedOrdreValidation != null ? $this->selectedOrdreValidation : null;

        if (!empty($this->selectedCompetences)) {
            $traces = $this->traceRepository->findByCompetence($this->selectedCompetences);
        } else {
            $traces = $this->traceRepository->findBy(['bibliotheque' => $bibliotheque]);
        }

        // Si on a des traces, on peut les trier
        if (!empty($traces)) {
            // Trier par date si ordreDate est défini
            if (!empty($ordreDate)) {
                usort($traces, function (Trace $a, Trace $b) use ($ordreDate) {
                    if ($ordreDate === "ASC") {
                        if ($a->getDateModification() === null) {
                            return $a->getDateCreation() <=> $b->getDateCreation();
                        } else {
                            return $b->getDateCreation() <=> $a->getDateCreation();
                        }
                    } else {
                        if ($a->getDateModification() === null) {
                            return $b->getDateCreation() <=> $a->getDateCreation();
                        } else {
                            return $a->getDateCreation() <=> $b->getDateCreation();
                        }
                    }
                });
            }

            if (!empty($ordreValidation)) {
                usort($traces, function (Trace $a, Trace $b) use ($ordreValidation) {
                    $totalA = $a->getValidations()->count();
                    $totalB = $b->getValidations()->count();

                    $validationsA = $a->getValidations()->filter(function ($validation) {
                        return $validation->getEtat() == 3;
                    })->count();

                    $validationsB = $b->getValidations()->filter(function ($validation) {
                        return $validation->getEtat() == 3;
                    })->count();

                    // Ratio du nombre des validations avec un état de 3 sur le total des validations
                    $ratiosA = ($totalA > 0) ? $validationsA / $totalA : 0;
                    $ratiosB = ($totalB > 0) ? $validationsB / $totalB : 0;

                    if ($ordreValidation === "ASC") {
                        return $ratiosA <=> $ratiosB;
                    } else {
                        return $ratiosB <=> $ratiosA;
                    }
                });
            }
        }

        return $traces;
    }

}
