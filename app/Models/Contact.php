<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'ownerId',
        'name',
        'email',
        'company',
        'group',
        'extra_fields',
    ];

    protected $casts = [
        'extra_fields' => 'array',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function toArray()
    {
        $array = parent::toArray();
        $extra = $array['extra_fields'] ?? [];
        unset($array['extra_fields']);
        
        return array_merge($array, is_array($extra) ? $extra : []);
    }
}
