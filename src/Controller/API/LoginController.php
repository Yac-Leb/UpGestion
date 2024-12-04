<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class LoginController extends AbstractController
{
    private $userRepository;
    private $passwordEncoder;

    // Injection des services nécessaires
    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/api/login", name="api_login", methods={"POST"})
     */
    public function login(Request $request): JsonResponse
    {
        // Récupérer les données JSON envoyées par AJAX
        $data = json_decode($request->getContent(), true);

        // Vérifier si les données sont présentes
        if (empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'Email et mot de passe sont requis.'], 400);
        }

        // Trouver l'utilisateur avec l'email
        $user = $this->userRepository->findOneByEmail($data['email']);

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé.'], 404);
        }

        // Vérifier que le mot de passe est correct
        if (!$this->passwordEncoder->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Mot de passe incorrect.'], 401);
        }

        // Si la connexion est réussie, renvoyer les informations de l'utilisateur
        return new JsonResponse([
            'success' => 'Connexion réussie.',
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
        ], 200);
    }
}
