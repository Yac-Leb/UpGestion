<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    private $entityManager;
    private $passwordEncoder;
    
    // Injection des services nécessaires
    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/api/register", name="api_register", methods={"POST"})
     */
    public function register(Request $request): JsonResponse
    {
        // Récupérer les données JSON envoyées par AJAX
        $data = json_decode($request->getContent(), true);

        // Vérifier si les données sont présentes
        if (empty($data['nom']) || empty($data['prenom']) || empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'Tous les champs sont requis.'], 400);
        }

        // Créer un nouvel utilisateur
        $user = new User();
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setEmail($data['email']);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $data['password']));

        // Sauvegarder l'utilisateur dans la base de données
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de l\'enregistrement de l\'utilisateur.'], 500);
        }

        // Retourner une réponse JSON pour confirmer l'enregistrement
        return new JsonResponse(['success' => 'Utilisateur enregistré avec succès.'], 200);
    }
}
