<?php

namespace App\Components\Trace\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class TracePdfType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contenu', CollectionType::class, [
                'entry_type' => FileType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => "form-control pdf-trace",
                        'accept' => 'pdf',
                    ],
                    'by_reference' => false,
                    'label' => false,
                    'help' => 'format accepté : pdf',
                ],
                'prototype' => true,
                'label' => false,
                'allow_extra_fields' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'mapped' => true,
                'by_reference' => false,
                'empty_data' => [],
                'data' => [],
            ]);
    }
}