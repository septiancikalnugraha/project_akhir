<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Create a new notification.
     *
     * @param  string  $title
     * @param  string  $message
     * @param  string  $type
     * @param  array  $data
     * @param  array|int  $userIds
     * @return \App\Models\Notification
     */
    public function create($title, $message, $type = 'info', $data = [], $userIds = null)
    {
        $notification = Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data
        ]);

        if ($userIds) {
            if (!is_array($userIds)) {
                $userIds = [$userIds];
            }
            $notification->users()->attach($userIds);
        } else {
            // Send to all users if no specific users are provided
            $notification->users()->attach(User::pluck('id')->toArray());
        }

        return $notification;
    }

    /**
     * Get unread notifications for a user.
     *
     * @param  int  $userId
     * @return \Illuminate\Support\Collection
     */
    public function getUnread($userId)
    {
        return Notification::whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId)
                  ->whereNull('notification_user.read_at');
        })->latest()->get();
    }

    /**
     * Get unread notification count for a user.
     *
     * @param  int  $userId
     * @return int
     */
    public function getUnreadCount($userId)
    {
        return Notification::whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId)
                  ->whereNull('notification_user.read_at');
        })->count();
    }

    /**
     * Mark a notification as read.
     *
     * @param  int  $notificationId
     * @param  int  $userId
     * @return bool
     */
    public function markAsRead($notificationId, $userId)
    {
        $notification = Notification::findOrFail($notificationId);
        return $notification->users()->updateExistingPivot($userId, [
            'read_at' => now()
        ]);
    }

    /**
     * Mark all notifications as read for a user.
     *
     * @param  int  $userId
     * @return int
     */
    public function markAllAsRead($userId)
    {
        return Notification::whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId)
                  ->whereNull('notification_user.read_at');
        })->get()->each(function ($notification) use ($userId) {
            $notification->users()->updateExistingPivot($userId, [
                'read_at' => now()
            ]);
        })->count();
    }

    /**
     * Delete a notification.
     *
     * @param  int  $notificationId
     * @return bool
     */
    public function delete($notificationId)
    {
        return Notification::findOrFail($notificationId)->delete();
    }

    /**
     * Delete all notifications for a user.
     *
     * @param  int  $userId
     * @return int
     */
    public function deleteAll($userId)
    {
        return Notification::whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })->get()->each(function ($notification) use ($userId) {
            $notification->users()->detach($userId);
        })->count();
    }

    /**
     * Delete old notifications.
     *
     * @param  int  $days
     * @return int
     */
    public function deleteOldNotifications($days = 30)
    {
        $date = now()->subDays($days);
        return Notification::where('created_at', '<', $date)->delete();
    }

    public function notifyLoanDue($loan)
    {
        $customer = $loan->customer;
        $title = 'Pengingat Jatuh Tempo Angsuran';
        $message = "Angsuran pinjaman {$loan->code} akan jatuh tempo pada " . 
                  Carbon::parse($loan->next_instalment_date)->format('d/m/Y');
        $type = 'loan_due';
        $data = [
            'loan_id' => $loan->id,
            'due_date' => $loan->next_instalment_date
        ];

        return $this->create($title, $message, $type, $data);
    }

    public function notifyLoanStatus($loan)
    {
        $customer = $loan->customer;
        $title = 'Status Pinjaman Diperbarui';
        $message = "Status pinjaman {$loan->code} telah diperbarui menjadi " . 
                  ucfirst($loan->status);
        $type = 'loan_status';
        $data = [
            'loan_id' => $loan->id,
            'status' => $loan->status
        ];

        return $this->create($title, $message, $type, $data);
    }

    public function notifyNewDeposit($deposit)
    {
        $customer = $deposit->customer;
        $title = 'Simpanan Baru';
        $message = "Simpanan baru sebesar Rp " . 
                  number_format($deposit->total, 0, ',', '.') . 
                  " telah berhasil dibuat";
        $type = 'new_deposit';
        $data = [
            'deposit_id' => $deposit->id,
            'amount' => $deposit->total
        ];

        return $this->create($title, $message, $type, $data);
    }

    public function notifyInstalmentPaid($instalment)
    {
        $customer = $instalment->loan->customer;
        $title = 'Angsuran Dibayar';
        $message = "Angsuran pinjaman {$instalment->loan->code} sebesar Rp " . 
                  number_format($instalment->total, 0, ',', '.') . 
                  " telah berhasil dibayar";
        $type = 'instalment_paid';
        $data = [
            'instalment_id' => $instalment->id,
            'amount' => $instalment->total
        ];

        return $this->create($title, $message, $type, $data);
    }
} 