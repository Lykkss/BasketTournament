<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Form\EquipeType;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/equipe')]
final class EquipeController extends AbstractController
{
    #[Route('/', name: 'app_equipe_index', methods: ['GET'])]
    public function index(EquipeRepository $equipeRepository): Response
    {
        return $this->render('equipe/index.html.twig', [
            'equipes' => $equipeRepository->findAll(),
        ]);
    }

    #[Route('/mes-equipes', name: 'mes_equipes', methods: ['GET'])]
    public function mesEquipes(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour voir vos équipes.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('equipe/mes_equipes.html.twig', [
            'equipes' => $user->getEquipes(),
        ]);
    }

    #[Route('/new', name: 'app_equipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipe = new Equipe();
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipe);
            $entityManager->flush();
            return $this->redirectToRoute('app_equipe_index');
        }

        return $this->render('equipe/new.html.twig', [
            'equipe' => $equipe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_equipe_show', methods: ['GET'])]
    public function show(Equipe $equipe): Response
    {
        return $this->render('equipe/show.html.twig', [
            'equipe' => $equipe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_equipe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_equipe_index');
        }

        return $this->render('equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/quitter', name: 'equipe_quitter', methods: ['POST'])]
public function quitterEquipe(Equipe $equipe, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();

    if (!$user) {
        $this->addFlash('danger', 'Vous devez être connecté pour quitter une équipe.');
        return $this->redirectToRoute('app_login');
    }

    // Vérifie si l'utilisateur est bien membre de l'équipe
    if (!$equipe->getMembres()->contains($user)) {
        $this->addFlash('warning', 'Vous ne faites pas partie de cette équipe.');
        return $this->redirectToRoute('mes_equipes');
    }

    // Retirer l'utilisateur de l'équipe
    $equipe->removeMembre($user);
    $entityManager->flush();

    $this->addFlash('success', 'Vous avez quitté l\'équipe ' . $equipe->getNom());
    return $this->redirectToRoute('mes_equipes');
}

}
