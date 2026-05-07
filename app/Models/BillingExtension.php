<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingExtension extends Model
{
    protected $fillable = [
        'reservation_id', 'requested_duration', 'status', 'admin_notes',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
