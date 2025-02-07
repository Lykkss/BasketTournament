<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Tournoi;
use App\Entity\Equipe;
use App\Entity\Game;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

class AdminDashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }
    
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(TournoiCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('🏀 BasketTournament - Administration');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            
            MenuItem::section('Gestion des utilisateurs'),
            MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class),

            MenuItem::section('Gestion des compétitions'),
            MenuItem::linkToCrud('Tournois', 'fa fa-trophy', Tournoi::class),
            MenuItem::linkToCrud('Équipes', 'fa fa-users', Equipe::class),
            MenuItem::linkToCrud('Matchs', 'fa fa-basketball-ball', Game::class),

            MenuItem::section('Paramètres'),
            MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out'),
        ];
    }
}
