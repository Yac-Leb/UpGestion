<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index()
    {
        return $this->render('accueil/index.html.twig');  // rend le template 'index.html.twig' de la page d'accueil
    }
}
