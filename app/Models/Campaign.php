<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'ownerId',
        'name',
        'templateId',
        'status',
        'scheduledAt',
        'recipientCount',
        'deliveredCount',
    ];

    protected $hidden = ['updated_at'];

    public function recipients()
    {
        return $this->hasMany(CampaignRecipient::class, 'campaign_id');
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['createdAt'] = $array['created_at'];
        unset($array['created_at']);
        if (isset($array['recipients'])) {
            $array['recipients'] = $this->recipients->toArray();
        }
        return $array;
    }
}
