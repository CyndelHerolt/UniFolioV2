<?php

namespace App\Components\Trace\TypeTrace;

use App\Components\Trace\Form\TraceLienType;
use App\Components\Trace\TypeTrace\AbstractTrace;
use App\Repository\TraceRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraceLien extends AbstractTrace
{
    public const TYPE = 'lien';

    public const FORM = TraceLienType::class;

    public const ICON = 'fa-solid fa-3x fa-link';

    public const CONSTRAINT = 'Format : https://www.exemple.com';

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
}