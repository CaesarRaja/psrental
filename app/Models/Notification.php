<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'role_target', 'title', 'message', 'link',
        'notifiable_type', 'notifiable_id', 'is_read', 'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Notifikasi untuk inbox pengguna saat ini: pesan personal sesuai peran,
     * atau broadcast (user_id null) untuk peran yang sama.
     */
    public function scopeForRecipient(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $outer) use ($user) {
            $outer->where(function (Builder $direct) use ($user) {
                $direct->where('user_id', $user->id)
                    ->where(function (Builder $roleCheck) use ($user) {
                        $roleCheck->whereNull('role_target')
                            ->orWhere('role_target', $user->role);
                    });
            })->orWhere(function (Builder $broadcast) use ($user) {
                $broadcast->whereNull('user_id')
                    ->where('role_target', $user->role);
            });
        });
    }
}
