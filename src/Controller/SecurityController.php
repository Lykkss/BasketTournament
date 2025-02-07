<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Encoder\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // ✅ Vérification si l'utilisateur est déjà connecté
        if ($this->security->getUser()) {
            return $this->redirectToRoute('app_redirect_after_login');
        }

        // Récupération des erreurs et du dernier identifiant utilisé
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): Response
    {
        // Symfony gère automatiquement la déconnexion
        return $this->redirectToRoute('app_home');
    }

    #[Route(path: '/redirect', name: 'app_redirect_after_login')]
    public function redirectAfterLogin(): RedirectResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_home'); // 🔹 Redirection publique
        }

        // ✅ Redirection selon le rôle utilisateur
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard'); // ✅ Accès admin
        }

        return $this->redirectToRoute('app_home'); // 🔹 Accès utilisateur normal
    }

    #[Route(path: '/admin-login', name: 'app_admin_login')]
    public function adminLogin(EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']);

        if ($user) {
            $security->login($user);
            return $this->redirectToRoute('admin_dashboard'); // 🔥 Redirige directement vers l'admin
        }

        $this->addFlash('danger', 'Aucun administrateur trouvé.');
        return $this->redirectToRoute('app_login'); // Redirige si l'utilisateur n'existe pas
    }


    #[Route('/create-admin', name: 'create_admin')]
    public function createAdmin(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Vérifie si un admin existe déjà
        $existingAdmin = $entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']);
        if ($existingAdmin) {
            return new Response('⚠️ Un administrateur existe déjà.', 400);
        }

        // Création d'un nouvel admin
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setPrenom('Admin');
        $admin->setNom('SuperAdmin');
        $admin->setRoles(['ROLE_ADMIN']);

        // Hachage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);

        // Sauvegarde en base de données
        $entityManager->persist($admin);
        $entityManager->flush();

        return new Response('✅ Admin créé avec succès !');
    }
}

   

