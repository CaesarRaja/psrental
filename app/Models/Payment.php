<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'reservation_id', 'total', 'method', 'status',
        'proof_image', 'payable_type', 'payable_id', 'rejection_reason',
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