<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Classement;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminDashboardController extends AbstractController
{
    // route pour accéder au dashboard de l'admin
    #[Route('/admin/dashboard', name: 'app_panel_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        return $this->render('panel_admin/panel_dashboard.html.twig');
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
    #[Route('/admin/add-shop', name: 'admin_add_shop')]
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
        if ($request->isMethod('POST')) {
            $titre = $request->request->get('titre');
            $prix = (float)$request->request->get('prix');
            $description = $request->request->get('description');

            $article = new Article();
            $article->setTitre($titre);
            $article->setPrix($prix);
            $article->setDescription($description);

            // Gestion des images
            $imageDir = 'C:/ecole/web/UpGestion/assets/images/boutique/';
            $dateTime = date('Ymd_His');

            $image1 = $request->files->get('premiere_image');
            $image2 = $request->files->get('deuxieme_image');

            if ($image1) {
                $image1Name = pathinfo($image1->getClientOriginalName(), PATHINFO_FILENAME) . "_{$dateTime}_1." . $image1->guessExtension();
                $image1->move($imageDir, $image1Name);
                $article->setPremiereImage($image1Name);
            }

            if ($image2) {
                $image2Name = pathinfo($image2->getClientOriginalName(), PATHINFO_FILENAME) . "_{$dateTime}_2." . $image2->guessExtension();
                $image2->move($imageDir, $image2Name);
                $article->setDeuxiemeImage($image2Name);
            }

            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article ajouté avec succès.');
            return $this->redirectToRoute('admin_add_shop');
        }

        return $this->render('panel_admin/panel_add_shop.html.twig');
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

// Affiche et gère le formulaire de mise à jour
#[Route('/admin/update-ranking', name: 'admin_update_ranking')]
#[IsGranted('ROLE_ADMIN')]
public function updateRanking(Request $request, EntityManagerInterface $entityManager): Response
{
    // Récupérer le classement actuel
    $classementRepo = $entityManager->getRepository(Classement::class);
    $classementEntity = $classementRepo->findOneBy([], ['date' => 'DESC']);

    $teams = $classementEntity ? $classementEntity->getClassement() : [];

    // Trier les équipes par points de manière décroissante
    usort($teams, function ($a, $b) {
        return $b['points'] <=> $a['points']; // Trier par points de manière décroissante
    });

    // Gestion des soumissions de formulaire
    if ($request->isMethod('POST')) {
        $teamIndex = $request->request->get('teamIndex');
        $teamName = $request->request->get('teamName');
        $teamPoints = (int)$request->request->get('teamPoints');

        // Gestion de l'upload du fichier
        $uploadedFile = $request->files->get('teamLogo');
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/images/classement';
        $fileName = '';

        if ($uploadedFile) {
            $fileName = uniqid() . '.' . $uploadedFile->guessExtension();
            try {
                // Déplacement du fichier dans le répertoire public
                $uploadedFile->move($uploadDir, $fileName);
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                return $this->redirectToRoute('admin_update_ranking');
            }
        }

        // Créer la nouvelle équipe
        $newTeam = [
            'name' => $teamName,
            'logo' => 'images/classement/' . $fileName, // Chemin relatif depuis public/
            'points' => $teamPoints,
        ];

        if ($teamIndex === '') { // Ajout d'une nouvelle équipe
            $teams[] = $newTeam;
        } else { // Modification d'une équipe existante
            $teams[(int)$teamIndex] = $newTeam;
        }

        // Sauvegarde dans la base de données
        if (!$classementEntity) {
            $classementEntity = new Classement();
        }
        $classementEntity->setClassement($teams);
        $entityManager->persist($classementEntity);
        $entityManager->flush();

        return $this->redirectToRoute('admin_update_ranking');
    }

    // Affichage du tableau et du formulaire
    return $this->render('panel_admin/panel_update_classement.html.twig', [
        'teams' => $teams,
    ]);
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

        // Rediriger vers la page du classement après la suppression
        return $this->redirectToRoute('admin_update_ranking');
    }

}
