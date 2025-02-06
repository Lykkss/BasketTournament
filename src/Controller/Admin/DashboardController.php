<?php 

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use App\Entity\Tournoi;
use App\Entity\Game;
use App\Entity\Equipe;
use App\Entity\User;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig'); // Vérifie bien ce fichier
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Admin Dashboard');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            MenuItem::linkToCrud('Tournois', 'fas fa-trophy', Tournoi::class),
            MenuItem::linkToCrud('Matchs', 'fas fa-basketball-ball', Game::class),
            MenuItem::linkToCrud('Équipes', 'fas fa-users', Equipe::class),
            MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', User::class),
        ];
    }
}
