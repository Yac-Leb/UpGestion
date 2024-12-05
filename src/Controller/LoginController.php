<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginController extends AbstractController
{
    /**
     * Route GET pour afficher le formulaire de connexion
     */
    #[Route('/login', name: 'app_login', methods: ['GET'])]
    public function showLoginForm(): Response
    {
        // Créer et afficher le formulaire de connexion
        $form = $this->createForm(LoginFormType::class);
        return $this->render('register/login.html.twig', [
            'loginForm' => $form->createView(),
        ]);
    }

    /**
     * Route POST pour traiter la soumission du formulaire de connexion
     */
    #[Route('/login', name: 'login_check', methods: ['POST'])]
    public function login(
        Request $request, 
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher // Correct dependency injection
    ): Response
    {
        // Créer le formulaire de connexion
        $form = $this->createForm(LoginFormType::class);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Extraire les données du formulaire
            $data = $form->getData();
            $email = $data['mail'];
            $plainPassword = $form->get('plainPassword')->getData();

            // Rechercher l'utilisateur dans la base de données
            $user = $entityManager->getRepository(User::class)->findOneBy(['mail' => $email]);

            // Vérifier si l'utilisateur existe
            if (!$user) {
                $this->addFlash('error', 'Utilisateur non trouvé.');
                return $this->redirectToRoute('app_login');
            }

            // Vérifier le mot de passe
            if (!$passwordHasher->isPasswordValid($user, $plainPassword)) {
                $this->addFlash('error', 'Mot de passe incorrect.');
                return $this->redirectToRoute('app_login');
            }

            // Connexion réussie - Redirection vers 'app_register'
            $this->addFlash('success', 'Connexion réussie.');
            return $this->redirectToRoute('app_register'); // Redirection vers la page 'app_register'
        }

        // Si le formulaire n'est pas valide ou soumis
        $this->addFlash('error', 'Veuillez remplir correctement le formulaire.');
        return $this->redirectToRoute('app_login');
    }
}
