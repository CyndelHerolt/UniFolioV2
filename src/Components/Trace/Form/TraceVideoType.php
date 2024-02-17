<?php

namespace App\Components\Trace\Form;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Count;

class TraceVideoType extends TraceAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

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
                        'class' => "form-control video_trace",
                        'placeholder' => 'lien de la vidéo',
                    ],
                    'by_reference' => false,
                    'label' => false,
                    'help' => 'La vidéo doit être hébergée sur YouTube',
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
            ]);
    }
}