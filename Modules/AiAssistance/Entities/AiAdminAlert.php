<?php

namespace Modules\AiAssistance\Entities;

use Illuminate\Database\Eloquent\Model;

class AiAdminAlert extends Model
{
    protected $table = 'ai_admin_alerts';

    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
        'emailed' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function toPayload(): array
    {
        return [
            'id' => (int) $this->id,
            'type' => (string) $this->type,
            'title' => (string) $this->title,
            'message' => (string) $this->message,
            'url' => $this->url,
            'at' => optional($this->created_at)->format('M j, g:i A'),
            'ago' => optional($this->created_at)->diffForHumans(),
            'read' => $this->read_at !== null,
        ];
    }
}
