@include('errors.layout', [
    'status' => 429,
    'title' => 'Too many requests',
    'message' => 'You’ve made too many requests in a short time. Wait a moment, then try again.',
    'hint' => 'This protects the system from overload.',
])
