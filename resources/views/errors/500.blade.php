@include('errors.layout', [
    'status' => 500,
    'title' => 'Something went wrong',
    'message' => 'We hit an unexpected problem on our side. Please try again in a moment. If it keeps happening, contact support.',
    'hint' => 'Our team can check the logs for this error.',
])
