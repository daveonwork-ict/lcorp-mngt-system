<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'announcement_number',
        'title',
        'content',
        'announcement_type',
        'priority_level',
        'target_scope',
        'publish_start_at',
        'publish_end_at',
        'is_pinned',
        'is_urgent',
        'requires_acknowledgment',
        'status',
        'created_by',
        'approved_by',
        'published_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'publish_start_at' => 'datetime',
            'publish_end_at' => 'datetime',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
            'is_pinned' => 'boolean',
            'is_urgent' => 'boolean',
            'requires_acknowledgment' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(AnnouncementTarget::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AnnouncementAttachment::class);
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }
}
