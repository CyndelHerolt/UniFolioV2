<?php

namespace App\Components\Trace\Form;

use App\Entity\Trace;
use App\Repository\BibliothequeRepository;
use App\Repository\TraceRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Service\Attribute\Required;

class TraceAbstractType extends AbstractType
{
    public function __construct(
        protected TraceRepository     $traceRepository,
        public BibliothequeRepository $bibliothequeRepository,
        #[Required] public Security   $security
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $competences = $options['competences'];

        $builder
            ->add('date_creation', DateTimeType::class, [
                'data' => new \DateTimeImmutable(),
                'widget' => 'single_text',
                'disabled' => true,
                'label' => ' ',
            ])
            ->add('date_modification', DateTimeType::class, [
                'data' => new \DateTimeImmutable(),
                'widget' => 'single_text',
                'disabled' => true,
                'label' => ' ',
            ])
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
                    'placeholder' => 'Titre de ma trace',
                ],
                'help' => '100 caractères maximum',
                'required' => true,
            ])
            //----------------------------------------------------------------
            ->add('legende', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir une légende',
                    ]),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'La légende ne peut pas dépasser 100 caractères',
                    ]),
                ],
                'label' => 'Légende',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => "form-control", 'placeholder' => '...'],
                'help' => 'Description de votre média, 100 caractères maximum',
                'required' => true,
            ])
            //----------------------------------------------------------------
            ->add('dateRealisation', DateType::class, [
                'data' => new \DateTimeImmutable(),
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir une date',
                    ])
                ],
                'format' => 'MM-yyyy',
                'widget' => 'single_text',
                'label' => 'Date de réalisation',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => "form-control", 'id' => 'dateRealisation'],
                'help' => 'Date à laquelle vous avez réalisé cette trace. A saisir au format mm-YYYY',
                'required' => true,
                'html5' => false,
            ])
            //----------------------------------------------------------------
            ->add('contexte', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un contexte',
                    ]),
                ],
                'label' => 'Contexte',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => "form-control", 'placeholder' => '...'],
                'help' => 'Le contexte dans lequel vous avez réalisé cette trace (SAE, projet personnel, en groupe, en solo ...)',
                'required' => true,
            ])
            //----------------------------------------------------------------
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir votre commentaire',
                    ]),
                ],
                'label' => 'Commentaire',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'tinymce form-control', 'placeholder' => '...', 'rows' => 10],
                'help' => 'Commentez votre trace pour justifier sa pertinence',
                'mapped' => true,
                'required' => true,
            ])
            //----------------------------------------------------------------
            ->add('competences', ChoiceType::class, [
                'constraints' => [
                    new Count([
                        'min' => 1,
                        'minMessage' => 'Veuillez sélectionner au moins une compétence',
                    ]),
                ],
                'choices' => array_combine($competences, $competences),
                'label' => false,
                'multiple' => true,
                'required' => true,
                'expanded' => true,
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trace::class,
            'user' => null,
            'competences' => null,
        ]);
    }
}