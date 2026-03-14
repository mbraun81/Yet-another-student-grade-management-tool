<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        //return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        //return $this->redirectToRoute('admin_user_index');

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        //return $this->render('dashboard/index.html.twig');
        return $this->redirectToRoute('admin_lehrer_index');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Yet Another Student Grade Management Tool');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        
        yield MenuItem::linkTo(FachCrudController::class, 'Fach', 'fas fa-list');
        yield MenuItem::linkTo(KlasseCrudController::class, 'Klasse', 'fas fa-list');

        yield MenuItem::linkTo(LehrerCrudController::class, 'Lehrer', 'fas fa-list');
        yield MenuItem::linkTo(SchuelerCrudController::class, 'Schüler', 'fas fa-list');

        yield MenuItem::linkTo(KompetenzCrudController::class, 'Kompetenz', 'fas fa-list');
        yield MenuItem::linkTo(KompetenzrasterCrudController::class, 'Raster', 'fas fa-list');
    }
}
