<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Tournoi;
use App\Form\GameType;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/game')]
final class GameController extends AbstractController
{
    #[Route(name: 'app_game_index', methods: ['GET'])]
    public function index(GameRepository $gameRepository): Response
    {
        return $this->render('game/index.html.twig', [
            'games' => $gameRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_game_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($game);
            $entityManager->flush();

            $this->addFlash('success', 'Le match a été créé avec succès.');
            return $this->redirectToRoute('app_game_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('game/new.html.twig', [
            'game' => $game,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_game_show', methods: ['GET'])]
    public function show(Game $game): Response
    {
        return $this->render('game/show.html.twig', [
            'game' => $game,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_game_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Game $game, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(GameType::class, $game);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        return $this->redirectToRoute('app_game_index');
    }

    return $this->render('game/edit.html.twig', [
        'game' => $game,
        'form' => $form,
    ]);
}


    #[Route('/{id}', name: 'app_game_delete', methods: ['POST'])]
    public function delete(Request $request, Game $game, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($game);
            $entityManager->flush();

            $this->addFlash('success', 'Le match a été supprimé.');
        }

        return $this->redirectToRoute('app_game_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * 🔥 Génère automatiquement les demi-finales et finale pour un tournoi
     */
    #[Route('/tournoi/{id}/generate-next-round', name: 'generate_next_round', methods: ['GET'])]
    public function generateNextRound(Tournoi $tournoi, EntityManagerInterface $entityManager, GameRepository $gameRepository): Response
    {
        // Récupère les gagnants des matchs précédents
        $winners = $gameRepository->getWinnersByTournoi($tournoi);

        // Vérifier si un nouveau tour peut être généré
        if (count($winners) < 2) {
            $this->addFlash('warning', 'Pas assez d’équipes qualifiées pour générer un nouveau tour.');
            return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
        }

        // Mélanger les gagnants pour la suite
        shuffle($winners);

        // Générer les nouveaux matchs
        for ($i = 0; $i < count($winners) - 1; $i += 2) {
            $game = new Game();
            $game->setEquipeA($winners[$i]);
            $game->setEquipeB($winners[$i + 1]);
            $game->setTournoi($tournoi);
            $entityManager->persist($game);
        }

        // Sauvegarder en base
        $entityManager->flush();
        $this->addFlash('success', 'Le tour suivant du tournoi a été généré avec succès.');

        return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
    }
}
