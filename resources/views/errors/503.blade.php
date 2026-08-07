@include('errors.layout', [
    'status' => 503,
    'title' => 'We’ll be right back',
    'message' => 'The service is temporarily unavailable for maintenance. Please check back shortly.',
    'hint' => 'Hang tight — things should be back up soon.',
])
