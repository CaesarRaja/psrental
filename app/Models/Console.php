<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Console extends Model
{
    protected $table = 'consoles';
    
    protected $fillable = [
        'name', 'type', 'status', 'price_per_hour',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'console_type', 'type');
    }
}