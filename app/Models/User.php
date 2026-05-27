<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_code',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'full_name',
        'name',
        'username',
        'email',
        'mobile_number',
        'profile_photo',
        'role_id',
        'primary_branch_id',
        'status',
        'is_active',
        'last_login_at',
        'last_login_ip',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_branches')
            ->withPivot(['is_primary'])
            ->withTimestamps();
    }

    public function primaryBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'primary_branch_id');
    }

    public function announcementReads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function chatRoomMemberships(): HasMany
    {
        return $this->hasMany(ChatRoomMember::class);
    }

    public function chatRooms(): BelongsToMany
    {
        return $this->belongsToMany(ChatRoom::class, 'chat_room_members')
            ->withPivot(['role_in_room', 'joined_at', 'status'])
            ->withTimestamps();
    }

    public function communicationNotifications(): HasMany
    {
        return $this->hasMany(CommunicationNotification::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ?: trim(($this->first_name ?? '').' '.($this->last_name ?? '')) ?: $this->name;
    }

    public function hasPermission(string $permissionCode): bool
    {
        if (! $this->relationLoaded('role')) {
            $this->load('role.permissions');
        }

        if (! $this->role) {
            return false;
        }

        if ($this->role->code === config('rms.owner_role_code')) {
            return true;
        }

        return $this->role
            ->permissions
            ->pluck('code')
            ->contains($permissionCode);
    }
}
