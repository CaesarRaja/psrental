<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';
    
    protected $fillable = [
        'emoji', 'name', 'category', 'price', 'stock',
    ];

    public function orders()
    {
        return $this->hasMany(FoodOrder::class);
    }
}