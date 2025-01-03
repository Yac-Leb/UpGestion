<?php

namespace App\Controller;

use App\Entity\Panier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function panier(SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);

        return $this->render('boutique/panier.html.twig', [
            'panier' => $panier,
        ]);
    }

    #[Route('/panier/supprimer/{index}', name: 'remove_from_panier')]
    public function removeFromPanier(SessionInterface $session, int $index): Response
    {
        $panier = $session->get('panier', []);

        if (isset($panier[$index])) {
            unset($panier[$index]);
            $session->set('panier', array_values($panier));
            $this->addFlash('success', 'Article supprimé du panier.');
        } else {
            $this->addFlash('error', 'Article introuvable dans le panier.');
        }

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/commandes', name: 'app_commandes')]
    public function userOrders(UserInterface $user, EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les commandes de l'utilisateur connecté
        $panierRepository = $entityManager->getRepository(Panier::class);
        $commandes = $panierRepository->findBy(['user' => $user]);

        return $this->render('boutique/commande.html.twig', [
            'commandes' => $commandes,
        ]);
    }
}