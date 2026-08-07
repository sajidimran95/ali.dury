@include('errors.layout', [
    'status' => 401,
    'title' => 'Sign in required',
    'message' => 'You need to be signed in to view this page. Please log in and try again.',
    'hint' => 'Your session may have ended for security.',
])
