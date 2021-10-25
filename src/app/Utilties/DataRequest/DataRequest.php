<?php

namespace App\Utilties\DataRequest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Dotenv\Dotenv;

class DataRequest
{
    //move to dot env
    const RESPONSE_CODE_SUCCESS = 200;
    protected $client;
    protected $method;
    protected $url;
    protected $queryString = '';
    protected $queryPath = '';
    protected $rootPath;
    protected $baseUrl = "";
    protected $dotEnv;


    public function __construct(
    ) {

        $this->logger = New Logger('propertyListing');
        $this
            ->logger
            ->pushHandler(
                new StreamHandler('',
                    Logger::DEBUG
                )
            );

        $this->dotEnv = Dotenv::createImmutable(dirname(__DIR__, 3));
        $this
            ->dotEnv
            ->load();
        $this->setBaseUrl($_ENV['API_URL']);

    }

    public function getData(){

        try {

            $url = $this->queryPath;

            $headers = [
                'Content-Type' => 'application/json',
                'X-Requested-By' => 'zulfadzly',
            ];

            $this->client = New Client(
                [
                    'base_uri' => $this->getBaseUrl()
                ]
            );
            $response = $this->client
                ->get(
                    $url,
                    [
                        'query' => $this->getQueryString()
                    ]
                );

            if($response->getStatusCode() ==  SELF::RESPONSE_CODE_SUCCESS) {

                return $response->getBody();

            }
//            $this
//                ->logger
//                ->info($response);

        } catch (RequestException $e) {
//            $this
//                ->logger
//                ->critical("Request failed {$e->getMessage()}, {$e->getCode()}");
            //capture page and save to setting
            throw new \Exception(
                $e->getMessage(),
                500);
        }

    }


    public function getQueryString()
    {
        return $this->queryString;
    }


    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;
    }


    public function getQueryPath()
    {
        return $this->queryPath;
    }


    public function setQueryPath($queryPath)
    {
        $this->queryPath = $queryPath;
    }


    public function getRootPath()
    {
        return $this->rootPath;
    }

    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function getDotEnv()
    {
        return $this->dotEnv;
    }

    public function setDotEnv($dotEnv)
    {
        $this->dotEnv = $dotEnv;
    }

}