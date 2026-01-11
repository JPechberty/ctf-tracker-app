<?php

namespace App\Controller\Admin;

use App\Entity\Challenge;
use App\Entity\Flag;
use App\Entity\Submission;
use App\Entity\Team;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(ChallengeCrudController::class)->generateUrl());
    }

    public function configureDashboard(): \EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard
    {
        return \EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard::new()
            ->setTitle('CTF Tracker - Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield \EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield \EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem::linkToCrud('Challenges', 'fa fa-trophy', Challenge::class);
        yield \EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem::linkToCrud('Flags', 'fa fa-flag', Flag::class);
        yield \EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem::linkToCrud('Teams', 'fa fa-users', Team::class);
        yield \EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem::linkToCrud('Submissions', 'fa fa-paper-plane', Submission::class);
    }
}
