<?php

namespace App\Components\Trace\TypeTrace;

use App\Components\Trace\Form\TraceImageType;
use App\Components\Trace\TypeTrace\AbstractTrace;
use App\Repository\TraceRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraceImage extends AbstractTrace
{
    public const TYPE = 'image';

    public const FORM = TraceImageType::class;

    public const FORM_TEMPLATE = 'trace/form_types/_form_image.html.twig';
    public const ICON = 'bi:image';

    public const CONSTRAINT = 'Formats acceptés : jpg, jpeg, png, gif, svg, webp';

    public const CLASS_NAME = self::class;

    public ?string $name = 'image';

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
        $max_size = 2 * 1024 * 1024; // 2 Mo en octets

        if ($existingContenu) {
            $contenu = array_merge($contenu, $existingContenu);
        }

        if ($contenu) {
            foreach ($contenu as $image) {
                if (!in_array($image->guessExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                    return ['success' => false, 'error' => 'Le contenu n\'est pas une image valide'];
                } elseif (filesize($image) > $max_size) {
                    return ['success' => false, 'error' => 'Le fichier doit faire 2mo maximum'];
                }
                    $fileName = uniqid() . '.' . $image->guessExtension();
                    $image->move($_ENV['PATH_FILES'], $fileName);
                    $content[] = $_ENV['SRC_FILES'] . '/' . $fileName;
            }
        } else {
            return ['success' => false, 'error' => 'Le contenu est vide'];
        }

        return ['success' => true, 'contenu' => $content];
    }
}