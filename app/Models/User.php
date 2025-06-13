<?php

namespace App\Models;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = [
        'role',
        'name',
        'email',
        'password'
    ];

    public function customers()
    {
        // Relationship with customers
    }

    public function histories()
    {
        // Relationship with histories
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->belongsToMany(Notification::class)
                    ->withPivot('read_at')
                    ->withTimestamps();
    }

    /**
     * Get the unread notifications for the user.
     */
    public function unreadNotifications()
    {
        return $this->notifications()
                    ->wherePivotNull('read_at');
    }

    /**
     * Get the read notifications for the user.
     */
    public function readNotifications()
    {
        return $this->notifications()
                    ->wherePivotNotNull('read_at');
    }
} 