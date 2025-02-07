<?php 

namespace App\Controller;

use App\Entity\Tournoi;
use App\Entity\Game;
use App\Form\TournoiType;
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
        $status = $request->query->get('status', 'Tous'); 
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
                $tournoi->setNbMaxEquipes(4);
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

    #[Route('/{id}/inscription', name: 'tournoi_inscription', methods: ['POST'])]
    public function inscription(Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour vous inscrire.');
            return $this->redirectToRoute('app_login');
        }
    
        // Vérification si l'utilisateur est déjà inscrit dans le tournoi
        if ($tournoi->getParticipants()->contains($user)) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à ce tournoi.');
            return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
        }
    
        // Vérification si le tournoi est encore en inscription
        if ($tournoi->getStatus() !== 'À venir') {
            $this->addFlash('danger', 'L\'inscription est fermée.');
            return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
        }
    
        // Vérification si le tournoi a déjà atteint sa limite d'inscriptions
        if (count($tournoi->getParticipants()) >= $tournoi->getNbMaxEquipes()) {
            $this->addFlash('danger', 'Le tournoi est complet.');
            return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
        }
    
        // Vérifier si l'utilisateur est déjà inscrit dans une équipe de ce tournoi
        $userHasTeamInTournament = false;
        foreach ($tournoi->getEquipes() as $equipe) {
            if ($equipe->getMembres()->contains($user)) {
                $userHasTeamInTournament = true;
                break;
            }
        }
    
        // Si l'utilisateur est déjà inscrit dans une équipe, ne pas permettre l'inscription
        if ($userHasTeamInTournament) {
            $this->addFlash('danger', 'Vous êtes déjà inscrit dans une équipe pour ce tournoi.');
            return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
        }
    
        // Ajouter l'utilisateur aux participants du tournoi
        $tournoi->addParticipant($user);
        $entityManager->persist($tournoi);
        $entityManager->flush();
    
        $this->addFlash('success', 'Vous êtes inscrit au tournoi.');
        return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
    }
    
    #[Route('/tournoi/{id}/desinscription', name: 'tournoi_desinscription', methods: ['POST'])]
public function desinscription(Tournoi $tournoi, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser(); // Récupère l'utilisateur connecté

    // Vérifie si l'utilisateur est inscrit
    if ($tournoi->getParticipants()->contains($user)) {
        $tournoi->removeParticipant($user);
        $entityManager->flush();
        $this->addFlash('success', 'Vous vous êtes désinscrit du tournoi avec succès.');
    } else {
        $this->addFlash('warning', 'Vous n\'êtes pas inscrit à ce tournoi.');
    }

    return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
}


    #[Route('/{id}/generate-matches', name: 'tournoi_generate_matches', methods: ['GET'])]
    public function generateMatches(Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        if ($tournoi->getStatus() !== 'À venir') {
            $this->addFlash('danger', 'Les matchs ne peuvent être générés que pour les tournois à venir.');
            return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
        }

        $equipes = $tournoi->getEquipes()->toArray();
        shuffle($equipes);

        for ($i = 0; $i < count($equipes); $i += 2) {
            if (isset($equipes[$i + 1])) {
                $match = new Game();
                $match->setTournoi($tournoi);
                $match->setEquipeA($equipes[$i]);
                $match->setEquipeB($equipes[$i + 1]);
                $entityManager->persist($match);
            }
        }

        $tournoi->setStatus('En cours');
        $entityManager->flush();

        $this->addFlash('success', 'Les matchs ont été générés avec succès.');
        return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
    }

    #[Route('/{id}/generate-next-round', name: 'generate_next_round', methods: ['GET'])]
    public function generateNextRound(Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        $matches = $entityManager->getRepository(Game::class)
            ->findBy(['tournoi' => $tournoi, 'vainqueur' => null]);

        if (count($matches) > 0) {
            $this->addFlash('warning', 'Tous les matchs du tour actuel doivent être terminés.');
            return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
        }

        $winningTeams = [];
        foreach ($tournoi->getGames() as $match) {
            if ($match->getVainqueur()) {
                $winningTeams[] = $match->getVainqueur();
            }
        }

        if (count($winningTeams) < 2) {
            $this->addFlash('success', '🏆 Tournoi terminé ! Vainqueur : ' . $winningTeams[0]->getNom());
            return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
        }

        $winningTeams = array_values($winningTeams);
        shuffle($winningTeams);

        for ($i = 0; $i < count($winningTeams); $i += 2) {
            if (isset($winningTeams[$i + 1])) {
                $newGame = new Game();
                $newGame->setTournoi($tournoi);
                $newGame->setEquipeA($winningTeams[$i]);
                $newGame->setEquipeB($winningTeams[$i + 1]);

                $entityManager->persist($newGame);
            }
        }

        $entityManager->flush();
        $this->addFlash('success', 'Le prochain tour a été généré !');

        return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
    }

    #[Route('/mes-tournois', name: 'mes_tournois', methods: ['GET'])]
    public function mesTournois(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour voir vos tournois.');
            return $this->redirectToRoute('app_login');
        }

        // Récupérer les tournois auxquels l'utilisateur est inscrit
        $tournois = $user->getTournoisInscrits();

        return $this->render('tournoi/mes_tournois.html.twig', [
            'tournois' => $tournois,
        ]);
    }

    #[Route('/{id}/bracket', name: 'tournoi_bracket', methods: ['GET'])]
    public function bracket(Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        // Récupération des matchs du tournoi
        $matches = $entityManager->getRepository(Game::class)
            ->findBy(['tournoi' => $tournoi], ['id' => 'ASC']);

        return $this->render('tournoi/bracket.html.twig', [
            'tournoi' => $tournoi,
            'matches' => $matches,
        ]);
    }



}
