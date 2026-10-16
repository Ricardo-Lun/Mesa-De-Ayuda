<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    protected $fillable = [
        'owner',
        'area',
        'brand',
        'model',
        'serial_number',
        'type',
        'received_at',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'date',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}