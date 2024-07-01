<?php

namespace App\Twig\Components;

use App\Components\Trace\TraceRegistry;
use App\Repository\TraceRepository;
use GuzzleHttp\Client;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class TracePortfolioPreview
{
    public int $id;
    public ?array $preview = [];

    public function __construct(
        protected TraceRepository $traceRepository,
        protected TraceRegistry   $traceRegistry,
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

    public function getTrace()
    {
        $trace = $this->traceRepository->find($this->id);

        if ($trace->getType() === "App\Components\Trace\TypeTrace\TraceLien") {
            $this->preview = [];
            $url = $trace->getContenu()[0];
            dump($url);
            $this->preview[] = $this->getLinkPreview($url);

        }

        return $trace;
    }

    public function getType()
    {
        $trace = $this->traceRepository->find($this->id);
        $type = $trace->getType();
        return $this->traceRegistry->getTypeTrace($type)::TYPE;
    }
}
