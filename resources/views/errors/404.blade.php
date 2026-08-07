@include('errors.layout', [
    'status' => 404,
    'title' => 'Page not found',
    'message' => 'This page doesn’t exist or may have been moved. Check the address, or head back to a safe place.',
    'hint' => 'Broken link? Try Home or the main menu.',
])
