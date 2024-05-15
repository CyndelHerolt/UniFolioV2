<?php

namespace App\Twig\Components;

use App\Components\Trace\Form\TraceAbstractType;
use App\Components\Trace\Form\TraceImageType;
use App\Components\Trace\Form\TraceLienType;
use App\Components\Trace\Form\TracePdfType;
use App\Components\Trace\Form\TraceVideoType;
use App\Components\Trace\TraceRegistry;
use App\Components\Trace\TypeTrace\AbstractTrace;
use App\Components\Trace\TypeTrace\TraceImage;
use App\Components\Trace\TypeTrace\TraceLien;
use App\Components\Trace\TypeTrace\TracePdf;
use App\Components\Trace\TypeTrace\TraceVideo;
use App\Repository\ApcCompetenceRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\TraceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use GuzzleHttp\Client;

#[AsTwigComponent]
final class TraceContent extends AbstractController
{
    public int $id;

    public ?array $preview = [];

    public ?bool $edit;

    public ?string $row;

    public function __construct(
        protected TraceRepository $traceRepository,
        protected TraceRegistry $traceRegistry,
        protected ApcCompetenceRepository $competenceRepository,
        protected ApcNiveauRepository $apcNiveauRepository,
    )
    {
    }

    public function getLinkPreview(string $url): array
    {
        $client = new Client();

        try {
            $response = $client->request('GET', $url);
        } catch (\Exception $e) {
            // Handle the exception here. For example, you can return a default value:
            return [
                'title' => null,
                'description' => null,
                'image' => null,
                'url' => $url,
            ];
        }

        $html = (string)$response->getBody();

        preg_match('/<title>(.*?)<\/title>/is', $html, $matches);
        $title = $matches[1] ?? '';
        // si pas de titre, on met le nom du site
        if (empty($title)) {
            preg_match('/<meta property="og:site_name" content="(.*?)"/is', $html, $matches);
            $title = $matches[1] ?? '';
            // si pas de titre, on met le nom du site
            if (empty($title)) {
                $title = parse_url($url, PHP_URL_HOST);
            }
        }
        // si pas de titre, on met un titre par défaut
        if (empty($title)) {
            $title = null;
        }

        preg_match('/<meta name="description" content="(.*?)"/is', $html, $matches);
        $description = $matches[1] ?? '';
        $description = substr($description, 0, 75);
        // si pas de description, on cherche une balise og:description
        if (empty($description)) {
            preg_match('/<meta property="og:description" content="(.*?)"/is', $html, $matches);
            $description = $matches[1] ?? '';
            $description = substr($description, 0, 75);
        }
        // si pas de description, on met une description par défaut
        if (empty($description)) {
            $description = null;
        }

        preg_match('/<meta property="og:image" content="(.*?)"/is', $html, $matches);
        $image = $matches[1] ?? '';
        // si pas d'image, on cherche une image dans le contenu
        if (empty($image)) {
            preg_match('/<img src="(.*?)"/is', $html, $matches);
            $image = $matches[1] ?? '';
            // si pas d'image, on met l'image link.jpg qui se trouve dans assets/images
            if (empty($image)) {
                $image = null;
            }
        }

        return [
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'url' => $url,
        ];
    }

    public function getForm()
    {
        $trace = $this->traceRepository->find($this->id);


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

        return $form->createView();
    }

    public function getFormType()
    {
        $trace = $this->traceRepository->find($this->id);

        $type = $this->traceRegistry->getTypeTrace($trace->getType());
        $formType = $type::FORM;

//        $formType = $type::FORM;
        $formType = $this->createForm($formType, $trace);
        $formType = $formType->createView();
        $typeTrace = $type::TYPE;

            return $formType;
    }

    public function getSelectedTraceType()
    {
        $trace = $this->traceRepository->find($this->id);
        $type = $trace->getType();

        return $type;
    }

    public function getType()
    {
        $trace = $this->traceRepository->find($this->id);

        return $trace->getType();
    }

    public function getTypesTraces()
    {
        return $this->traceRegistry->getTypeTraces();
    }

    public function getTypeTrace($name)
    {
        return $this->traceRegistry->getTypeTrace($name);
    }

    public function getTrace()
    {
        $trace = $this->traceRepository->find($this->id);

        if ($trace->getType() === "lien") {
            $this->preview = [];
            foreach ($trace->getContenu() as $url) {
                $this->preview[] = $this->getLinkPreview($url);
            }
        }

        return $trace;
    }
}
