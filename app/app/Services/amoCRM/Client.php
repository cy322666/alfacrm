<?php

namespace App\Services\amoCRM;

use Ufee\Amo\Oauthapi;

class Client
{
    public EloquentStorage $storage;
    public Oauthapi $service;

    public function init($storage): Client
    {
        $this->storage = $storage;

        try {
            $this->service = Oauthapi::setInstance([
                'domain'        => $this->storage->model->subdomain,
                'client_id'     => $this->storage->model->client_id,
                'client_secret' => $this->storage->model->client_secret,
                'redirect_uri'  => $this->storage->model->redirect_uri,
            ]);
        } catch (\Exception $exception) {
            dd($exception->getLine());

        }


        try {
            $this->service->account;

        } catch (\Exception $exception) {

            if ($this->storage->model->refresh_token) {

                $oauth = $this->service->refreshAccessToken($this->storage->model->refresh_token);

            } else
                $oauth = $this->service->fetchAccessToken($this->storage->model->code);

            $this->service->setOauth([
                'token_type'    => $oauth['token_type'],
                'expires_in'    => $oauth['expires_in'],
                'access_token'  => $oauth['access_token'],
                'refresh_token' => $oauth['refresh_token'],
                'created_at'    => $oauth['created_at'] ?? time(),
            ]);
        }
        return $this;
    }
}
