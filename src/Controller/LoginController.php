<?php

namespace App\Controller;

use App\Form\LoginFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà authentifié, rediriger vers la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Création du formulaire de connexion
        $loginForm = $this->createForm(LoginFormType::class, null, [
            'action' => $this->generateUrl('app_login'),
            'method' => 'POST',
        ]);

        // Récupère l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier email saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        // Soumettre le formulaire
        $loginForm->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($loginForm->isSubmitted() && $loginForm->isValid()) {
            print('OKKKKKK');
            return $this->redirectToRoute('app_home');
            // Vous pouvez gérer la logique d'authentification ici si nécessaire.
            // (Cependant, Symfony gère automatiquement l'authentification avec un firewall)
        }
        print('Nonnnn okk');
        return $this->render('register/login.html.twig', [
            'loginForm' => $loginForm->createView(),
            'last_username' => $lastUsername,
            'error' => $error ? 'Mot de passe ou email incorrect' : null,
        ]);
    }
}
