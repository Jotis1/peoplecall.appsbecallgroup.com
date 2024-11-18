<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>{{ $title ?? 'Page Title' }}</title>

        @vite('resources/css/app.css')

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap"
            rel="stylesheet"
        />
        <link rel="shortcut icon" href="{{ asset('icon.png') }}" type="image/x-icon" />
    </head>
    
    <body class="bg-white dark:bg-ctp-base dark:ctp-mocha font-sans text-sm font-medium text-ctp-text antialiased sm:text-base">
        {{ $slot }}
    </body>
</html>
