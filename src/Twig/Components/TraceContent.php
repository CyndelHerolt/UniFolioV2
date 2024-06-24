<?php

namespace App\Twig\Components;

use App\Components\Trace\Form\TraceAbstractType;
use App\Components\Trace\TraceRegistry;
use App\Repository\ApcCompetenceRepository;
use App\Repository\ApcNiveauRepository;
use App\Repository\TraceRepository;
use App\Service\CompetencesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use GuzzleHttp\Client;

#[AsTwigComponent]
final class TraceContent extends AbstractController
{
    public int $id;

    public ?array $preview = [];

    public ?bool $edit = false;

    public ?string $row;

    public function __construct(
        protected TraceRepository $traceRepository,
        protected TraceRegistry $traceRegistry,
        protected ApcCompetenceRepository $competenceRepository,
        protected ApcNiveauRepository $apcNiveauRepository,
        protected CompetencesService $competencesService
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
        $user = $this->getUser();

        $competences = $this->competencesService->getCompetences($user);

        if (isset($apcNiveaux)) {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcNiveaux']]);
        } else {
            $form = $this->createForm(TraceAbstractType::class, $trace, ['user' => $user, 'competences' => $competences['apcApprentissagesCritiques']]);
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

    public function getTraceType()
    {
        $trace = $this->traceRepository->find($this->id);
        $type = $this->traceRegistry->getTypeTrace($trace->getType())::TYPE;

        return $type;
    }


    public function getTrace()
    {
        $trace = $this->traceRepository->find($this->id);

        if ($trace->getType() === "App\Components\Trace\TypeTrace\TraceLien") {
            $this->preview = [];
            foreach ($trace->getContenu() as $url) {
                $this->preview[] = $this->getLinkPreview($url);
            }
        }

        return $trace;
    }
}
