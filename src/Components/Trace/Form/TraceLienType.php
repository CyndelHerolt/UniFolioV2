<?php

/*
 * Copyright (c) 2023. | Cyndel Herolt | IUT de Troyes  - All Rights Reserved
 * @author cyndelherolt
 * @project UniFolio
 */

namespace App\Components\Trace\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Count;

class TraceLienType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contenu', CollectionType::class, [
                'entry_type' => TextType::class,
                'constraints' => [
                    new Count([
                        'min' => 1,
                        'minMessage' => 'Veuillez ajouter au moins un lien',
                    ]),
                ],
                'entry_options' => [
                    'attr' => [
                        'class' => "form-control lien_trace",
                        'style' => 'width: fit-content',
                        'placeholder' => 'Lien',
                    ],
                    'by_reference' => false,
                    'label' => false,
                    'help' => 'Format du lien : https://www.exemple.com',
                ],
                'prototype' => true,
                'label' => false,
                'allow_extra_fields' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'empty_data' => [],
            ]);
    }
}
