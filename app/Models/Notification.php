<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'message',
        'type',
        'data'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array'
    ];

    /**
     * Get the users that received the notification.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('read_at')
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include unread notifications.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query)
    {
        return $query->whereHas('users', function ($query) {
            $query->where('users.id', auth()->id())
                  ->whereNull('notification_user.read_at');
        });
    }

    /**
     * Scope a query to only include read notifications.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRead($query)
    {
        return $query->whereHas('users', function ($query) {
            $query->where('users.id', auth()->id())
                  ->whereNotNull('notification_user.read_at');
        });
    }

    /**
     * Check if the notification is unread for the current user.
     *
     * @return bool
     */
    public function isUnread()
    {
        return $this->users()
                    ->where('users.id', auth()->id())
                    ->whereNull('notification_user.read_at')
                    ->exists();
    }

    /**
     * Check if the notification is read for the current user.
     *
     * @return bool
     */
    public function isRead()
    {
        return $this->users()
                    ->where('users.id', auth()->id())
                    ->whereNotNull('notification_user.read_at')
                    ->exists();
    }
} 