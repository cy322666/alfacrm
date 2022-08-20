<?php

namespace App\Services\AlfaCRM;

use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use GuzzleHttp\Client as Guzzle;

class Client
{
    public function init(): \Nikitanp\AlfacrmApiPhp\Client
    {
        $apiClient = new \Nikitanp\AlfacrmApiPhp\Client(
            new Guzzle,
            new RequestFactory,
            new StreamFactory,
        );
        $apiClient->setDomain(env('ALFACRM_DOMAIN'));
        $apiClient->setEmail(env('ALFACRM_EMAIL'));
        $apiClient->setApiKey(env('ALFACRM_API_KEY'));
        $apiClient->authorize();

        return $apiClient;
    }
}
