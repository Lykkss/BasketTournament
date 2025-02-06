<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\SecurityBundle\Security;

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
        // ✅ Redirection automatique si l'utilisateur est déjà connecté
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
    public function logout(): void
    {
        throw new \LogicException('Cette méthode est gérée automatiquement par Symfony.');
    }

    #[Route(path: '/redirect', name: 'app_redirect_after_login')]
    public function redirectAfterLogin(): RedirectResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_landing_public'); // 🔹 Redirige vers la landing publique
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin'); // ✅ Redirige bien vers le Dashboard Admin
        }

        return $this->redirectToRoute('app_landing_private'); // 🔹 Redirige l'utilisateur normal
    }

}
