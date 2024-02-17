<?php

namespace App\Components\Trace\Form;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class TraceImageType extends TraceAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('contenu', CollectionType::class, [
                'entry_type' => FileType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => "form-control image_trace",
                        'accept' => 'jpg, jpeg, png, gif, svg, webp'
                    ],
                    'data_class' => null,
                    'by_reference' => false,
                    'label' => false,
                    'help' => 'formats acceptés : jpg, jpeg, png, gif, svg, webp',
                ],
                'prototype' => true,
                'label' => false,
                'allow_extra_fields' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'empty_data' => [],
                'mapped' => true,
                'data' => [],
            ]);
    }
}
