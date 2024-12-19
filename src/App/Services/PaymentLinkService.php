<?php

namespace Jeybin\Networkintl\App\Services;

use Jeybin\Networkintl\App\Services\Client\NgeniusClient;

final class PaymentLinkService extends NgeniusClient {
    
    private $client;

    public function __construct() {
        $this->client = new NgeniusClient();
    }

    /**
     * Create payment link
     */
    public function createPaymentLink(array $request) {
        try {
            return $this->client
                       ->setApi('transactions/outlets/{outlet-reference}/payment-links')
                       ->execute('post', $request);
        } catch (\Exception $exception) {
            throwNgeniusPackageResponse($exception);
        }
    }

    /**
     * Get payment link details
     */
    public function getPaymentLink(string $linkId) {
        try {
            return $this->client
                       ->setApi("transactions/outlets/{outlet-reference}/payment-links/$linkId")
                       ->execute('get');
        } catch (\Exception $exception) {
            throwNgeniusPackageResponse($exception);
        }
    }
} 