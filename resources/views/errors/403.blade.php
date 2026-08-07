@include('errors.layout', [
    'status' => 403,
    'title' => 'Access denied',
    'message' => "You don’t have permission to open this page. If you think this is a mistake, contact your administrator.",
    'hint' => 'Ask an admin to update your role or permissions.',
])
