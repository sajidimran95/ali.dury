<?php

namespace Modules\AiAssistance\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Modules\AiAssistance\Entities\AiAssistanceSetting;
use Modules\AiAssistance\Services\AdminAlertService;
use Modules\AiAssistance\Services\IntelligenceService;

class IntelligenceDigestCommand extends Command
{
    protected $signature = 'japsai:intelligence-digest {--business_id=} {--email=}';

    protected $description = 'Email JapsAI intelligence digest at 7am when digest is enabled';

    public function handle(IntelligenceService $intel, AdminAlertService $alerts): int
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
            $business_id = (int) $settings->business_id;
            $body = $intel->snapshot($business_id)['digest'];
            $alertText = $alerts->sinceText($business_id, now()->subDay());
            if ($alertText !== '') {
                $body .= "\n\n".$alertText;
            }
            try {
                Mail::raw($body, function ($m) use ($email) {
                    $m->to($email)->subject('JapsAI daily digest');
                });
                $this->info('Sent to '.$email);
            } catch (\Throwable $e) {
                report($e);
                $this->error($email.': '.$e->getMessage());
            }
        }

        return 0;
    }
}
