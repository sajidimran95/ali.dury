<?php

namespace Modules\AiAssistance\Entities;

use Illuminate\Database\Eloquent\Model;

class AiChatMessage extends Model
{
    protected $table = 'ai_chat_messages';

    protected $guarded = ['id'];
}
