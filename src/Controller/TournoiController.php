<?php 

namespace App\Controller;

use App\Entity\Tournoi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Game;

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

    #[Route('/{id}', name: 'tournoi_show', requirements: ['id' => '\d+'])]
    public function show(Tournoi $tournoi): Response
    {       
        return $this->render('tournoi/show.html.twig', [
            'tournoi' => $tournoi,
        ]);
    }

    #[Route('/mes-tournois', name: 'mes_tournois')]
    public function mesTournois(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // ✅ Récupérer l'utilisateur connecté

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour voir vos tournois.');
            return $this->redirectToRoute('app_login');
        }

        // ✅ Récupérer les tournois de l'utilisateur
        $tournois = $user->getTournoisInscrits();

        return $this->render('tournoi/mes_tournois.html.twig', [
            'tournois' => $tournois,
        ]);
    }

    #[Route('/inscription/{id}', name: 'tournoi_inscription', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function inscription(Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
    
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour vous inscrire à un tournoi.');
            return $this->redirectToRoute('app_login');
        }
    
        // Vérifier si l'utilisateur fait partie d'une équipe
        $equipe = $user->getEquipes()->first();
        if (!$equipe) {
            $this->addFlash('warning', 'Vous devez appartenir à une équipe pour vous inscrire à un tournoi.');
            return $this->redirectToRoute('app_equipe_index');
        }
    
        // Vérifier si le tournoi est "À venir"
        if ($tournoi->getStatus() !== 'À venir') {
            $this->addFlash('danger', 'Vous ne pouvez vous inscrire qu\'à un tournoi "À venir".');
            return $this->redirectToRoute('tournois_list');
        }
    
        // Vérifier si l'équipe est déjà inscrite
        if ($tournoi->getEquipes()->contains($equipe)) {
            $this->addFlash('warning', 'Votre équipe est déjà inscrite à ce tournoi.');
            return $this->redirectToRoute('mes_tournois');
        }
    
        // Inscription de l'équipe au tournoi
        $tournoi->addEquipe($equipe);
        $entityManager->persist($tournoi);
        $entityManager->flush();
    
        $this->addFlash('success', 'Votre équipe est inscrite au tournoi ' . $tournoi->getNom() . ' avec succès.');
        return $this->redirectToRoute('mes_tournois');
    }
    

    #[Route('/tournoi/{id}/generate-matches', name: 'tournoi_generate_matches')]
public function generateMatches(Tournoi $tournoi, EntityManagerInterface $entityManager): Response
{
    // Récupérer les équipes inscrites
    $equipes = $tournoi->getEquipes();

    if (count($equipes) < 2) {
        $this->addFlash('warning', 'Il faut au moins 2 équipes pour générer des matchs.');
        return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
    }

    // Mélanger les équipes pour un tirage au sort aléatoire
    $equipesArray = $equipes->toArray();
    shuffle($equipesArray);

    // Générer les matchs (tour par tour)
    $nbEquipes = count($equipesArray);
    for ($i = 0; $i < $nbEquipes; $i += 2) {
        if (isset($equipesArray[$i + 1])) {
            $match = new Game();
            $match->setTournoi($tournoi);
            $match->setEquipeA($equipesArray[$i]);
            $match->setEquipeB($equipesArray[$i + 1]);
            $entityManager->persist($match);
        }
    }

    $entityManager->flush();

    $this->addFlash('success', 'Les matchs ont été générés avec succès !');
    return $this->redirectToRoute('tournoi_show', ['id' => $tournoi->getId()]);
}


    #[Route('/desinscription/{id}', name: 'tournoi_desinscription', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function desinscription(Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour vous désinscrire d\'un tournoi.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si l'utilisateur est bien inscrit
        if (!$tournoi->getParticipants()->contains($user)) {
            $this->addFlash('warning', 'Vous n\'êtes pas inscrit à ce tournoi.');
            return $this->redirectToRoute('mes_tournois');
        }

        // ✅ Supprimer l'utilisateur du tournoi et inversement
        $tournoi->removeParticipant($user);
        $user->removeTournoiInscrit($tournoi);

        // ✅ Enregistrer uniquement l'utilisateur (pas besoin de persister le tournoi)
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes désinscrit du tournoi.');

        return $this->redirectToRoute('mes_tournois');
    }

    #[Route('/{id}/bracket', name: 'tournoi_bracket', methods: ['GET'])]
    public function bracket(Tournoi $tournoi): Response
    {
        return $this->render('tournoi/bracket.html.twig', [
            'tournoi' => $tournoi,
        ]);
    }

}
