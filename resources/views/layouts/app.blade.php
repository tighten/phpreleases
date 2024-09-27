<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>PHP Releases | Tighten</title>
    <meta name="description" content="API endpoints with support information for PHP versions 5.6 and later.">
    <meta property="og:title" content="PHP Releases | Tighten">
    <meta property="og:description" content="API endpoints with support information for PHP versions 5.6 and later.">
    <meta property="og:image" content="{{ config('app.url') . '/images/phpreleases-cover.png' }}">
    <meta property="og:url" content="{{ config('app.url') }}">
    <meta property="og:site_name" content="PHP Releases">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:image:height" content="630">
    <meta property="og:image:width" content="1200">

    <meta name="twitter:title" content="PHP Releases">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@TightenCo">
    <meta name="twitter:image:alt" content="Tighten logo">
    <meta name="twitter:description" content="API endpoints with support information for PHP versions 5.6 and later.">
    <meta property="og:image" content="{{ config('app.url') . '/images/phpreleases-cover.png' }}">

    @vite('resources/css/app.css')
    @stack('scripts')

    @if (app()->environment('production'))
        <script src="https://cdn.usefathom.com/script.js" data-site="MKBAABBX" defer></script>
    @endif
</head>
<body>
    <div>

        @include('partials.header')

        <div class="bg-white max-w-2xl mx-auto py-14 px-4 sm:px-6 lg:max-w-7xl lg:px-8 lg:grid-cols-2">
            <div>
                @yield('content')
            </div>
        </div>
    </div>
    <div class="bg-gray-100 text-gray-800 flex justify-center py-8">
        <div>Brought to you by the lovely folks at <a href="https://tighten.co/" class="text-indigo-700 hover:text-indigo-900 underline">Tighten</a>.</div>
    </div>

</body>
</html>

