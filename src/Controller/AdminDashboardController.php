<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Classement;
use App\Entity\Panier;

use App\Form\ShopFormType;
use App\Form\ClassementFormType;

use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\ClassementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_panel_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        // Récupérer les admins
        $admins = $entityManager->getRepository(User::class)->findAdmins();
        
        // Récupérer les articles
        $articles = $entityManager->getRepository(Article::class)->findAll();
    
        // Récupérer tous les classements
        $classementRepo = $entityManager->getRepository(Classement::class);
        $classements = $classementRepo->findBy([], ['date' => 'DESC']); // Récupérer tous les classements, triés par date décroissante
    
        // Passer les données à la vue
        return $this->render('panel_admin/panel_dashboard.html.twig', [
            'admins' => $admins,
            'articles' => $articles,
            'classements' => $classements // Passer tous les classements à la vue
        ]);
    }
    
    

        #[Route('/admin/shop', name: 'admin_shop')]
    #[IsGranted('ROLE_ADMIN')]
    public function listArticles(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();
        return $this->render('panel_admin/panel_shop.html.twig', [
            'articles' => $articles
        ]);
    }

    // route pour éditer un article
    #[Route('/admin/edit-article/{id}', name: 'admin_edit_article')]
    #[IsGranted('ROLE_ADMIN')]
    public function editArticle(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $article->setTitre($request->request->get('titre'));
            $article->setPrix((float)$request->request->get('prix'));
            $article->setDescription($request->request->get('description'));

            $entityManager->flush();
            $this->addFlash('success', 'Article mis à jour avec succès.');
            return $this->redirectToRoute('admin_shop');
        }

        return $this->render('panel_admin/panel_edit_article.html.twig', [
            'article' => $article
        ]);
    }

    // route pour supprimer un article
    #[Route('/admin/delete-article/{id}', name: 'admin_delete_article')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteArticle(Article $article, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash('success', 'Article supprimé avec succès.');
        return $this->redirectToRoute('admin_shop');
    }

    // route pour ajouter un article
    #[Route('/admin/all-shop', name: 'admin_all_shop')]
    #[IsGranted('ROLE_ADMIN')]
    public function addShop(): Response
    {
        return $this->render('panel_admin/panel_add_shop.html.twig');
    }

    // route pour ajouter un article
    #[Route('/admin/add-article', name: 'admin_add_article')]
    #[IsGranted('ROLE_ADMIN')]
    public function addArticle(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ShopFormType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion des images
            $imageDir = $this->getParameter('kernel.project_dir') . '/public/images/boutique/';;
            $dateTime = date('Ymd_His');

            // Récupération des fichiers
            $image1 = $form->get('premiere_image')->getData();
            $image2 = $form->get('deuxieme_image')->getData();

            if ($image1) {
                $image1Name = pathinfo($image1->getClientOriginalName(), PATHINFO_FILENAME) . "_{$dateTime}_1." . $image1->guessExtension();
                try {
                    $image1->move($imageDir, $image1Name);
                    $article->setPremiereImage($image1Name);
                } catch (FileException $e) {
                    // Gérer l'erreur si le fichier ne peut pas être déplacé
                    $form->get('premiere_image')->addError(new FormError('Une erreur est survenue lors du téléchargement de la première image.'));
                }
            }

            if ($image2) {
                $image2Name = pathinfo($image2->getClientOriginalName(), PATHINFO_FILENAME) . "_{$dateTime}_2." . $image2->guessExtension();
                try {
                    $image2->move($imageDir, $image2Name);
                    $article->setDeuxiemeImage($image2Name);
                } catch (FileException $e) {
                    // Gérer l'erreur si le fichier ne peut pas être déplacé
                    $form->get('deuxieme_image')->addError(new FormError('Une erreur est survenue lors du téléchargement de la deuxième image.'));
                }
            }

            // Sauvegarde de l'article
            $entityManager->persist($article);
            $entityManager->flush();

            // Ajout du message flash
            $this->addFlash('success', 'Article ajouté avec succès.');

            // Redirection après soumission réussie
            return $this->redirectToRoute('admin_add_article');
        }

        return $this->render('panel_admin/panel_add_shop.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // route pour ajouter un admin
    #[Route('/admin/add-admin', name: 'admin_add_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function addAdmin(Request $request, EntityManagerInterface $entityManager): Response
    {
        $searchTerm = $request->query->get('search');

        if ($searchTerm) {
            $users = $entityManager->getRepository(User::class)->createQueryBuilder('u')
                ->where('u.mail LIKE :searchTerm')
                ->orWhere('u.nom LIKE :searchTerm')
                ->orWhere('u.prenom LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%')
                ->getQuery()
                ->getResult();
        } else {
            $users = $entityManager->getRepository(User::class)->findAll();
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('panel_admin/_user_list.html.twig', [
                'users' => $users,
            ]);
        }

        return $this->render('panel_admin/panel_add_admin.html.twig', [
            'users' => $users,
        ]);
    }


    // route pour mettre à jour le rôle d'un utilisateur
    #[Route('/admin/update-role/{id}', name: 'admin_update_role', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateUserRole(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $role = $request->request->get('role');
        
        if (in_array($role, ['ROLE_USER', 'ROLE_ADMIN'])) {
            $user->setRoles([$role]);
            $entityManager->flush();
            $this->addFlash('success', 'Le rôle de l\'utilisateur a été mis à jour avec succès.');
        } else {
            $this->addFlash('error', 'Erreur : rôle non valide.');
        }
        
        return $this->redirectToRoute('admin_add_admin');
    }




    // route pour supprimer un utilisateur
    #[Route('/admin/delete-user/{id}', name: 'admin_delete_user')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', 'L\'utilisateur a été supprimé avec succès.');
        return $this->redirectToRoute('admin_add_admin');
    }

    #[Route('/admin/user/{id}/commandes', name: 'admin_user_commandes')]
    #[IsGranted('ROLE_ADMIN')]
    public function showUserOrders(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur par son ID
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Récupérer les paniers associés à cet utilisateur
        $paniers = $entityManager->getRepository(Panier::class)->findBy(['user' => $user]);

        return $this->render('panel_admin/users_commandes.html.twig', [
            'user' => $user,
            'commandes' => $paniers,
        ]);
    }

    #[Route('/admin/commande/{id}/delete', name: 'admin_delete_commande', methods: ['POST'])]
