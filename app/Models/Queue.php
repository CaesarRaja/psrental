<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $table = 'queues';
    
    protected $fillable = [
        'user_id', 'reservation_id', 'console_type',
        'queue_number', 'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($queue) {
            $queue->queue_number = str_pad(
                Queue::whereDate('created_at', today())->count() + 1,
                3, '0', STR_PAD_LEFT
            );
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}