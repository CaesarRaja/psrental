<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';
    
    protected $fillable = [
        'photo', 'name', 'category', 'price', 'stock', 'status',
    ];

    public function orders()
    {
        return $this->hasMany(FoodOrder::class);
    }
}