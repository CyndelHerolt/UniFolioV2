<?php

namespace App\Twig\Components;

use App\Repository\TraceRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use GuzzleHttp\Client;

#[AsTwigComponent]
final class TraceContent
{
    public int $id;

    public ?array $preview = [];

    public function __construct(
        protected TraceRepository $traceRepository,
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

        preg_match('/<meta name="description" content="(.*?)"/is', $html, $matches);
        $description = $matches[1] ?? '';
        $description = substr($description, 0, 75);

        preg_match('/<meta property="og:image" content="(.*?)"/is', $html, $matches);
        $image = $matches[1] ?? '';

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

        if ($trace->getType() === "lien") {
            $this->preview = [];
            foreach ($trace->getContenu() as $url) {
                $this->preview[] = $this->getLinkPreview($url);
            }
        }

        return $trace;
    }
}
