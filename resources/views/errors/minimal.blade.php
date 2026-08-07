{{-- Used when APP_DEBUG is false for unhandled exceptions --}}
@php
    $status = 500;
    if (isset($exception) && method_exists($exception, 'getStatusCode')) {
        try {
            $status = (int) $exception->getStatusCode();
        } catch (\Throwable $e) {
            $status = 500;
        }
    }

    $map = [
        401 => [
            'title' => 'Sign in required',
            'message' => 'You need to be signed in to view this page. Please log in and try again.',
            'hint' => 'Your session may have ended for security.',
        ],
        403 => [
            'title' => 'Access denied',
            'message' => "You don’t have permission to open this page. If you think this is a mistake, contact your administrator.",
            'hint' => 'Ask an admin to update your role or permissions.',
        ],
        404 => [
            'title' => 'Page not found',
            'message' => 'This page doesn’t exist or may have been moved. Check the address, or head back to a safe place.',
            'hint' => 'Broken link? Try Home or the main menu.',
        ],
        419 => [
            'title' => 'Page expired',
            'message' => 'This form took too long or your session expired. Refresh the page and submit again.',
            'hint' => 'Usually fixed by refreshing and signing in again.',
        ],
        429 => [
            'title' => 'Too many requests',
            'message' => 'You’ve made too many requests in a short time. Wait a moment, then try again.',
            'hint' => 'This protects the system from overload.',
        ],
        503 => [
            'title' => 'We’ll be right back',
            'message' => 'The service is temporarily unavailable for maintenance. Please check back shortly.',
            'hint' => 'Hang tight — things should be back up soon.',
        ],
    ];

    $copy = $map[$status] ?? [
        'title' => 'Something went wrong',
        'message' => 'We hit an unexpected problem on our side. Please try again in a moment. If it keeps happening, contact support.',
        'hint' => 'Our team can check the logs for this error.',
    ];
@endphp
@include('errors.layout', [
    'status' => $status,
    'title' => $copy['title'],
    'message' => $copy['message'],
    'hint' => $copy['hint'],
])
