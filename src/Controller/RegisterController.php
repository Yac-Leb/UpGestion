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
use Symfony\Component\HttpFoundation\JsonResponse;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Créer un nouvel utilisateur
        $user = new User();

        // Si la requête est en JSON
        if ($request->isMethod('POST') && $request->headers->get('Content-Type') === 'application/json') {
            $data = json_decode($request->getContent(), true);

            // Vérifiez que les données sont présentes
            if (empty($data['mail']) || empty($data['prenom']) || empty($data['nom']) || empty($data['plainPassword'])) {
                return new JsonResponse(['error' => 'Données manquantes.'], 400);
            }

            // Remplir les données de l'utilisateur
            $user->setMail($data['mail']);
            $user->setNom($data['nom']);
            $user->setPrenom($data['prenom']);
            $user->setPassword(
                $passwordHasher->hashPassword($user, $data['plainPassword'])
            );

            // Sauvegarder l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Retourner une réponse JSON avec un message de succès
            return new JsonResponse(['message' => 'Inscription réussie'], 200);
        }

        // Si la requête n'est pas en JSON, il s'agit d'un formulaire HTML classique
        $form = $this->createForm(RegisterFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hacher le mot de passe
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            // Sauvegarder l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Ajouter un message flash de succès
            $this->addFlash('success', 'Votre inscription a été réussie !');

            // Rediriger vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        // Afficher le formulaire d'inscription dans un navigateur
        return $this->render('register/register.html.twig', [
            'registerForm' => $form->createView(),
        ]);
    }
}
