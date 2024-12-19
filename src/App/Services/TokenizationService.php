<?php

namespace Jeybin\Networkintl\App\Services;

use Jeybin\Networkintl\App\Services\Client\NgeniusClient;

final class TokenizationService extends NgeniusClient {
    
    private $client;

    public function __construct() {
        $this->client = new NgeniusClient();
    }

    /**
     * Create new token
     */
    public function createToken(array $request) {
        try {
            return $this->client
                       ->setApi('transactions/outlets/{outlet-reference}/tokens')
                       ->execute('post', $request);
        } catch (\Exception $exception) {
            throwNgeniusPackageResponse($exception);
        }
    }

    /**
     * Get all tokens
     */
    public function getTokens() {
        try {
            return $this->client
                       ->setApi('transactions/outlets/{outlet-reference}/tokens')
                       ->execute('get');
        } catch (\Exception $exception) {
            throwNgeniusPackageResponse($exception);
        }
    }

    /**
     * Delete specific token
     */
    public function deleteToken(string $tokenId) {
        try {
            return $this->client
                       ->setApi("transactions/outlets/{outlet-reference}/tokens/$tokenId")
                       ->execute('delete');
        } catch (\Exception $exception) {
            throwNgeniusPackageResponse($exception);
        }
    }
} 