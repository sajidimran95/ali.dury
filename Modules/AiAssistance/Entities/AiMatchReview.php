<?php

namespace Modules\AiAssistance\Entities;

use Illuminate\Database\Eloquent\Model;

class AiMatchReview extends Model
{
    protected $table = 'ai_match_reviews';

    protected $guarded = ['id'];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];
}
