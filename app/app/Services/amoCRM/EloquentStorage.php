<?php

namespace App\Services\amoCRM;

use Illuminate\Database\Eloquent\Model;
use Ufee\Amo\Base\Storage\Oauth\AbstractStorage;
use Ufee\Amo\Oauthapi;

class EloquentStorage extends AbstractStorage
{
    public Model $model;

    public function __construct(array $options, Model $storage_model)
    {
        parent::__construct($options);

        $this->model = $storage_model;
    }

    public function initClient(Oauthapi $client) {

        parent::initClient($client);

        $key = $client->getAuth('domain').'_'.$client->getAuth('client_id');

        if ($data = $this->getOauth()) {

            static::$_oauth[$key] = $data;
        }
    }

    public function setOauthData(Oauthapi $client, array $oauth): bool
    {
        parent::setOauthData($client, $oauth);

        return (bool)$this->setOauth($client->getAuth('client_id'), $oauth);
    }

    private function getOauth() : array
    {
        return [
            'token_type'    => $this->model->token_type,
            'access_token'  => $this->model->access_token,
            'refresh_token' => $this->model->refresh_token,
            'expires_in'    => $this->model->expires_in,
            'created_at'    => $this->model->created_at,
        ];
    }

    private function setOauth(string $client_id, array $oauth) : array
    {
        $this->model->token_type    = $oauth['token_type'];
        $this->model->access_token  = $oauth['access_token'];
        $this->model->refresh_token = $oauth['refresh_token'];
        $this->model->expires_in    = $oauth['expires_in'];
        $this->model->created_at    = $oauth['created_at'];
        $this->model->save();

        return $oauth;
    }
}
