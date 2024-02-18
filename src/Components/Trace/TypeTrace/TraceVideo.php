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

    public const ICON = 'fa-solid fa-3x fa-youtube';

    public const CONSTRAINT = 'La vidéo doit être hébergée sur YouTube';


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
}