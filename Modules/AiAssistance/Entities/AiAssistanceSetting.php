<?php

namespace Modules\AiAssistance\Entities;

use Illuminate\Database\Eloquent\Model;

class AiAssistanceSetting extends Model
{
    protected $table = 'ai_assistance_settings';

    protected $guarded = ['id'];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public static function forBusiness(int $business_id): self
    {
        return static::firstOrCreate(
            ['business_id' => $business_id],
            [
                'is_enabled' => true,
                'openai_api_key' => null,
                'openai_model' => config('aiassistance.openai_model', 'gpt-4o-mini'),
            ]
        );
    }

    public function resolveApiKey(): ?string
    {
        $key = trim((string) $this->openai_api_key);
        if ($key !== '') {
            return $key;
        }

        $env = trim((string) config('openai.api_key'));

        return $env !== '' ? $env : null;
    }

    public function resolveModel(): string
    {
        return $this->openai_model ?: config('aiassistance.openai_model', 'gpt-4o-mini');
    }
}
