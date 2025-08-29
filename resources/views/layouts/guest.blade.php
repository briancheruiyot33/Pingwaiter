<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Signup Page</title>

    <link rel="icon" type="image/ico" href="{{ URL::asset('favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div>
            <a href="/">
                <img src="{!! url('uploads/logo.png') !!}" alt="" id="cimg" class="img-fluid img-thumbnail">
            </a>
        </div>
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">

            {{ $slot }}
            <a href="{{ url('auth/google') }}"
                style="display: inline-block; padding: 12px 24px; background-color: #4285F4; color: white; border-radius: 4px; text-align: center; text-decoration: none; font-size: 16px; font-weight: 600; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                Continue with Google
            </a>
        </div>
        <br>


    </div>
</body>
<script>
    @auth
    window.location = '/login';
    @endauth
</script>

</html>
