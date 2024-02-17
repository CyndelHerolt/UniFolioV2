<?php

namespace App\Components\Trace\TypeTrace;



use App\Components\Trace\Form\TraceAbstractType;
use App\Repository\TraceRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbstractTrace
{
    public const TYPE = 'abstract';
    public const FORM = TraceAbstractType::class;

    public ?string $name = 'abstract';

    public array $options = [];

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('form', $this::FORM)
            ->setDefault('type_trace', $this->name);
    }
    public function getOptions()
    {
        return $this->options;
    }

    public function getOption(string $name)
    {
        return $this->options[$name];
    }

}