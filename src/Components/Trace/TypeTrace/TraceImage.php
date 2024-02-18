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

    public const ICON = 'bi bi-image';

    public const CONSTRAINT = 'Formats acceptés : jpg, jpeg, png, gif, svg, webp';

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
}