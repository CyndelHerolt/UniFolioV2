<?php

/*
 * Copyright (c) 2023. | Cyndel Herolt | IUT de Troyes  - All Rights Reserved
 * @author cyndelherolt
 * @project UniFolio
 */

namespace App\Controller\Admin;

use App\Entity\Departement;
use App\Entity\Etudiant;
use App\Entity\Groupe;
use App\Entity\Semestre;
use App\Entity\User;
use App\Repository\DepartementRepository;
use App\Repository\EtudiantRepository;
use App\Repository\GroupeRepository;
use App\Repository\SemestreRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\InsufficientEntityPermissionException;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

class EtudiantCrudController extends AbstractCrudController
{
    private $entityManager;

    public function __construct(
        EntityManagerInterface      $entityManager,
        protected UserPasswordHasherInterface $passwordHasher,
        protected DepartementRepository       $departementRepository,
        protected EtudiantRepository          $etudiantRepository,
        protected SemestreRepository          $semestreRepository,
        protected GroupeRepository            $groupeRepository,
    )
    {
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return Etudiant::class;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName == Crud::PAGE_INDEX) {
            yield TextField::new('prenom', 'Prénom');
            yield TextField::new('nom', 'Nom');
            yield TextField::new('semestre.annee.diplome.departement.libelle', 'Département');
            yield TextField::new('semestre.libelle', 'Semestre');
            yield TextField::new('groupesAsString', 'Groupes');
            yield TextField::new('mail_univ', 'Mail universitaire');
            yield TextField::new('mail_perso', 'Mail personnel');
            yield TextField::new('username', 'Identifiant');
            yield TextField::new('bio', 'Bio');
            yield IntegerField::new('anneeSortie', 'Année de sortie');
        } elseif ($pageName == Crud::PAGE_EDIT) {
            // User
            yield FormField::addPanel('Informations User')->setIcon('fa fa-user');
            yield TextField::new('users.username', 'Username')
                ->setFormTypeOption('required', 'true');
            yield TextField::new('users.password', 'Password')
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('required', 'true');
            yield ChoiceField::new('users.roles', 'Rôles')
                ->setChoices(['ETUDIANT' => 'ROLE_ETUDIANT', 'ADMIN' => 'ROLE_ADMIN', 'TEST' => 'ROLE_TEST'])
                ->allowMultipleChoices()
                ->setRequired(true)
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('label', 'Rôles')
                ->setFormTypeOption('help', 'Sélectionner tous les rôles qui s\'appliquent à cet utilisateur.')
                ->setFormTypeOption('attr', ['class' => 'form-control'])
                ->setFormTypeOption('choice_attr', ['class' => 'form-check-inline']);

            // Etudiant
            yield FormField::addPanel('Informations Etudiant')->setIcon('fa fa-user-graduate');
            yield TextField::new('prenom', 'Prénom')
                ->setFormTypeOption('required', 'true');
            yield TextField::new('nom', 'Nom')
                ->setFormTypeOption('required', 'true');
            yield TextField::new('mail_perso', 'Mail personnel')
                ->setFormType(EmailType::class);
            yield TextField::new('mail_univ', 'Mail universitaire')
                ->setFormTypeOption('required', 'true')
                ->setFormType(EmailType::class);

            //Structure
            yield FormField::addPanel('Informations Structure')->setIcon('fa fa-school');
            yield TextField::new('semestre.annee.diplome.departement.libelle', 'Département')
                ->setFormTypeOption('disabled', 'true');
            yield TextField::new('semestre.libelle', 'Semestre')
                ->setFormTypeOption('disabled', 'true');
            yield TextField::new('groupesAsString', 'Groupes')
                ->setFormTypeOption('disabled', 'true');
        }
        if ($pageName == Crud::PAGE_NEW) {
            // User
            yield FormField::addPanel('Informations User')->setIcon('fa fa-user');
            yield TextField::new('username')
                ->setFormTypeOption('required', 'true');
            yield TextField::new('password')
                ->setFormTypeOption('mapped', false)
                ->setFormTypeOption('required', 'true');
            yield ChoiceField::new('roles')
                ->setChoices(['ETUDIANT' => 'ROLE_ETUDIANT', 'ADMIN' => 'ROLE_ADMIN', 'TEST' => 'ROLE_TEST'])
                ->allowMultipleChoices()
                ->setRequired(true)
                ->setFormTypeOption('mapped', false)
                ->setFormTypeOption('label', 'Rôles')
                ->setFormTypeOption('help', 'Veuillez sélectionner tous les rôles qui s\'appliquent à cet utilisateur.')
                ->setFormTypeOption('attr', ['class' => 'form-control'])
                ->setFormTypeOption('choice_attr', ['class' => 'form-check-inline']);

            // Etudiant
            yield FormField::addPanel('Informations Etudiant')->setIcon('fa fa-user-graduate');
            yield TextField::new('prenom', 'Prénom')
                ->setFormTypeOption('required', 'true');
            yield TextField::new('nom', 'Nom')
                ->setFormTypeOption('required', 'true');
            yield TextField::new('mail_perso', 'Mail personnel')
                ->setFormType(EmailType::class);
            yield TextField::new('mail_univ', 'Mail universitaire')
                ->setFormTypeOption('required', 'true')
                ->setFormType(EmailType::class);

            //todo: à voir, plus simple gestion depuis les éléments de structures ? (département, semestre, groupe)
//            //Structure
//            yield FormField::addPanel('Informations Structure')->setIcon('fa fa-school');
//            // récupérer les départements
//            $departementsObjets = $this->departementRepository->findAll();
//            foreach ($departementsObjets as $departement) {
//                $departements[] = $departement->getLibelle();
//            }
//            // inverser les clés et les valeurs
//            $departements = array_flip($departements);
//            yield ChoiceField::new('departement', 'Département')
//                ->setChoices($departements)
//                ->setFormTypeOption('mapped', true)
//                ->setFormTypeOption('required', true)
//                ->setFormTypeOption('label', 'Département')
//                ->setFormTypeOption('attr', ['class' => 'form-control'])
//                ->setFormTypeOption('data', $departement->getLibelle())
//                ->setFormTypeOption('choice_attr', ['class' => 'form-check-inline']);
//            $semestresObjets = $this->semestreRepository->findBy(['actif' => true]);
//            foreach ($semestresObjets as $semestre) {
//                $semestres[] = $semestre->getLibelle();
//            }
//            $semestres = array_flip($semestres);
//            yield ChoiceField::new('semestreId')
//                ->setFormTypeOption('mapped', true)
//                ->setFormTypeOption('required', true)
//                ->setFormTypeOption('label', 'Semestre')
//                ->setChoices($semestres)
//                ->setFormTypeOption('attr', ['class' => 'form-control'])
//                ->setFormTypeOption('choice_attr', ['class' => 'form-check-inline']);
//            $groupesObjets = [];
//            foreach ($semestresObjets as $semestre) {
//                $groupesObjets[] = $this->groupeRepository->findBySemestre($semestre);
//            }
//            foreach ($groupesObjets as $groupe) {
//                foreach ($groupe as $g) {
//                    $groupes[] = $semestre->getLibelle() . '-' . $g->getLibelle();
//                }
//            }
//            $groupes = array_flip($groupes);
//            yield ChoiceField::new('groupeId')
//                ->setFormTypeOption('mapped', true)
//                ->allowMultipleChoices()
//                ->setFormTypeOption('required', 'true')
//                ->setFormTypeOption('label', 'Groupe')
//                ->renderAsBadges()
//                ->setChoices($groupes)
//                ->setFormTypeOption('attr', ['class' => 'form-control'])
//                ->setFormTypeOption('choice_attr', ['class' => 'form-check-inline']);
        }
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Etudiant.e')
            ->setEntityLabelInPlural('Etudiant.es')
            ->showEntityActionsInlined()
            ->setPaginatorPageSize(20)
            ->setPageTitle('index', 'Liste des étudiant.es')
            ->setPageTitle('new', 'Ajouter un.e étudiant.e')
            ->setPageTitle('edit', 'Modifier un.e étudiant.e');
    }

    public function new(AdminContext $context): Response|KeyValueStore
    {
        $event = new BeforeCrudActionEvent($context);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::NEW, 'entity' => null])) {
            throw new ForbiddenActionException($context);
        }

        if (!$context->getEntity()->isAccessible()) {
            throw new InsufficientEntityPermissionException($context);
        }

        $context->getEntity()->setInstance($this->createEntity($context->getEntity()->getFqcn()));
        $this->container->get(EntityFactory::class)->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_NEW)));
        $context->getCrud()->setFieldAssets($this->getFieldAssets($context->getEntity()->getFields()));
        $this->container->get(EntityFactory::class)->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());

        $newForm = $this->createNewForm($context->getEntity(), $context->getCrud()->getNewFormOptions(), $context);
        $newForm->handleRequest($context->getRequest());

        $entityInstance = $newForm->getData();
        $context->getEntity()->setInstance($entityInstance);

        if ($newForm->isSubmitted() && $newForm->isValid()) {
            $this->processUploadedFiles($newForm);

            $event = new BeforeEntityPersistedEvent($entityInstance);
            $this->container->get('event_dispatcher')->dispatch($event);
            $entityInstance = $event->getEntityInstance();

            $user = new User();
            $departement = $context->getRequest()->request->all('Etudiant')['departement'];
            $departement = $this->entityManager->getRepository(Departement::class)->find($departement);

            $semestre = $context->getRequest()->request->all('Etudiant')['semestreId'];
            $semestre = $this->entityManager->getRepository(Semestre::class)->find($semestre);

            // Récupérer les groupes
            $groupes = $context->getRequest()->request->all('Etudiant')['groupeId'];
            $groupes = $this->entityManager->getRepository(Groupe::class)->findBy(['id' => $groupes]);

            // password hashing
            $plaintextPassword = $context->getRequest()->request->all('Etudiant')['password'];

            // hash the password (based on the security.yaml config for the $user class)
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );


            $user->setUsername($context->getRequest()->request->all('Etudiant')['username']);
            $user->setPassword($hashedPassword);
            $user->setRoles($context->getRequest()->request->all('Etudiant')['roles']);
            $user->setIsVerified(true);

            $etudiant = new Etudiant();
            $etudiant->setUsername($context->getRequest()->request->all('Etudiant')['username']);
            $etudiant->setPrenom($context->getRequest()->request->all('Etudiant')['prenom']);
            $etudiant->setNom($context->getRequest()->request->all('Etudiant')['nom']);
            $etudiant->setMailPerso($context->getRequest()->request->all('Etudiant')['mail_perso']);
            $etudiant->setMailUniv($context->getRequest()->request->all('Etudiant')['mail_univ']);
            $etudiant->setSemestre($semestre);
            foreach ($groupes as $groupe) {
                $etudiant->addGroupe($groupe);
            }

            $user->setEtudiant($etudiant);

            $usersRepository = $this->entityManager->getRepository(User::class);
            $etudiantRepository = $this->entityManager->getRepository(Etudiant::class);

            $usersRepository->save($user, true);
            $etudiantRepository->save($etudiant, true);

            $this->container->get('event_dispatcher')->dispatch(new AfterEntityPersistedEvent($entityInstance));
            $context->getEntity()->setInstance($entityInstance);

            return $this->getRedirectResponseAfterSave($context, Action::NEW);
        }

        $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
            'pageName' => Crud::PAGE_NEW,
            'templateName' => 'crud/new',
            'entity' => $context->getEntity(),
            'new_form' => $newForm,
        ]));

        $event = new AfterCrudActionEvent($context, $responseParameters);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        return $responseParameters;
    }

    public function edit(AdminContext $context): Response|KeyValueStore
    {
        $event = new BeforeCrudActionEvent($context);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::EDIT, 'entity' => $context->getEntity()])) {
            throw new ForbiddenActionException($context);
        }

        if (!$context->getEntity()->isAccessible()) {
            throw new InsufficientEntityPermissionException($context);
        }

        $this->container->get(EntityFactory::class)->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_EDIT)));
        $context->getCrud()->setFieldAssets($this->getFieldAssets($context->getEntity()->getFields()));
        $this->container->get(EntityFactory::class)->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());
        $entityInstance = $context->getEntity()->getInstance();

        if ($context->getRequest()->isXmlHttpRequest()) {
            if ('PATCH' !== $context->getRequest()->getMethod()) {
                throw new MethodNotAllowedHttpException(['PATCH']);
            }

            if (!$this->isCsrfTokenValid(BooleanField::CSRF_TOKEN_NAME, $context->getRequest()->query->get('csrfToken'))) {
                if (class_exists(InvalidCsrfTokenException::class)) {
                    throw new InvalidCsrfTokenException();
                } else {
                    return new Response('Invalid CSRF token.', 400);
                }
            }

            $fieldName = $context->getRequest()->query->get('fieldName');
            $newValue = 'true' === mb_strtolower($context->getRequest()->query->get('newValue'));

            try {
                $event = $this->ajaxEdit($context->getEntity(), $fieldName, $newValue);
            } catch (\Exception) {
                throw new BadRequestHttpException();
            }

            if ($event->isPropagationStopped()) {
                return $event->getResponse();
            }

            return new Response($newValue ? '1' : '0');
        }


        $editForm = $this->createEditForm($context->getEntity(), $context->getCrud()->getEditFormOptions(), $context);
        $editForm->handleRequest($context->getRequest());
        if ($editForm->isSubmitted() && $editForm->isValid()) {
//            dd($context->getRequest()->request->all('Etudiant')['roles']);

            $this->processUploadedFiles($editForm);

            $event = new BeforeEntityUpdatedEvent($entityInstance);
            $this->container->get('event_dispatcher')->dispatch($event);
            $entityInstance = $event->getEntityInstance();

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $context->getRequest()->request->all('Etudiant')['username']]);

            $user->setUsername($context->getRequest()->request->all('Etudiant')['username']);
            $user->setRoles($context->getRequest()->request->all('Etudiant')['roles']);

            $usersRepository = $this->entityManager->getRepository(User::class);
            $usersRepository->save($user, true);


            $this->updateEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

            $this->container->get('event_dispatcher')->dispatch(new AfterEntityUpdatedEvent($entityInstance));

            return $this->getRedirectResponseAfterSave($context, Action::EDIT);
        }

        $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
            'pageName' => Crud::PAGE_EDIT,
            'templateName' => 'crud/edit',
            'edit_form' => $editForm,
            'entity' => $context->getEntity(),
        ]));

        $event = new AfterCrudActionEvent($context, $responseParameters);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        return $responseParameters;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
    }
}
