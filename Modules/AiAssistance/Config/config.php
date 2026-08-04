<?php

return [
    'name' => 'AiAssistance',
    'module_version' => '1.1.0',
    'admin_prefix' => 'ai',
    'openai_model' => 'gpt-4o-mini',
    'chat_history_limit' => 20,
    // Short snapshot cache only (seconds). 0 = always hit DB.
    // Do NOT cache free-form AI chat replies long-term — numbers go stale.
    'snapshot_cache_seconds' => 45,
];
