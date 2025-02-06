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
    // 1️⃣ Récupère les vainqueurs du tour précédent
    $winnerIds = $gameRepository->getWinnersByTournoi($tournoi);

    // 2️⃣ Vérifie qu'il y a au moins 2 vainqueurs
    if (count($winnerIds) < 2) {
        $this->addFlash('warning', 'Pas assez d’équipes qualifiées pour générer un nouveau tour.');
        return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
    }

    // 3️⃣ Récupérer les objets Equipe à partir des IDs
    $winners = $entityManager->getRepository(Equipe::class)->findBy(['id' => $winnerIds]);

    // 4️⃣ Mélange les gagnants pour éviter des répétitions de matchs
    shuffle($winners);

    // 5️⃣ Génération des nouveaux matchs
    $newMatches = [];
    for ($i = 0; $i < count($winners) - 1; $i += 2) {
        $game = new Game();
        $game->setEquipeA($winners[$i]);
        $game->setEquipeB($winners[$i + 1]);
        $game->setTournoi($tournoi);
        $entityManager->persist($game);
        $newMatches[] = $game;
    }

    // 6️⃣ Vérifier s'il reste une équipe seule => elle est qualifiée d'office
    if (count($winners) % 2 === 1) {
        $qualifiedTeam = end($winners);
        $this->addFlash('info', "L'équipe {$qualifiedTeam->getNom()} est qualifiée automatiquement pour le prochain tour.");
    }

    // 7️⃣ Sauvegarde en base de données
    $entityManager->flush();
    
    $this->addFlash('success', 'Le tour suivant du tournoi a été généré avec succès.');
    return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
}

    /**
     * 🔥 Génération automatique du tournoi final
     */
    #[Route('/tournoi/{id}/generate-final', name: 'generate_final_tournament', methods: ['GET'])]
public function generateFinalTournament(Tournoi $tournoi, EntityManagerInterface $entityManager, GameRepository $gameRepository): Response
{
    // 🔥 Vérifier si le tournoi a déjà un tournoi final
    if ($tournoi->getParentTournoi() !== null) {
        $this->addFlash('warning', 'Ce tournoi est déjà un tournoi final.');
        return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
    }

    // 🔎 Récupérer les vainqueurs des sous-tournois
    $winnerIds = $gameRepository->getWinnersBySousTournois($tournoi);

    // ❌ Vérifier qu'il y a bien des vainqueurs pour créer un tournoi final
    if (count($winnerIds) < 2) {
        $this->addFlash('warning', 'Pas assez d’équipes qualifiées pour un tournoi final.');
        return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
    }

    // ✅ Récupérer les objets Equipe correspondants
    $winners = $entityManager->getRepository(Equipe::class)->findBy(['id' => $winnerIds]);

    // 🏆 Création du tournoi final
    $finalTournoi = new Tournoi();
    $finalTournoi->setNom('🏆 Tournoi Final - ' . $tournoi->getNom());
    $finalTournoi->setDateDebut(new \DateTimeImmutable());
    $finalTournoi->setDateFin(new \DateTimeImmutable('+7 days'));
    $finalTournoi->setStatus('En cours');
    $finalTournoi->setNbMaxEquipes(count($winners));
    $finalTournoi->setParentTournoi($tournoi);

    // 📌 Ajouter les équipes qualifiées au tournoi final
    foreach ($winners as $winner) {
        $finalTournoi->addParticipant($winner);
    }

    $entityManager->persist($finalTournoi);
    $entityManager->flush();

    $this->addFlash('success', '🏆 Le tournoi final a été créé avec succès !');
    return $this->redirectToRoute('tournoi_show', ['id' => $finalTournoi->getId()]);
}
}
