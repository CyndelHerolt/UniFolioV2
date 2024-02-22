<?php

namespace App\Components\Trace\Form;

use App\Entity\Trace;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Dropzone\Form\DropzoneType;

class TraceImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contenu', CollectionType::class, [
                'entry_type' => DropzoneType::class,
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trace::class,
        ]);
    }
}
