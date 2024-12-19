<?php

namespace Jeybin\Networkintl\App\Services;

use Jeybin\Networkintl\App\Services\Client\NgeniusClient;

final class RecurringPaymentService extends NgeniusClient {
    
    private $client;

    public function __construct() {
        $this->client = new NgeniusClient();
    }

    /**
     * Create recurring payment
     */
    public function createRecurring(string $orderId, array $request) {
        try {
            return $this->client
                       ->setApi("transactions/outlets/{outlet-reference}/orders/$orderId/recurring")
                       ->execute('post', $request);
        } catch (\Exception $exception) {
            throwNgeniusPackageResponse($exception);
        }
    }

    /**
     * Update recurring payment
     */
    public function updateRecurring(string $orderId, string $recurringId, array $request) {
        try {
            return $this->client
                       ->setApi("transactions/outlets/{outlet-reference}/orders/$orderId/recurring/$recurringId")
                       ->execute('put', $request);
        } catch (\Exception $exception) {
            throwNgeniusPackageResponse($exception);
        }
    }
} 