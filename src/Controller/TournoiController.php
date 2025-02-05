<?php

namespace App\Controller;

use App\Entity\Tournoi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tournois')]
class TournoiController extends AbstractController
{
    #[Route('/', name: 'tournois_list')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $tournois = $entityManager->getRepository(Tournoi::class)->findAll();

        return $this->render('tournoi/index.html.twig', [
            'tournois' => $tournois, 
        ]);
    }
}
