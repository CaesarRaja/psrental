<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = [
        'bank_name', 'account_number', 'account_holder', 'qris_image',
    ];
}
