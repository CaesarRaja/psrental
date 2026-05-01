<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'user_id', 'category', 'priority', 'subject',
        'message', 'attachment', 'response', 'status',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}