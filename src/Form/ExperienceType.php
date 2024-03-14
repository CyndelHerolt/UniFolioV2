<?php

namespace App\Form;

use App\Entity\Cv;
use App\Entity\Experience;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperienceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('poste', TextType::class, [
                'label' => 'Poste',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => "form-control", 'placeholder' => 'Poste occupé',],
                'help' => 'Poste occupé dans l\'entreprise',
                'required' => false,
            ])
            ->add('entreprise', TextType::class, [
                'label' => 'Entreprise',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => "form-control", 'placeholder' => 'Entreprise d\'accueil',],
                'help' => 'Nom de l\'entreprise',
                'required' => false,
            ])
            ->add('date_debut', DateType::class, [
                'label' => 'Date de début',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => "form-control", 'placeholder' => 'Date de prise de poste',],
                'help' => 'Format attendu : 01/01/2001',
                'widget' => 'single_text',
                'input_format' => 'd/m/Y',
                'required' => false,
            ])
            ->add('date_fin', DateType::class, [
                'label' => 'Date de fin',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => "form-control", 'placeholder' => 'Date de départ de l\'entreprise',],
                'help' => 'Format attendu : 01/01/2001',
                'widget' => 'single_text',
                'input_format' => 'd/m/Y',
                'required' => false,
            ])
            //----------------------------------------------------------------
            ->add('activites', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => "form-control experience-activite",
                        'placeholder' => 'Activités de mon expérience',
                    ],
                    'by_reference' => false,
                    'label' => false,
                    'label_attr' => ['class' => 'form-label'],
                ],
                'attr' => ['class' => 'exp-activite'],
                'prototype' => true,
                'label' => 'Activités',
                'allow_extra_fields' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'empty_data' => [],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Experience::class,
        ]);
    }
}
