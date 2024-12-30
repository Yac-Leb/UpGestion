<?php
// src/Controller/BoutiqueController.php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}
