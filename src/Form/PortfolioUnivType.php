<?php

namespace App\Form;

use App\Entity\Annee;
use App\Entity\Etudiant;
use App\Entity\PortfolioUniv;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\UX\Dropzone\Form\DropzoneType;

class PortfolioUnivType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
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
                    'placeholder' => 'Titre du portfolio',
                ],
                'help' => '100 caractères maximum',
                'required' => true,
            ])
            ->add('banniere', DropzoneType::class, [
                'label' => 'Bannière',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => "form-control banner-dropzone",
                ],
                'help' => 'Image de couverture du portfolio',
                'required' => false,
                'mapped' => false,
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir votre description',
                    ]),
                ],
                'label' => 'Description',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Mon portfolio universitaire regroupe l\'ensemble de mes travaux, tous liés par l\'intermédiaire des compétences universitaires de ma formation.', 'rows' => 3, 'id' => 'trace_abstract_description'],
                'help' => 'Un court paragraphe d\'introduction à votre portfolio',
                'mapped' => true,
                'required' => false,
            ])
            ->add('visibilite', ChoiceType::class, [
                'choices' => [
                    'Public' => true,
                    'Privé' => false,
                ],
                'label' => 'Visibilité',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => "form-control"],
                'help' => 'Un portfolio public sera visible des enseignants alors qu\'un portfolio privé ne sera visible que par vous-même',
                'required' => true,
                'mapped' => true,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('optSearch', ChoiceType::class, [
                'choices' => [
                    'Afficher' => true,
                    'Masquer' => false,
                ],
                'label' => 'Option alternance ou stage',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => "form-control"],
                'mapped' => true,
                'help' => 'Signaler sur votre portfolio que vous êtes à la recherche d\'une alternance ou d\'un stage',
                'expanded' => true,
                'multiple' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PortfolioUniv::class,
        ]);
    }
}
