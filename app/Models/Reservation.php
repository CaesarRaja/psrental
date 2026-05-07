<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'user_id', 'console_type', 'date', 'start_time',
        'duration', 'total_price', 'status', 'extended_duration', 'started_at',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function queue()
    {
        return $this->hasOne(Queue::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function billingExtensions()
    {
        return $this->hasMany(BillingExtension::class);
    }

    public function foodOrders()
    {
        return $this->hasMany(FoodOrder::class);
    }
}