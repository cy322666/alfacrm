<?php

namespace App\Services\AlfaCRM;

use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use GuzzleHttp\Client as Guzzle;

class Client
{
    const BRANCH_1_ID = 1;//база новых лидов
    const BRANCH_2_ID = 2;//комитерна
    const BRANCH_3_ID = 3;//полтавская
    const BRANCH_4_ID = 4;//окт рев

    const CLIENT_TYPE_ID = 1; //физик
    const CLIENT_STUDY = 1; //is_study 0 - лид 1 - клиент

//    const SOURCE_1_ID = 1; //звонок
    const SOURCE_2_ID = 2; //рекомендация
//    const SOURCE_3_ID = 3; //сайт
    const SOURCE_4_ID = 4; //вк
//    const SOURCE_5_ID = 5; //вывеска
//    const SOURCE_6_ID = 6; //действующий
//    const SOURCE_7_ID = 7; //инста
    const SOURCE_8_ID = 8; //рекл в инете
    const SOURCE_9_ID = 9; //поиск в инете
    const SOURCE_10_ID = 10; //живут рядом
    const SOURCE_11_ID = 11; //дубльгис
    const SOURCE_12_ID = 12; //в парке

    public static function init(): \Nikitanp\AlfacrmApiPhp\Client
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
