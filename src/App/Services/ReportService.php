<?php

namespace Jeybin\Networkintl\App\Services;

use Jeybin\Networkintl\App\Services\Client\NgeniusClient;

final class ReportService extends NgeniusClient {
    
    private $client;

    public function __construct() {
        $this->client = new NgeniusClient();
    }

    /**
     * Get all reports
     */
    public function getReports() {
        try {
            return $this->client
                       ->setApi('transactions/outlets/{outlet-reference}/reports')
                       ->execute('get');
        } catch (\Exception $exception) {
            throwNgeniusPackageResponse($exception);
        }
    }

    /**
     * Get specific report
     */
    public function getReport(string $reportId) {
        try {
            return $this->client
                       ->setApi("transactions/outlets/{outlet-reference}/reports/$reportId")
                       ->execute('get');
        } catch (\Exception $exception) {
            throwNgeniusPackageResponse($exception);
        }
    }
} 