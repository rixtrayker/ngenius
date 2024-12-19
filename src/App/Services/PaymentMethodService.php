<?php

namespace Jeybin\Networkintl\App\Services;

use Jeybin\Networkintl\App\Services\Client\NgeniusClient;

final class PaymentMethodService extends NgeniusClient {
    
    private $client;

    public function __construct() {
        $this->client = new NgeniusClient();
    }

    /**
     * Get all available payment methods
     */
    public function getAllPaymentMethods() {
        try {
            return $this->client
                       ->setApi('transactions/outlets/{outlet-reference}/payment-methods')
                       ->execute('get');
        } catch (\Exception $exception) {
            throwNgeniusPackageResponse($exception);
        }
    }

    /**
     * Get specific payment method details
     */
    public function getPaymentMethod(string $paymentMethod) {
        try {
            return $this->client
                       ->setApi("transactions/outlets/{outlet-reference}/payment-methods/$paymentMethod")
                       ->execute('get');
        } catch (\Exception $exception) {
            throwNgeniusPackageResponse($exception);
        }
    }
} 