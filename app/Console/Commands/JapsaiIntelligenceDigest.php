<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Modules\AiAssistance\Entities\AiAssistanceSetting;
use Modules\AiAssistance\Services\IntelligenceService;

class JapsaiIntelligenceDigest extends Command
{
    protected $signature = 'japsai:intelligence-digest {--business_id=} {--email=}';

    protected $description = 'Email JapsAI intelligence digest at 7am when digest is enabled';

    public function handle(IntelligenceService $intel): int
    {
        if (! Schema::hasTable('ai_assistance_settings')) {
            return 0;
        }

        $onlyId = (int) $this->option('business_id');
        $onlyEmail = $this->option('email');

        $q = AiAssistanceSetting::query()->where('is_enabled', 1);
        if (Schema::hasColumn('ai_assistance_settings', 'digest_enabled')) {
            $q->where('digest_enabled', 1);
        }
        if ($onlyId) {
            $q->where('business_id', $onlyId);
        }

        foreach ($q->get() as $settings) {
            $email = $onlyEmail ?: $settings->digest_email;
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $data = $intel->snapshot((int) $settings->business_id);
            Mail::raw($data['digest'], function ($m) use ($email) {
                $m->to($email)->subject('JapsAI daily digest');
            });
            $this->info('Sent to '.$email);
        }

        return 0;
    }
}
