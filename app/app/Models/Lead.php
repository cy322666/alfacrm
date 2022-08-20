<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'amo_contact_id',
        'amo_contact_phone',
        'amo_contact_email',
        'amo_children_name',
        'amo_children_bd',
        'amo_lead_source',
        'amo_lead_instagram',
        'amo_lead_vk',
        'amo_lead_notes',
        'amo_lead_id',
        'amo_lead_name',
        'amo_contact_name',
        'alfa_lead_id',
        'alfa_client_id',
        'status',
    ];
}
