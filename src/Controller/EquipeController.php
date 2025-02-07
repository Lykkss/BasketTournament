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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    #[Route('/new', name: 'app_equipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipe = new Equipe();
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipe);
            $entityManager->flush();

            $this->addFlash('success', 'L\'équipe a été créée avec succès !');
            return $this->redirectToRoute('app_equipe_index');
        }

        return $this->render('equipe/new.html.twig', [
            'equipe' => $equipe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_equipe_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(EquipeRepository $equipeRepository, int $id): Response
    {
        $equipe = $equipeRepository->find($id);

        if (!$equipe) {
            throw new NotFoundHttpException("L'équipe demandée n'existe pas.");
        }

        return $this->render('equipe/show.html.twig', [
            'equipe' => $equipe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_equipe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EquipeRepository $equipeRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $equipe = $equipeRepository->find($id);

        if (!$equipe) {
            throw new NotFoundHttpException("L'équipe demandée n'existe pas.");
        }

        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'L\'équipe a été mise à jour.');
            return $this->redirectToRoute('app_equipe_index');
        }

        return $this->render('equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_equipe_delete', methods: ['POST'])]
    public function delete(Request $request, EquipeRepository $equipeRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $equipe = $equipeRepository->find($id);

        if (!$equipe) {
            throw new NotFoundHttpException("L'équipe demandée n'existe pas.");
        }

        if ($this->isCsrfTokenValid('delete' . $equipe->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($equipe);
                $entityManager->flush();
                $this->addFlash('success', 'L\'équipe a été supprimée.');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Impossible de supprimer cette équipe. Vérifiez qu\'elle n\'est pas associée à un tournoi.');
            }
        }

        return $this->redirectToRoute('app_equipe_index');
    }

    #[Route('/{id}/rejoindre', name: 'equipe_rejoindre', methods: ['POST'])]
    public function rejoindreEquipe(EquipeRepository $equipeRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour rejoindre une équipe.');
            return $this->redirectToRoute('app_login');
        }

        $equipe = $equipeRepository->find($id);

        if (!$equipe) {
            throw new NotFoundHttpException("L'équipe demandée n'existe pas.");
        }

        if ($equipe->getMembres()->contains($user)) {
            $this->addFlash('warning', 'Vous faites déjà partie de cette équipe.');
            return $this->redirectToRoute('app_equipe_index');
        }

        $equipe->addMembre($user);
        $entityManager->persist($equipe);
        $entityManager->flush();

        $this->addFlash('success', 'Vous avez rejoint l\'équipe ' . $equipe->getNom() . ' avec succès.');
        return $this->redirectToRoute('app_equipe_index');
    }

    #[Route('/mes-equipes', name: 'mes_equipes', methods: ['GET'])]
    public function mesEquipes(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour voir vos équipes.');
            return $this->redirectToRoute('app_login');
        }

        $equipes = $user->getEquipes();

        return $this->render('equipe/mes_equipes.html.twig', [
            'equipes' => $equipes,
        ]);
    }
}
