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

        if (!$tournois) {
            $this->addFlash('warning', 'Aucun tournoi trouvé.');
        }

        return $this->render('tournoi/index.html.twig', [
            'tournois' => $tournois,
        ]);
    }

    #[Route('/{id}', name: 'tournoi_show', requirements: ['id' => '\d+'])]
        public function show(Tournoi $tournoi): Response
            {       
                return $this->render('tournoi/show.html.twig', [
                    'tournoi' => $tournoi,
                ]);
            }
}


