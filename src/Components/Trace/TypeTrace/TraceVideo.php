<?php

namespace App\Components\Trace\TypeTrace;

use App\Components\Trace\Form\TraceImageType;
use App\Components\Trace\Form\TraceVideoType;
use App\Components\Trace\TypeTrace\AbstractTrace;
use App\Repository\TraceRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraceVideo extends AbstractTrace
{
    public const TYPE = 'video';

    public const FORM = TraceVideoType::class;

    public const FORM_TEMPLATE = 'trace/form_types/_form_video.html.twig';

    public const ICON = 'bi:youtube';

    public const CONSTRAINT = 'La vidéo doit être hébergée sur YouTube';

    public const CLASS_NAME = self::class;

    public ?string $name = 'video';

    public array $options = [];

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('form', $this::FORM)
            ->setDefault('type_trace', $this->name);
    }

    public function display(): string
    {
        return self::TYPE;
    }

    public function sauvegarde(?array $contenu,
                               ?array $existingContenu
    ): array
    {
        if ($existingContenu) {
            $contenu = array_merge($contenu, $existingContenu);
        }

        if ($contenu) {
            foreach ($contenu as $key => $video) {
                $youtubeId = null;
                if (
                    preg_match('/^(https?:\/\/)?(www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $video, $matches)
                    || preg_match('/^(https?:\/\/)?(www\.)?youtu\.be\/([a-zA-Z0-9_-]+)/', $video, $matches)
                ) {
                    $youtubeId = $matches[3];
                }

                if ($youtubeId) {
                    // Construire le lien embed à partir de l'ID
                    $Embedcontenu = 'https://www.youtube.com/embed/' . $youtubeId;

                    // Remplacer le lien original par le lien embed dans le tableau des video
                    $contenu[$key] = $Embedcontenu;
                }

                // Vérification des liens non youtube
                if (!$youtubeId && !preg_match('/^(https?:\/\/)?(www\.)?youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $video)) {
                    $error = 'Le lien n\'est pas un lien YouTube valide';
                    return array('success' => false, 'error' => $error);
                }
            }
        } else {
            return ['success' => false, 'error' => 'Le contenu est vide'];
        }

        return ['success' => true, 'contenu' => $contenu];
    }
}