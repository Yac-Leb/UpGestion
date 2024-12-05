<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();

        // Créer le formulaire d'inscription
        $form = $this->createForm(RegisterFormType::class, $user);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Encoder le mot de passe
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            // Sauvegarder l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Votre inscription a été réussie !');

            return $this->redirectToRoute('app_login');
        }

        // Afficher le formulaire
        return $this->render('register/register.html.twig', [
            'registerForm' => $form->createView(),
        ]);
    }
}
