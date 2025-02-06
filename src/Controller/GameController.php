<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Equipe;
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
            return $this->redirectToRoute('app_game_index');
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
            $this->addFlash('success', 'Le match a été mis à jour.');
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
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token'))) {
            $entityManager->remove($game);
            $entityManager->flush();
            $this->addFlash('success', 'Le match a été supprimé.');
        }

        return $this->redirectToRoute('app_game_index');
    }

    /**
     * 🔥 Génération automatique du prochain tour du tournoi
     */
    #[Route('/tournoi/{id}/generate-next-round', name: 'generate_next_round', methods: ['GET'])]
public function generateNextRound(Tournoi $tournoi, EntityManagerInterface $entityManager, GameRepository $gameRepository): Response
{
    // 1️⃣ Récupère les gagnants du tour précédent
    $winners = $gameRepository->getWinnersByTournoi($tournoi);

    // 2️⃣ Vérifie qu'il y a au moins 2 gagnants pour continuer
    if (count($winners) < 2) {
        $this->addFlash('warning', 'Pas assez d’équipes qualifiées pour générer un nouveau tour.');
        return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
    }

    // 3️⃣ Mélange les équipes qualifiées
    shuffle($winners);

    // 4️⃣ Génération des nouveaux matchs
    for ($i = 0; $i < count($winners) - 1; $i += 2) {
        $game = new Game();

        // Récupérer les entités Equipe pour chaque gagnant
        $equipeA = $entityManager->getRepository(Equipe::class)->find($winners[$i]);
        $equipeB = $entityManager->getRepository(Equipe::class)->find($winners[$i + 1]);

        // Vérifier que les équipes existent avant de les affecter
        if ($equipeA && $equipeB) {
            $game->setEquipeA($equipeA);
            $game->setEquipeB($equipeB);
            $game->setTournoi($tournoi);
            $entityManager->persist($game);
        }
    }

    // 5️⃣ Sauvegarde des nouveaux matchs
    $entityManager->flush();
    $this->addFlash('success', 'Le tour suivant du tournoi a été généré avec succès.');

    return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
}


    /**
     * 🔥 Génération automatique du tournoi final
     */
    #[Route('/tournoi/{id}/generate-final', name: 'generate_final_tournament', methods: ['GET'])]
    public function generateFinalTournament(Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        // 🔥 Vérifie si le tournoi est déjà un tournoi final
        if ($tournoi->getParentTournoi() !== null) {
            $this->addFlash('warning', 'Ce tournoi est déjà un tournoi final.');
            return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
        }

        // 🔎 Récupère les vainqueurs des sous-tournois
        $winners = [];
        foreach ($tournoi->getSousTournois() as $sousTournoi) {
            $dernierMatch = $sousTournoi->getGames()->last();
            if ($dernierMatch && $dernierMatch->getVainqueur()) {
                $winners[] = $dernierMatch->getVainqueur();
            }
        }

        // ❌ Vérifie qu'il y a au moins 2 gagnants pour un tournoi final
        if (count($winners) < 2) {
            $this->addFlash('warning', 'Pas assez d’équipes qualifiées pour un tournoi final.');
            return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
        }

        // 🏆 Création du tournoi final
        $finalTournoi = new Tournoi();
        $finalTournoi->setNom('🏆 Tournoi Final - ' . $tournoi->getNom());
        $finalTournoi->setDateDebut(new \DateTimeImmutable());
        $finalTournoi->setDateFin(new \DateTimeImmutable('+7 days'));
        $finalTournoi->setStatus('En cours');
        $finalTournoi->setNbMaxEquipes(count($winners));
        $finalTournoi->setParentTournoi($tournoi);

        // 📌 Ajoute les gagnants au tournoi final
        foreach ($winners as $winner) {
            $finalTournoi->addParticipant($winner);
        }

        $entityManager->persist($finalTournoi);
        $entityManager->flush();

        $this->addFlash('success', '🏆 Le tournoi final a été créé avec succès !');
        return $this->redirectToRoute('tournoi_show', ['id' => $finalTournoi->getId()]);
    }
}
