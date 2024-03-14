<?php

namespace App\Form;

use App\Entity\Cv;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('intitule', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un titre',
                    ]),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
                'label' => 'Titre',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => "form-control",
                    'placeholder' => 'Titre du CV',
                ],
                'help' => '100 caractères maximum',
                'required' => true,
            ])
            ->add('email', TextType::class, [
                'label' => 'Adresse mail de contact',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => "form-control",
                    'placeholder' => 'machin@exemple.com',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Accroche',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'tinymce form-control', 'placeholder' => '...', 'rows' => 3, 'id' => 'trace_abstract_description'],
                'help' => 'Rédigez une courte accroche.',
                'mapped' => true,
                'required' => false,
            ])
            //----------------------------------------------------------------
            ->add('soft_skills', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => "form-control soft_skills",
                        'placeholder' => 'exemple',
                    ],
                    'by_reference' => false,
                    'label' => false,
                    'label_attr' => ['class' => 'form-label'],
                ],
                'prototype' => true,
                'label' => 'Soft skills',
                'allow_extra_fields' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
                'by_reference' => false,
                'empty_data' => [],
            ])
            //----------------------------------------------------------------
            ->add('hard_skills', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => "form-control hard_skills",
                        'placeholder' => 'exemple',
                    ],
                    'by_reference' => false,
                    'label_attr' => ['class' => 'form-label'],
                    'label' => false,
                ],
                'prototype' => true,
                'label' => 'Hard skills',
                'allow_extra_fields' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
                'by_reference' => false,
                'empty_data' => [],
                'help' => 'compétences techniques'
            ])
            //----------------------------------------------------------------
            ->add('langues', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => "form-control langues",
                        'placeholder' => 'Langues',
                    ],
                    'by_reference' => false,
                    'label' => false,
                    'label_attr' => ['class' => 'form-label'],
                ],
                'prototype' => true,
                'label' => 'Langues',
                'allow_extra_fields' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
                'by_reference' => false,
                'empty_data' => [],
                'help' => 'Langues parlées'
            ])
            //----------------------------------------------------------------
            ->add('reseaux', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => "form-control reseaux",
                        'placeholder' => 'Mes reseaux',
                    ],
                    'by_reference' => false,
                    'label' => false,
                    'label_attr' => ['class' => 'form-label'],
                ],
                'prototype' => true,
                'label' => false,
                'allow_extra_fields' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
                'by_reference' => false,
                'empty_data' => [],
            ])
            //----------------------------------------------------------------
            ->add('experiences', CollectionType::class, [
                'entry_type' => ExperienceType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => "form-control experience",
                    ],
                    'by_reference' => false,
                    'label' => false,
                    'label_attr' => ['class' => 'form-label'],
                    'help' => '',
                ],
                'prototype' => true,
                'label' => 'Experience',
                'allow_extra_fields' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
                'by_reference' => false,
                'empty_data' => [],
            ])
            //----------------------------------------------------------------
            ->add('formations', CollectionType::class, [
                'entry_type' => FormationType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => "form-control formation",
                    ],
                    'by_reference' => false,
                    'label' => false,
                    'label_attr' => ['class' => 'form-label'],
                    'help' => '',
                ],
                'prototype' => true,
                'label' => 'Formation',
                'allow_extra_fields' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
                'by_reference' => false,
                'empty_data' => [],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cv::class,
        ]);
    }
}
