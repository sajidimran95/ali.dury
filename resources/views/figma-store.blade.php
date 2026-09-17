<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Green Cloud Wholesale</title>
    <meta name="description" content="Explore a user-friendly platform for smoke and vape products, offering intuitive navigation, detailed product views, and seamless shopping experiences for wholesale clients.">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('figma-store/app.css') }}?v={{ filemtime(public_path('figma-store/app.css')) }}">
</head>
<body>
    <div id="root"></div>
    <script>
        (function () {
            var hash = window.location.hash || '';
            if (hash.indexOf('#/') === 0) {
                var next = hash.slice(1) || '/';
                window.history.replaceState(window.history.state, '', next + window.location.search);
            }
        })();
    </script>
    <script type="module" src="{{ asset('figma-store/app.js') }}?v={{ filemtime(public_path('figma-store/app.js')) }}"></script>
</body>
</html>
