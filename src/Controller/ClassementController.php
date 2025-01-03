<?php

namespace App\Controller;

use App\Repository\ClassementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClassementController extends AbstractController
{
    #[Route('/classement', name: 'app_classement')]
    public function index(ClassementRepository $classementRepository): Response
    {
        $classements = $classementRepository->findBy([], ['points' => 'DESC']);

        return $this->render('classement/classement.html.twig', [
            'classements' => $classements,
        ]);
    }
}