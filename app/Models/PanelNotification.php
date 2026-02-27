<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PanelNotification extends Model
{
    protected $fillable = [
        'user_id',
        'triggered_by',
        'type',
        'title',
        'message',
        'link',
        'is_read',
        'notifiable_type',
        'notifiable_id'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class , 'triggered_by');
    }

    /**
     * Polymorphic relation to the source entity (Lead, Project, etc)
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    public static function send($userId, $type, $title, $message, $link = '#', $triggeredBy = null, $notifiable = null)
    {
        $data = [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'triggered_by' => $triggeredBy,
            'is_read' => false
        ];

        if ($notifiable) {
            $data['notifiable_type'] = get_class($notifiable);
            $data['notifiable_id'] = $notifiable->id;
        }

        return self::create($data);
    }
}
