<?php

namespace App\Controller;

use App\Entity\Panier;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\PayPalService;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\PaymentExecution;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PaymentController extends AbstractController
{
    private PayPalService $payPalService;

    public function __construct(PayPalService $payPalService)
    {
        $this->payPalService = $payPalService;
    }

    #[Route('/paiement', name: 'paiement')]
    public function paiement(SessionInterface $session): Response
    {
        $apiContext = $this->payPalService->getApiContext();

        // Récupération du panier depuis la session
        $panier = $session->get('panier', []);

        // Calcul du total à payer
        $total = array_reduce($panier, function ($carry, $item) {
            return $carry + ($item['prix'] * $item['quantity']);
        }, 0);

        // Configuration du paiement
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setTotal($total); // Montant total
        $amount->setCurrency('EUR'); // Devise

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setDescription('Achat sur ma boutique Symfony');

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->payPalService->getReturnUrl())
            ->setCancelUrl($this->payPalService->getCancelUrl());

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions([$transaction])
            ->setRedirectUrls($redirectUrls);

        try {
            $payment->create($apiContext);

            // Stocker l'ID du paiement en session pour validation ultérieure
            $session->set('paypal_payment_id', $payment->getId());

            // Redirection vers PayPal
            return $this->redirect($payment->getApprovalLink());
        } catch (\Exception $e) {
            return new Response("Erreur lors de la création du paiement : " . $e->getMessage());
        }
    }

    #[Route('/payment/return', name: 'payment_return')]
    public function paymentReturn(Request $request, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $apiContext = $this->payPalService->getApiContext();
        $paymentId = $session->get('paypal_payment_id');
        $payerId = $request->query->get('PayerID');

        if (!$paymentId || !$payerId) {
            return $this->redirectToRoute('app_home');
        }

        try {
            // Récupérer le paiement et l'exécuter
            $payment = Payment::get($paymentId, $apiContext);
            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);
            $result = $payment->execute($execution, $apiContext);

            // Créer une nouvelle ligne dans le panier avec l'utilisateur et les articles
            $panier = new Panier();
            $panier->setArticles($this->getPanierArticlesFromSession($session)); // Exemple de méthode pour récupérer les articles du panier
            $panier->setDate(new \DateTime());
            $panier->setArchive(false);

            // Ajoutez l'ID de l'utilisateur ici
            $user = $this->getUser(); // Récupérer l'utilisateur connecté
            $panier->setUser($user); // Si vous avez un lien entre Panier et User

            // Persister le panier dans la base de données
            $entityManager->persist($panier);
            $entityManager->flush();

            // Afficher les détails de paiement ou confirmation
            $transaction = $result->getTransactions()[0];
            $total = $transaction->getAmount()->getTotal();

            // Vider le panier de la session
            $session->set('panier', []);

            // Récupérer toutes les commandes de l'utilisateur connecté
            $panierRepository = $entityManager->getRepository(Panier::class);
            $commandes = $panierRepository->findBy(['user' => $user]);

            // Passer les commandes à la vue
            return $this->render('boutique/commande.html.twig', [
                'commandes' => $commandes,
            ]);
        } catch (\Exception $e) {
            return new Response("Erreur lors de l'exécution du paiement : " . $e->getMessage());
        }
    }

    // Ajoutez cette méthode dans votre contrôleur PaymentController
    private function getPanierArticlesFromSession(SessionInterface $session): array
    {
        // Récupérer les articles du panier stockés dans la session
        $panier = $session->get('panier', []); // Par défaut, un tableau vide si rien n'est dans la session

        // Transformer les articles du panier en un format adapté à l'entité Panier
        $articles = [];
        foreach ($panier as $item) {
            // Vous pouvez ajouter ici des vérifications ou transformations supplémentaires si nécessaire
            $articles[] = [
                'titre' => $item['titre'],
                'size' => $item['size'],
                'quantity' => $item['quantity'],
                'prix' => $item['prix'],
            ];
        }

        return $articles;
    }
}
