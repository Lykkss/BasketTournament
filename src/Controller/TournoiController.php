<?php 

namespace App\Controller;

use App\Entity\Tournoi;
use App\Entity\Game;
use App\Form\TournoiType;
use App\Form\GameType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tournois')]
class TournoiController extends AbstractController
{
    #[Route('/', name: 'tournois_list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $status = $request->query->get('status', 'Tous'); // Valeur par défaut
        $queryBuilder = $entityManager->getRepository(Tournoi::class)->createQueryBuilder('t');

        if ($status !== 'Tous') {
            $queryBuilder->where('t.status = :status')->setParameter('status', $status);
        }

        $tournois = $queryBuilder->getQuery()->getResult();

        return $this->render('tournoi/index.html.twig', [
            'tournois' => $tournois,
            'status' => $status,
        ]);
    }

    #[Route('/new', name: 'tournoi_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tournoi = new Tournoi();
        $form = $this->createForm(TournoiType::class, $tournoi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($tournoi->getNbMaxEquipes() === null) {
                $tournoi->setNbMaxEquipes(4); // Valeur par défaut
            }

            $entityManager->persist($tournoi);
            $entityManager->flush();

            $this->addFlash('success', 'Tournoi créé avec succès !');
            return $this->redirectToRoute('tournois_list');
        }

        return $this->render('tournoi/new.html.twig', [
            'tournoi' => $tournoi,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'tournoi_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Tournoi $tournoi): Response
    {
        return $this->render('tournoi/show.html.twig', [
            'tournoi' => $tournoi,
        ]);
    }

    #[Route('/{id}/edit', name: 'tournoi_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TournoiType::class, $tournoi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Tournoi mis à jour.');
            return $this->redirectToRoute('tournois_list');
        }

        return $this->render('tournoi/edit.html.twig', [
            'tournoi' => $tournoi,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'tournoi_delete', methods: ['POST'])]
    public function delete(Request $request, Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tournoi->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tournoi);
            $entityManager->flush();
            $this->addFlash('success', 'Tournoi supprimé.');
        }

        return $this->redirectToRoute('tournois_list');
    }

    #[Route('/match/{id}/edit', name: 'app_game_edit', methods: ['GET', 'POST'])]
    public function editMatch(Game $game, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Match mis à jour avec succès.');
            return $this->redirectToRoute('tournoi_show', ['id' => $game->getTournoi()->getId()]);
        }

        return $this->render('game/edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/generate-next-round', name: 'generate_next_round', methods: ['GET'])]
    public function generateNextRound(Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        $this->addFlash('success', 'Le tour suivant a été généré avec succès.');
        return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
    }

    #[Route('/{id}/generate-final', name: 'generate_final_tournament', methods: ['GET'])]
    public function generateFinalTournament(Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        $this->addFlash('success', 'Le tournoi final a été généré avec succès.');
        return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
    }

    #[Route('/{id}/bracket', name: 'tournoi_bracket', methods: ['GET'])]
    public function bracket(Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        $matches = $entityManager->getRepository(Game::class)
            ->findBy(['tournoi' => $tournoi], ['id' => 'ASC']);

        return $this->render('tournoi/bracket.html.twig', [
            'tournoi' => $tournoi,
            'matches' => $matches,
        ]);
    }

    #[Route('/mes-tournois', name: 'mes_tournois', methods: ['GET'])]
    public function mesTournois(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour voir vos tournois.');
            return $this->redirectToRoute('app_login');
        }

        $tournois = $user->getTournoisInscrits();

        return $this->render('tournoi/mes_tournois.html.twig', [
            'tournois' => $tournois,
        ]);
    }
}
