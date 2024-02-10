<?php

/*
 * Copyright (c) 2023. | Cyndel Herolt | IUT de Troyes  - All Rights Reserved
 * @author cyndelherolt
 * @project UniFolio
 */
namespace App\Controller\Admin;

use App\Entity\AnneeUniversitaire;
use App\Entity\Commentaire;
use App\Entity\Enseignant;
use App\Entity\Etudiant;
use App\Entity\Groupe;
use App\Entity\Page;
use App\Entity\Portfolio;
use App\Entity\Semestre;
use App\Entity\Trace;
use App\Entity\Users;
use App\Entity\Validation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
//        return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        return $this->render('Admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('UniFolio')
            ->setTranslationDomain('admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::linkToUrl('Site étudiant', 'fas fa-user', '/dashboard?_switch_user=etudiant');
        yield MenuItem::linkToUrl('Site enseignant', 'fas fa-user', '/dashboard?_switch_user=enseignant');
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-arrow-right-from-bracket');

        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToCrud('Gestion des etudiants', 'fas fa-list', Etudiant::class);
        yield MenuItem::linkToCrud('Gestion des enseignants', 'fas fa-list', Enseignant::class);

        yield MenuItem::section('Portfolios');
//        yield MenuItem::linkToCrud('Gestion des traces', 'fas fa-list', Trace::class);
//        yield MenuItem::linkToCrud('Gestion des pages', 'fas fa-list', Page::class);
//        yield MenuItem::linkToCrud('Gestion des portfolios', 'fas fa-list', Portfolio::class);
//        yield MenuItem::linkToCrud('Gestion des commentaires', 'fas fa-list', Commentaire::class);
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->addMenuItems([
//                MenuItem::LinkToRoute('Mon profil', 'fas fa-user', 'app_profil'),
            ]);
    }
}
