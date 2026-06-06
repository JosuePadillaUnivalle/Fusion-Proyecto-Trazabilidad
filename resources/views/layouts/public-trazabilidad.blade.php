<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Trazabilidad | AgroFusion')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            background: #ffffff;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }

        .trz-public-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 1.5rem 1rem 2rem;
        }

        .trz-public-inner {
            width: 100%;
            max-width: 640px;
        }

        @stack('styles')
    </style>
</head>
<body>
    <main class="trz-public-page">
        <div class="trz-public-inner">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>
