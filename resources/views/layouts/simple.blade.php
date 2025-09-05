<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    <!-- Styles -->
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #1f2937;
        }

        .btn {
            background: #3b82f6;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #2563eb;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>{{ config('app.name') }}</h1>

        @yield('content')

        {{ $slot ?? '' }}

        <hr style="margin: 40px 0; border: none; border-top: 1px solid #e5e7eb;">

        <p style="text-align: center; color: #6b7280; font-size: 14px;">
            <a href="/admin" class="btn">Akses Admin Panel</a>
        </p>
    </div>
</body>

</html>
