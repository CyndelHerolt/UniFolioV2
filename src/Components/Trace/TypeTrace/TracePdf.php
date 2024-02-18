<?php

namespace App\Components\Trace\TypeTrace;

use App\Components\Trace\Form\TracePdfType;
use App\Components\Trace\TypeTrace\AbstractTrace;
use App\Repository\TraceRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TracePdf extends AbstractTrace
{
    public const TYPE = 'pdf';

    public const FORM = TracePdfType::class;

    public const ICON = 'fa-solid fa-3x fa-file-pdf';

    public const CONSTRAINT = 'Poids maximum : 8 Mo';

    public ?string $name = 'pdf';

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
}