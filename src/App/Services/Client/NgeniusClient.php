<?php

namespace Jeybin\Networkintl\App\Services\Client;


use Exception;
use Illuminate\Support\Facades\Http;
use Jeybin\Networkintl\App\Models\NgeniusGateway;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class NgeniusClient
{

    /**
     * Base api url of ngenius
     *
     * @var [string]
     */
    private string $BASE_URL;


    /**
     * Base api url of ngenius
     *
     * @var [string]
     */
    private string $API_URL;

    /**
     * Refernce Id of ngenius
     *
     * @var [string]
     */
    private string $REFERENCE_ID;


    /**
     * Generated access token
     *
     * @var [string]
     */
    private string $BEARER_TOKEN;


    public function __construct()
    {
        $this->initalize();
    }

    /**
     * Initalizing the Ngenius Client 
     * Variables
     * 
     */
    private function initalize()
    {

        /**
         * Fetching the gateway configurations from the
         * table ngenius_gateway and setting some of them
         * as private variables
         */
        $gatewayConfig = NgeniusGateway::where('active', true)->first();
        if (empty($gatewayConfig)) {
            throwNgeniusPackageResponse('Please configure ngenius_gateway table to continue', null, 422);
        }

        /**
         * Base url from the table
         */
        $this->BASE_URL     = $gatewayConfig->base_url;

        /**
         * Reference id got from the dashboard of ngenius
         */
        $this->REFERENCE_ID = $gatewayConfig->reference_id;

        /**
         * Generating the bearer token since the bearer token is
         * required for every request it is generating from 
         * the constructor method itself and keeping it in 
         * the private variable. The bearer token will expire in 5 minutes
         */
        $this->BEARER_TOKEN = $this->ACCESS_TOKEN($gatewayConfig->api_key);
    }

    protected function setApi($api = '/')
    {
        /**
         * If with reference api string contains
         * {outlet-reference} string it will be
         * replaced with the reference id from 
         * the settings table 
         */
        $api = (strpos($api, '{outlet-reference}')) ? str_replace('{outlet-reference}', $this->REFERENCE_ID, $api) : $api;
        $this->API_URL = $this->BASE_URL . $api;
        return $this;
    }

    /**
     * Generating Access token
     * Using curl because the guzzle http client 
     * always giving Bad request error
     */
    private function ACCESS_TOKEN($API_KEY)
    {

        try {
            /**
             * Access token generation api
             */
            $this->setApi('identity/auth/access-token');
            /**
             * Api headers for the curl request
             */
            $headers = array(
                "authorization: Basic " . $API_KEY,
                "accept: application/vnd.ni-identity.v1+json",
                "content-type: application/vnd.ni-identity.v1+json"
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->API_URL);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            if (config('ngenius-config.log_curl_info')) {
                $verbose = fopen(storage_path('logs/laravel.log'), 'a');
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_STDERR, $verbose);
            }

            $res = curl_exec($ch);
            $output = json_decode($res);

            if (config('ngenius-config.log_curl_info')) {
                Log::channel(config('ngenius-config.log_channel'))->info('Curl request executed successfully.', [
                    'curl_info:' => $res,
                ]);
                fclose($verbose);
            }

            if (!empty($output) && !empty($output->access_token)) {
                return $output->access_token;
            } else {
                throwNgeniusPackageResponse($output);
            }
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }


    protected function execute($type, $request = [], $headers = [])
    {
        $type = strtolower($type);
        if (!in_array($type, ['post', 'get', 'put', 'delete'])) {
            throwNgeniusPackageResponse('Invalid execute type please check!', null, 500);
        }

        if (empty($this->BEARER_TOKEN)) {
            throwNgeniusPackageResponse('Access token not found, please generate access token to continue', null, 422);
        }

        $allheaders = [
            'Content-Type' => 'application/vnd.ni-payment.v2+json',
            'Accept'       => 'application/vnd.ni-payment.v2+json'
        ];

        if (!empty($headers)) {
            $allheaders = array_merge($allheaders, $headers);
        }

        if ($type == 'post') {
            $response =  $this->POST_REQUEST($allheaders, $request);
        }

        if ($type == 'get') {
            $response =  $this->GET_REQUEST($allheaders);
        }

        if ($type == 'put') {
            $response =  $this->PUT_REQUEST($allheaders, $request);
        }

        if ($type == 'delete') {
            $response =  $this->DELETE_REQUEST($allheaders);
        }


        if (empty($response->json())) {
            throwNgeniusPackageResponse('Failed generate payment url', null, 406);
        }

        if ((!$response->successful())) {
            if (!empty($response['message']) && !empty($response['errors']) && !empty($response['code'])) {
                throwNgeniusPackageResponse($response['message'], $response['errors'], $response['code']);
            }
            throwNgeniusPackageResponse($response);
        }

        return $response;
    }

    private function POST_REQUEST($headers, $body = [])
    {
        try {
            return Http::withHeaders($headers)
                ->withToken($this->BEARER_TOKEN)
                ->post($this->API_URL, $body);
        } catch (\Exception $exception) {
            throwNgeniusPackageResponse($exception);
        } catch (ConnectionException $connException) {
            throwNgeniusPackageResponse($connException);
        }
    }


    private function GET_REQUEST($headers)
    {
        try {
            $headers = ['Accept'       => 'application/vnd.ni-payment.v2+json'];
            return Http::withHeaders($headers)
                ->withToken($this->BEARER_TOKEN)
                ->get($this->API_URL);
        } catch (Exception $exception) {
            throwNgeniusPackageResponse($exception);
        } catch (ConnectionException $connException) {
            throwNgeniusPackageResponse($connException);
        }
    }

    private function PUT_REQUEST($headers, $body = [])
    {
        try {
            return Http::withHeaders($headers)
                ->withToken($this->BEARER_TOKEN)
                ->put($this->API_URL, $body);
        } catch (Exception $exception) {
            throwNgeniusPackageResponse($exception);
        } catch (ConnectionException $connException) {
            throwNgeniusPackageResponse($connException);
        }
    }

    private function DELETE_REQUEST($headers)
    {
        try {
            $headers = ['Accept'       => 'application/vnd.ni-payment.v2+json'];
            return Http::withHeaders($headers)
                ->withToken($this->BEARER_TOKEN)
                ->delete($this->API_URL);
        } catch (Exception $exception) {
            throwNgeniusPackageResponse($exception);
        } catch (ConnectionException $connException) {
            throwNgeniusPackageResponse($connException);
        }
    }
}
