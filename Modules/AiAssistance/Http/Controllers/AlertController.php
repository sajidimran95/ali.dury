<?php

namespace Modules\AiAssistance\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\AiAssistance\Services\AdminAlertService;

class AlertController extends Controller
{
    public function __construct(protected AdminAlertService $alerts)
    {
    }

    protected function businessOrAbort(): int
    {
        $business_id = (int) request()->session()->get('user.business_id');
        if (! AdminAlertService::canView(auth()->user(), $business_id)) {
            abort(403, 'Unauthorized action.');
        }

        return $business_id;
    }

    public function index()
    {
        $business_id = $this->businessOrAbort();
        $alerts = $this->alerts->latest($business_id, 25);

        return response()->json([
            'alerts' => $alerts,
            'unread' => $this->alerts->unreadCount($business_id),
            'latest_id' => $alerts[0]['id'] ?? 0,
        ]);
    }

    public function markRead()
    {
        $business_id = $this->businessOrAbort();
        $this->alerts->markAllRead($business_id);

        return response()->json(['success' => true, 'unread' => 0]);
    }
}
