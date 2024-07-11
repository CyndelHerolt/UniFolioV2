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

        $competences = $this->competencesService->getCompetencesEtudiant($user);

        if (isset($competences['apcNiveaux'])) {
            $this->competences = $competences['apcNiveaux'];
        } else {
            $this->competences = $competences['apcApprentissagesCritiques'];
        }

        $this->selectedAnnee = $annee->getId();

        $this->allTraces = $this->getAllTrace();
    }

    #[LiveAction]
    public function selectAll(): void
    {
        // si toutes les traces sont déjà sélectionnées, on les déselectionne
        if (count($this->selectedTraces) === count($this->allTraces)) {
            $this->selectedTraces = [];
        } else {
            $this->selectedTraces = array_map(fn(Trace $trace) => $trace->getId(), $this->allTraces);
        }
    }

    #[LiveAction]
    public function changeCompetences()
    {
        $this->allTraces = $this->getAllTrace();
    }

    #[LiveAction]
    public function changeOrdreDate()
    {
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
                        return $a->getDateModification() <=> $b->getDateModification();
                    } else {
                        return $b->getDateModification() <=> $a->getDateModification();
                    }
                });
            }
        }

        return $traces;
    }

}