#[IsGranted('ROLE_ADMIN')]
public function deleteCommande(int $id, EntityManagerInterface $entityManager, Request $request): Response
{
    // Récupérer la commande par son ID
    $commande = $entityManager->getRepository(Panier::class)->find($id);

    if (!$commande) {
        throw $this->createNotFoundException('Commande non trouvée');
    }

    // Vérification du token CSRF
    if (!$this->isCsrfTokenValid('delete_' . $commande->getId(), $request->request->get('_token'))) {
        return $this->redirectToRoute('admin_user_commandes', ['id' => $commande->getUser()->getId()]);
    }

    // Suppression de la commande
    $entityManager->remove($commande);
    $entityManager->flush();

    $this->addFlash('success', 'Commande supprimée avec succès.');

    // Redirection vers la page des commandes de l'utilisateur
    return $this->redirectToRoute('admin_user_commandes', ['id' => $commande->getUser()->getId()]);
}



    #[Route('/admin/update-archive/{id}', name: 'admin_update_archive')]
    public function updateArchive(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Récupérer la commande par son ID
        $commande = $entityManager->getRepository(Panier::class)->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('archive_' . $commande->getId(), $request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Changer le statut d'archive
        $commande->setArchive(!$commande->isArchive()); // Inverser la valeur de 'archive'

        // Sauvegarder les modifications
        $entityManager->flush();

        // Ajouter un message flash pour informer l'utilisateur
        $this->addFlash('success', 'Le statut de la commande a été mis à jour.');

        // Rediriger vers la page des commandes
        return $this->redirectToRoute('admin_user_commandes', ['id' => $commande->getUser()->getId()]);
    }





// Supprimer une équipe
#[Route('/admin/delete-team/{index}', name: 'admin_delete_team')]
#[IsGranted('ROLE_ADMIN')]
public function deleteTeam(int $index, EntityManagerInterface $entityManager): Response
{
    // Récupérer l'entité Classement
    $classementRepo = $entityManager->getRepository(Classement::class);
    $classementEntity = $classementRepo->findOneBy([], ['date' => 'DESC']);

    if ($classementEntity) {
        // Récupérer le classement des équipes
        $teams = $classementEntity->getClassement();

        // Vérifier si l'équipe existe à l'index donné
        if (isset($teams[$index])) {
            // Récupérer le chemin du logo de l'équipe
            $logoPath = $this->getParameter('kernel.project_dir') . '/public/' . $teams[$index]['logo'];

            // Vérifier si le fichier existe et le supprimer
            if (file_exists($logoPath)) {
                unlink($logoPath); // Supprimer le fichier image
            }

            // Supprimer l'équipe du classement
            unset($teams[$index]);

            // Réindexer les clés du tableau
            $teams = array_values($teams);

            // Mettre à jour l'entité Classement avec la nouvelle liste d'équipes
            $classementEntity->setClassement($teams);
            $entityManager->flush();
        }
    }

    return $this->redirectToRoute('admin_update_ranking');
}

// Votre contrôleur

#[Route('/admin/update-ranking', name: 'admin_update_ranking')]
#[IsGranted('ROLE_ADMIN')]
public function index(ClassementRepository $classementRepository, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
{
    // Récupérer les équipes triées par points de manière décroissante
    $teams = $classementRepository->findAllOrderedByPoints();

    // Vérifier si un ID d'équipe a été envoyé via le formulaire
    $teamId = $request->request->get('team_id');
    if ($teamId) {
        // Récupérer l'équipe à modifier
        $classement = $classementRepository->find($teamId);
        if (!$classement) {
            $this->addFlash('error', 'L\'équipe demandée n\'existe pas.');
            return $this->redirectToRoute('admin_update_ranking');
        }
    } else {
        // Si aucun ID n'est envoyé, créer une nouvelle équipe
        $classement = new Classement();
    }

    // Créer et traiter le formulaire
    $form = $this->createForm(ClassementFormType::class, $classement);
    $form->handleRequest($request);

    // Si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        // Gestion de l'upload du fichier logo
        $file = $form->get('logo')->getData();

        if ($file) {
            // Générer un nom unique pour le fichier
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . (new \DateTime())->format('YmdHis') . '.' . $file->guessExtension();

            // Déplacer le fichier dans le répertoire 'public/images/classement'
            try {
                $file->move(
                    $this->getParameter('kernel.project_dir') . '/public/images/classement',
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur lors de l\'upload du logo.');
                return $this->redirectToRoute('admin_update_ranking');
            }

            // Mettre à jour le logo dans l'entité
            $classement->setLogo($newFilename);
        }

        // Enregistrer ou mettre à jour l'entité
        $em->persist($classement);
        $em->flush();

        $this->addFlash('success', 'L\'équipe a été enregistrée avec succès.');
        return $this->redirectToRoute('admin_update_ranking');
    }

    // Renvoyer la vue avec les équipes et le formulaire
    return $this->render('panel_admin/panel_update_classement.html.twig', [
        'teams' => $teams,
        'form' => $form->createView(),
        'team_id' => $teamId,
    ]);
}



    // Supprimer une équipe
    #[Route('/admin/supprimer-ranking/{id}', name: 'classement_supprimer')]
    #[IsGranted('ROLE_ADMIN')]
    public function supprimer(int $id, EntityManagerInterface $em, ClassementRepository $classementRepository): Response
    {
        $classement = $classementRepository->find($id);
    
        if ($classement) {
            // Chemin complet de l'image
            $imagePath = $this->getParameter('kernel.project_dir') . '/public/images/classement/' . $classement->getLogo();
    
            // Vérifier si le fichier existe et le supprimer
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
    
            // Supprimer l'entité Classement
            $em->remove($classement);
            $em->flush();
        }
    
        return $this->redirectToRoute('admin_update_ranking'); // Redirige après la suppression
    }








    #[Route('/admin/all-rankings', name: 'admin_all_rankings')]
    #[IsGranted('ROLE_ADMIN')]
    public function allRankings(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les classements
        $classements = $entityManager->getRepository(Classement::class)->findBy([], ['date' => 'DESC']);

        // Passer les classements à la vue
        return $this->render('panel_admin/panel_all_rankings.html.twig', [
            'classements' => $classements,
        ]);
    }

}
