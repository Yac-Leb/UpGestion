<?php

namespace App\Service;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayPalService
{
    private ApiContext $apiContext;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;

        // Initialisation de l'API PayPal
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                'AWzw9SHObKFZ49XIDtIFEE8QoEZFCuoELX9_J8mA3nWz1tf3tn9V_Xsyg5fsVdDpwCntKdyDI59eZFUj', // Client ID
                'ELRv-g2tD-H9LWN4NwumM2MSAcx7iKXWU4jTjk07W19emZkZyouKEuN85HM0gXv4QI5DK0A5048x0tAJ'  // Secret
            )
        );

        // Configuration de PayPal (Mode Sandbox pour les tests)
        $this->apiContext->setConfig([
            'mode' => 'sandbox', // "sandbox" pour les tests, "live" pour la production
            'http.ConnectionTimeOut' => 30,
            'log.LogEnabled' => true,
            'log.FileName' => '../var/log/paypal.log',
            'log.LogLevel' => 'DEBUG',
        ]);
    }

    public function getApiContext(): ApiContext
    {
        return $this->apiContext;
    }

    public function getReturnUrl(): string
    {
        return $this->urlGenerator->generate('payment_return', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function getCancelUrl(): string
    {
        return $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
