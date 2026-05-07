<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodOrder extends Model
{
    protected $fillable = [
        'user_id', 'reservation_id', 'items', 'total', 'notes', 'status',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}