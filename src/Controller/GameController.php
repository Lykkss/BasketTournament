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

    #[Route('/game/{id}', name: 'app_game_show', methods: ['GET'])]
public function show(Game $game): Response
{
    return $this->render('game/show.html.twig', [
        'game' => $game,
    ]);
}

#[Route('/game/{id}/edit', name: 'app_game_edit', methods: ['GET', 'POST'])]
public function edit(Game $game, Request $request, EntityManagerInterface $entityManager): Response{
    // Créer un formulaire pour le jeu
    $form = $this->createForm(GameType::class, $game);
    $form->handleRequest($request);

    // Si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();  // Enregistrer les changements en base de données
        $this->addFlash('success', 'Le jeu a été modifié avec succès !');

        return $this->redirectToRoute('app_game_show', ['id' => $game->getId()]);
    }

    return $this->render('game/edit.html.twig', [
        'game' => $game,
        'form' => $form->createView(),
    ]);
}


#[Route('/game/new', name: 'app_game_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    // Créer un nouvel objet Game
    $game = new Game();

    // Créer le formulaire pour le jeu
    $form = $this->createForm(GameType::class, $game);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Sauvegarder le jeu dans la base de données
        $entityManager->persist($game);
        $entityManager->flush();

        $this->addFlash('success', 'Le jeu a été créé avec succès !');

        // Rediriger vers la page de détails du jeu
        return $this->redirectToRoute('app_game_show', ['id' => $game->getId()]);
    }

    // Rendre le formulaire dans la vue
    return $this->render('game/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route('/{id}/generate-next-round', name: 'generate_next_round', methods: ['GET'])]
    public function generateNextRound(Tournoi $tournoi, EntityManagerInterface $entityManager, GameRepository $gameRepository): Response
    {
        // 🔎 1️⃣ Vérifier si tous les matchs du tour précédent sont terminés
        $matches = $gameRepository->findBy(['tournoi' => $tournoi, 'vainqueur' => null]);

        if (count($matches) > 0) {
            $this->addFlash('warning', 'Tous les matchs du tour actuel doivent être terminés avant de générer le prochain tour.');
            return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
        }

        // 🔎 2️⃣ Récupérer les vainqueurs du tour précédent
        $winners = [];
        foreach ($tournoi->getGames() as $match) {
            if ($match->getVainqueur()) {
                $winners[] = $match->getVainqueur();
            }
        }

        // ❌ Vérifier qu'il y a au moins 2 vainqueurs
        if (count($winners) < 2) {
            $this->addFlash('success', '🏆 Le tournoi est terminé ! Vainqueur : ' . $winners[0]->getNom());
            return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
        }

        // ✅ 3️⃣ Conversion en tableau pour le shuffle et mélange
        $winners = array_values($winners);
        shuffle($winners);  // Mélanger les vainqueurs
        for ($i = 0; $i < count($winners) - 1; $i += 2) {
            $game = new Game();
            $game->setEquipeA($winners[$i]);
            $game->setEquipeB($winners[$i + 1]);
            $game->setTournoi($tournoi);
            $entityManager->persist($game);
}


        // ✅ 4️⃣ Génération des matchs pour le tour suivant
        for ($i = 0; $i < count($winners) - 1; $i += 2) {
            $game = new Game();
            $game->setEquipeA($winners[$i]);
            $game->setEquipeB($winners[$i + 1]);
            $game->setTournoi($tournoi);

            $entityManager->persist($game);
        }

        // ✅ 5️⃣ Gérer le cas d’une équipe qualifiée d’office (nombre impair)
        if (count($winners) % 2 === 1) {
            $qualifiedTeam = end($winners);
            $this->addFlash('info', "L'équipe {$qualifiedTeam->getNom()} est qualifiée automatiquement pour le prochain tour.");
        }

        // 🔥 6️⃣ Sauvegarde en base et mise à jour du statut du tournoi
        $entityManager->flush();
        $this->addFlash('success', 'Le prochain tour a été généré avec succès.');

        return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
    }

    #[Route('/{id}/generate-final', name: 'generate_final_tournament', methods: ['GET'])]
    public function generateFinalTournament(Tournoi $tournoi, EntityManagerInterface $entityManager, GameRepository $gameRepository): Response
    {
        // 🔥 Vérifier si le tournoi a déjà un tournoi final
        if ($tournoi->getParentTournoi() !== null) {
            $this->addFlash('warning', 'Ce tournoi est déjà un tournoi final.');
            return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
        }

        // 🔎 Récupérer les vainqueurs des sous-tournois
        $winners = [];
        foreach ($tournoi->getGames() as $match) {
            if ($match->getVainqueur()) {
                $winners[] = $match->getVainqueur();
            }
        }

        // ❌ Vérifier qu'il y a bien des vainqueurs
        if (count($winners) < 2) {
            $this->addFlash('warning', 'Pas assez d’équipes qualifiées pour un tournoi final.');
            return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
        }

        // ✅ Création du tournoi final
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

    #[Route('/update-score', name: 'update_score', methods: ['POST'])]
    public function updateScore(Request $request, EntityManagerInterface $entityManager)
    {
        // Récupérer les données de la requête JSON
        $data = json_decode($request->getContent(), true);
        $matchId = $data['matchId'];
        $scoreEquipeA = $data['scoreEquipeA'];
        $scoreEquipeB = $data['scoreEquipeB'];
    
        // Trouver le match dans la base de données
        $game = $entityManager->getRepository(Game::class)->find($matchId);
        if ($game) {
            // Mettre à jour les scores
            $game->setScoreEquipeA($scoreEquipeA);
            $game->setScoreEquipeB($scoreEquipeB);
            $entityManager->flush(); // Sauvegarder les changements
    
            // Déterminer le vainqueur
            $vainqueur = null;
            if ($scoreEquipeA > $scoreEquipeB) {
                $vainqueur = $game->getEquipeA()->getNom();
            } elseif ($scoreEquipeB > $scoreEquipeA) {
                $vainqueur = $game->getEquipeB()->getNom();
            }
    
            // Retourner une réponse JSON pour indiquer que la mise à jour a réussi
            return $this->json([
                'success' => true,
                'vainqueur' => $vainqueur,
            ]);
        }
    
        return $this->json(['success' => false] , 400);
    }
    

#[Route('/bracket/{id}', name: 'app_game_bracket', methods: ['GET'])]
public function bracket(Tournoi $tournoi, GameRepository $gameRepository): Response
{
    // Récupérer les matchs pour ce tournoi
    $matches = $gameRepository->findBy(['tournoi' => $tournoi]);

    return $this->render('game/bracket.html.twig', [
        'tournoi' => $tournoi,
        'matches' => $matches,  // Passe les matchs à la vue Twig
    ]);
}

#[Route('/game/{id}/delete', name: 'app_game_delete', methods: ['POST'])]
public function delete(Game $game, EntityManagerInterface $entityManager): Response
{
    // Suppression du jeu
    $entityManager->remove($game);
    $entityManager->flush();

    // Redirection après la suppression
    $this->addFlash('success', 'Le jeu a été supprimé avec succès.');
    return $this->redirectToRoute('app_game_index');
}





}
