@include('errors.layout', [
    'status' => 419,
    'title' => 'Page expired',
    'message' => 'This form took too long or your session expired. Refresh the page and submit again.',
    'hint' => 'Usually fixed by refreshing and signing in again.',
    'extra_action' => '<button type="button" class="error-btn error-btn--ghost" onclick="window.location.reload()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 3v6h-6"/></svg>Refresh</button>',
])
