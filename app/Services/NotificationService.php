<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Send notification to a specific user.
     */
    public static function notifyUser(int $userId, string $title, string $message, ?string $link = null, ?string $notifiableType = null, ?int $notifiableId = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
        ]);
    }

    /**
     * Send notification to all admins.
     */
    public static function notifyAdmins(string $title, string $message, ?string $link = null, ?string $notifiableType = null, ?int $notifiableId = null): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'role_target' => 'admin',
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $notifiableId,
            ]);
        }
    }

    /**
     * Send notification to a customer.
     */
    public static function notifyCustomer(int $customerId, string $title, string $message, ?string $link = null, ?string $notifiableType = null, ?int $notifiableId = null): Notification
    {
        return Notification::create([
            'user_id' => $customerId,
            'role_target' => 'customer',
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
        ]);
    }
}
