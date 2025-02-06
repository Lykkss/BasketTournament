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

        // Vérifier si l'utilisateur est déjà inscrit
        if ($tournoi->getParticipants()->contains($user)) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à ce tournoi.');
            return $this->redirectToRoute('mes_tournois');
        }

        // Vérifier si le tournoi est "À venir"
        if ($tournoi->getStatus() !== 'À venir') {
            $this->addFlash('danger', 'Vous ne pouvez vous inscrire qu\'aux tournois "À venir".');
            return $this->redirectToRoute('tournois_list');
        }

        // ✅ Ajouter l'utilisateur au tournoi et vice versa
        $tournoi->addParticipant($user);
        $user->addTournoiInscrit($tournoi);

        // ✅ Enregistrer uniquement l'utilisateur (pas besoin de persister le tournoi)
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes inscrit au tournoi.');

        return $this->redirectToRoute('mes_tournois');
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
}
