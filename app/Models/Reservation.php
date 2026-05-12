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

    public function pricePerHour(): int
    {
        return match ($this->console_type) {
            'PS4' => 15000,
            'PS5' => 25000,
            'VR' => 35000,
            default => 15000,
        };
    }

    public function approvedBillingExtensionMinutes(): int
    {
        if ($this->relationLoaded('billingExtensions')) {
            return (int) $this->billingExtensions->where('status', 'approved')->sum('requested_duration');
        }

        return (int) $this->billingExtensions()->where('status', 'approved')->sum('requested_duration');
    }

    public function approvedBillingExtensionPrice(): float
    {
        return $this->approvedBillingExtensionMinutes() * ($this->pricePerHour() / 60);
    }

    /** Subtotal reservasi termasuk perpanjang waktu yang sudah disetujui (tanpa makanan). */
    public function reservationSubtotalWithExtensions(): float
    {
        return (float) $this->total_price + $this->approvedBillingExtensionPrice();
    }

    public function approvedFoodOrdersTotal(): float
    {
        return (float) FoodOrder::where('reservation_id', $this->id)
            ->whereIn('status', ['approved', 'delivered'])
            ->sum('total');
    }

    /** Total yang harus dibayar: reservasi + perpanjangan + makanan disetujui. */
    public function grandInvoiceTotal(): float
    {
        return $this->reservationSubtotalWithExtensions() + $this->approvedFoodOrdersTotal();
    }
}