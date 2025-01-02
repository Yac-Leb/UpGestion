<?php
// src/Controller/BoutiqueController.php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class BoutiqueController extends AbstractController
{
    #[Route('/boutique', name: 'app_boutique')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les articles
        $articles = $entityManager->getRepository(Article::class)->findAll();

        // Passer les articles au template
        return $this->render('boutique/boutique.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/boutique/article/{id}', name: 'article_detail')]
    public function articleDetail(EntityManagerInterface $entityManager, int $id): Response
    {
        // Récupérer l'article par son ID
        $article = $entityManager->getRepository(Article::class)->find($id);
    
        // Vérifier si l'article existe
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }
    
        // Passer l'article au template
        return $this->render('boutique/article.html.twig', [
            'article' => $article,
        ]);
    }
    

    #[Route('/boutique/article/{id}/panier', name: 'add_panier', methods: ['POST'])]
    public function addToPanier(
        EntityManagerInterface $entityManager, 
        int $id, 
        Request $request, 
        SessionInterface $session
    ): Response {
        // Récupérer l'article
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        // Récupérer les options depuis le formulaire
        $size = $request->request->get('size');
        $quantity = (int) $request->request->get('quantity');

        // Initialiser ou récupérer le panier dans la session
        $panier = $session->get('panier', []);

        // Ajouter l'article au panier
        $panierItem = [
            'id' => $article->getId(),
            'titre' => $article->getTitre(),
            'prix' => $article->getPrix(),
            'size' => $size,
            'quantity' => $quantity,
        ];

        $panier[] = $panierItem;
        $session->set('panier', $panier);

        // Message de confirmation
        $this->addFlash('success', "{$article->getTitre()} ajouté au panier!");

        // Redirection vers la page panier
        return $this->redirectToRoute('app_panier');
    }


    
}
