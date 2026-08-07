@include('errors.layout', [
    'status' => 400,
    'title' => 'Bad request',
    'message' => 'The request could not be understood. Please check what you submitted and try again.',
    'hint' => 'This is usually a temporary or form-related issue.',
])
