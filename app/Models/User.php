<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the panel notifications for the user.
     */
    public function panelNotifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PanelNotification::class);
    }

    /**
     * Get the count of unread panel notifications.
     */
    public function unreadNotificationsCount(): int
    {
        return $this->panelNotifications()->where('is_read', false)->count();
    }

    /**
     * Get the role name for the user.
     */
    public function getRoleNameAttribute(): string
    {
        // return $this->getRoleNames()->first() ?? 'No Role';
         return $this->roles->first()?->name ?? 'No Role';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
