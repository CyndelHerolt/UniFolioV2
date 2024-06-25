<?php

namespace App\Components\Trace\TypeTrace;

use App\Components\Trace\Form\TraceLienType;
use App\Entity\Trace;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraceLien extends AbstractTrace
{
    public const TYPE = 'lien';

    public const FORM = TraceLienType::class;

    public const FORM_TEMPLATE = 'trace/form_types/_form_lien.html.twig';

    public const ICON = 'bi:link-45deg';

    public const CONSTRAINT = 'Format : https://www.exemple.com';

    public const CLASS_NAME = self::class;

    public ?string $name = 'lien';

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

    public function sauvegarde(?array $contenu, ?array $existingContenu): array
    {
        if ($contenu) {
            foreach ($contenu as $lien) {
                if (!filter_var($lien, FILTER_VALIDATE_URL)) {
                    return ['success' => false, 'error' => 'Le contenu n\'est pas un lien valide'];
                }
            }
        } else {
            return ['success' => false, 'error' => 'Le contenu est vide'];
        }

        return ['success' => true, 'contenu' => $contenu];
    }
}