<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\PasswordHasherInterface; // Vérifiez l'importation correcte
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    private $passwordHasher;

    public function __construct(PasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(Request $request)
    {
        // Récupérer l'email et le mot de passe depuis le formulaire
        $email = $request->get('email');
        $password = $request->get('password');

        // Récupérer l'utilisateur et vérifier le mot de passe
        $user = $this->getDoctrine()
                     ->getRepository(User::class)
                     ->findOneByEmail($email);

        if ($user && $this->passwordHasher->isPasswordValid($user, $password)) {
            // Authentification réussie
            // Vous pouvez rediriger l'utilisateur vers une page sécurisée, par exemple :
            return $this->redirectToRoute('home');
        }

        // Si la connexion échoue
        return $this->render('security/login.html.twig', [
            'error' => 'Email ou mot de passe incorrect',
        ]);
    }
}
