<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Sesión expirada | AgroFusion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        (function () {
            var loginUrl = @json(route('login'));
            var destino = loginUrl;
            try {
                var ref = document.referrer || '';
                if (ref && ref.indexOf(loginUrl) === 0) {
                    destino = loginUrl;
                }
            } catch (e) { /* noop */ }
            try {
                window.location.replace(destino);
            } catch (e) {
                window.location.href = destino;
            }
        })();
    </script>
    <meta http-equiv="refresh" content="0;url={{ route('login') }}">
</head>
<body>
    <p style="font-family:system-ui,sans-serif;text-align:center;padding:2rem;color:#64748b;">
        La sesión expiró o cambió de usuario. Redirigiendo al inicio de sesión…
        Si no avanza, <a href="{{ route('login') }}">inicie sesión aquí</a>.
    </p>
</body>
</html>
