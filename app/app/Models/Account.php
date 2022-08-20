<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'subdomain',
        'access_token',
        'refresh_token',
        'client_secret',
        'redirect_uri',
        'token_type',
        'expires_in',
    ];
}
