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
        'phone',
        'is_active',
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

    public function conceptTasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConceptTask::class, 'assigned_to');
    }

    public function shootSchedules()
    {
        return $this->hasMany(ShootSchedule::class, 'shooting_person_id');
    }

    public function editTasks()
    {
        return $this->hasMany(EditTask::class, 'assigned_to');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'created_by');
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
