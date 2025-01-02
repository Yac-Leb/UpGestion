<?php
// src/Controller/PaiementController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Transaction;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

class PaiementController extends AbstractController
{
    private $apiContext;

    public function __construct()
    {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                'AWzw9SHObKFZ49XIDtIFEE8QoEZFCuoELX9_J8mA3nWz1tf3tn9V_Xsyg5fsVdDpwCntKdyDI59eZFUj',  // Remplacez par votre Client ID PayPal
                'ELRv-g2tD-H9LWN4NwumM2MSAcx7iKXWU4jTjk07W19emZkZyouKEuN85HM0gXv4QI5DK0A5048x0tAJ'  // Remplacez par votre Client Secret PayPal
            )
        );
        $this->apiContext->setConfig([
            'mode' => 'sandbox', // Pour utiliser le mode sandbox
        ]);
    }

    #[Route('/paiement', name: 'paiement')]
    public function index(): Response
    {
        // Exemple de panier
        $panier = [
            ['titre' => 'Article 1', 'prix' => 20, 'quantity' => 2],
            ['titre' => 'Article 2', 'prix' => 30, 'quantity' => 1]
        ];

        // Créez une liste d'articles PayPal
        $items = [];
        $total = 0;

        foreach ($panier as $product) {
            $item = new Item();
            $item->setName($product['titre'])
                 ->setCurrency('EUR')
                 ->setQuantity($product['quantity'])
                 ->setPrice($product['prix']);
            $items[] = $item;
            $total += $product['prix'] * $product['quantity'];
        }

        $itemList = new ItemList();
        $itemList->setItems($items);

        // Détails du paiement
        $details = new Details();
        $details->setSubtotal($total);

        // Montant total
        $amount = new Amount();
        $amount->setCurrency('EUR')
               ->setTotal($total)
               ->setDetails($details);

        // Création de la transaction
        $transaction = new Transaction();
        $transaction->setAmount($amount)
                    ->setItemList($itemList)
                    ->setDescription('Votre panier');

        // Créer un payer
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        // Définir les URLs de redirection
        $redirectUrls = new RedirectUrls();
        
        // Générer des URLs complètes en utilisant generateUrl avec true pour l'option absolue
        $returnUrl = $this->generateUrl('paiement_success', [], true); // URL complète pour succès
        $cancelUrl = $this->generateUrl('paiement_cancel', [], true);  // URL complète pour annulation

        $redirectUrls->setReturnUrl($returnUrl)
                     ->setCancelUrl($cancelUrl);

        // Créer le paiement
        $payment = new Payment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions([$transaction])
                ->setRedirectUrls($redirectUrls);

        try {
            // Créer le paiement avec l'API PayPal
            $payment->create($this->apiContext);

            // Obtenir l'URL de redirection
            foreach ($payment->getLinks() as $link) {
                if ($link->getRel() == 'approval_url') {
                    $redirectUrl = $link->getHref();
                    break;
                }
            }

            return $this->redirect($redirectUrl);

        } catch (\Exception $e) {
            return new Response('Erreur lors du paiement PayPal : ' . $e->getMessage());
        }
    }

    #[Route('/paiement/success', name: 'paiement_success')]
    public function success(): Response
    {
        return new Response('Paiement réussi !');
    }

    #[Route('/paiement/cancel', name: 'paiement_cancel')]
    public function cancel(): Response
    {
        return new Response('Paiement annulé.');
    }
}
