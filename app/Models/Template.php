<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'ownerId',
        'name',
        'senderName',
        'senderEmail',
        'subject',
        'body',
    ];

    protected $hidden = ['updated_at'];

    public function toArray()
    {
        $array = parent::toArray();
        $array['createdAt'] = $array['created_at'];
        unset($array['created_at']);
        return $array;
    }
}
