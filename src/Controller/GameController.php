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
        shuffle($winners);

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
}
