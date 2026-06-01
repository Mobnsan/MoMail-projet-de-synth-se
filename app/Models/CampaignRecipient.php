<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignRecipient extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'campaign_id',
        'email',
        'name',
        'company',
        'status',
        'subject',
        'error',
    ];

    protected $hidden = ['created_at', 'updated_at', 'campaign_id'];
}
