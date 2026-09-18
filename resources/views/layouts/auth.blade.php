<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'AgroFusion | Acceso')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">

    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --af-forest: #1f4a2c;
            --af-forest-2: #2d6a3e;
            --af-leaf: #4c8c4a;
            --af-leaf-soft: #e6efdf;
            --af-harvest: #c8993f;
            --af-cream: #faf8f2;
            --af-paper: #ffffff;
            --af-line: #dcd8c9;
            --af-ink: #1d2a21;
            --af-ink-2: #4a574c;
            --af-muted: #7a8579;
            --af-danger: #b3412f;
            --af-radius: 12px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            min-height: 100vh;
            color: var(--af-ink);
            background-color: #2b3d2a;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Fondo: campo de cultivo ── */
        .auth-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                linear-gradient(180deg, rgba(20, 38, 22, .30) 0%, rgba(20, 38, 22, .18) 40%, rgba(14, 28, 16, .62) 100%),
                url('https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=2000&q=80') center / cover no-repeat;
        }

        .auth-layout {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 16px 28px;
        }

        /* ── Tarjeta: etiqueta con marco interior ── */
        .auth-card {
            position: relative;
            width: 620px;
            max-width: 100%;
            background: var(--af-cream);
            border-radius: 26px;
            padding: 36px 38px 28px;
            box-shadow:
                0 24px 50px -14px rgba(10, 25, 12, .5),
                0 4px 14px rgba(10, 25, 12, .12);
        }

        .auth-card::before {
            content: '';
            position: absolute;
            inset: 8px;
            border: 1px solid #d9cfae;
            border-radius: 19px;
            pointer-events: none;
        }

        /* ── Marca ── */
        .auth-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .auth-brand-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .auth-app-name {
            font-family: 'Bricolage Grotesque', 'Plus Jakarta Sans', sans-serif;
            font-size: 2.7rem;
            font-weight: 800;
            color: var(--af-forest);
            letter-spacing: -.02em;
            line-height: 1;
        }

        .auth-tagline {
            margin-top: 10px;
            font-size: .82rem;
            font-weight: 500;
            color: var(--af-ink-2);
            letter-spacing: .01em;
        }

        /* ── Ruta de trazabilidad ── */
        .trace {
            width: 100%;
            margin: 22px 0 26px;
            padding: 16px 0 14px;
            border-top: 1px solid var(--af-line);
            border-bottom: 1px solid var(--af-line);
        }

        .trace-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .18em;
            color: #a57a26;
            margin-bottom: 4px;
        }

        .trace-title::before,
        .trace-title::after {
            content: '';
            width: 22px;
            height: 1.5px;
            background: currentColor;
            opacity: .45;
        }

        .trace-steps {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            position: relative;
        }

        /* línea base + línea de progreso animada */
        .trace-steps::before,
        .trace-steps::after {
            content: '';
            position: absolute;
            top: 56.5px;
            left: calc(100% / 14);
            right: calc(100% / 14);
            height: 3px;
            border-radius: 3px;
        }

        .trace-steps::before {
            background-image: linear-gradient(90deg, #b9cdb0 60%, transparent 0);
            background-size: 8px 3px;
        }

        .trace-steps::after {
            background: linear-gradient(90deg, var(--af-leaf), var(--af-harvest));
            transform-origin: left center;
            animation: trace-fill 10s linear infinite;
        }

        .trace-step {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: center;
            padding: 34px 0;
        }

        .trace-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: var(--af-paper);
            border: 2px solid #c9dbbd;
            color: var(--af-forest-2);
            font-size: 1.15rem;
            box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35);
        }


        /* Etiquetas alternadas (abajo / arriba) para que ninguna palabra se corte */
        .trace-label {
            position: absolute;
            left: 50%;
            top: calc(100% - 28px);
            transform: translateX(-50%);
            width: 200%;
            font-size: .66rem;
            line-height: 1.2;
            --dir: 1;
            font-weight: 700;
            color: var(--af-forest-2);
            text-align: center;
            hyphens: none;
            -webkit-hyphens: none;
            word-break: normal;
            overflow-wrap: normal;
        }

        .trace-step:nth-child(even) .trace-label {
            --dir: -1;
            top: auto;
            bottom: calc(100% - 28px);
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        /*
         * Ciclo de 10s: la línea avanza y, al llegar a cada paso, el ícono se enciende
         * y su nombre se despliega (abajo en los impares, arriba en los pares).
         * Los pasos quedan encendidos hasta completar la ruta y luego se apagan juntos.
         */
        @keyframes trace-fill {
            0%        { transform: scaleX(0); opacity: 1; }
            63%, 88%  { transform: scaleX(1); opacity: 1; }
            95%, 100% { transform: scaleX(1); opacity: 0; }
        }

        @keyframes trace-icon-1 {
            0% { background: var(--af-paper); border-color: #c9dbbd; color: var(--af-forest-2); transform: scale(1); box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35); }
            3% { background: var(--af-forest-2); border-color: var(--af-forest-2); color: #fff; transform: scale(1.16); box-shadow: 0 0 0 7px rgba(76, 140, 74, .22), 0 10px 18px -6px rgba(31, 74, 44, .6); }
            6%, 88% { background: var(--af-forest-2); border-color: var(--af-forest-2); color: #fff; transform: scale(1.04); box-shadow: 0 0 0 4px rgba(76, 140, 74, .16), 0 8px 16px -6px rgba(31, 74, 44, .5); }
            95%, 100% { background: var(--af-paper); border-color: #c9dbbd; color: var(--af-forest-2); transform: scale(1); box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35); }
        }
        @keyframes trace-label-1 {
            0% { opacity: 0; transform: translate(-50%, calc(var(--dir) * -12px)) scale(.9); }
            5%, 88% { opacity: 1; transform: translate(-50%, 0) scale(1); }
            95%, 100% { opacity: 0; transform: translate(-50%, 0) scale(1); }
        }
        .trace-step:nth-child(1) .trace-icon  { animation: trace-icon-1 10s ease-out infinite; }
        .trace-step:nth-child(1) .trace-label { animation: trace-label-1 10s cubic-bezier(.2, .8, .3, 1.2) infinite; }

        @keyframes trace-icon-2 {
            0%, 10.5% { background: var(--af-paper); border-color: #c9dbbd; color: var(--af-forest-2); transform: scale(1); box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35); }
            13.5% { background: var(--af-forest-2); border-color: var(--af-forest-2); color: #fff; transform: scale(1.16); box-shadow: 0 0 0 7px rgba(76, 140, 74, .22), 0 10px 18px -6px rgba(31, 74, 44, .6); }
            16.5%, 88% { background: var(--af-forest-2); border-color: var(--af-forest-2); color: #fff; transform: scale(1.04); box-shadow: 0 0 0 4px rgba(76, 140, 74, .16), 0 8px 16px -6px rgba(31, 74, 44, .5); }
            95%, 100% { background: var(--af-paper); border-color: #c9dbbd; color: var(--af-forest-2); transform: scale(1); box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35); }
        }
        @keyframes trace-label-2 {
            0%, 10.5% { opacity: 0; transform: translate(-50%, calc(var(--dir) * -12px)) scale(.9); }
            15.5%, 88% { opacity: 1; transform: translate(-50%, 0) scale(1); }
            95%, 100% { opacity: 0; transform: translate(-50%, 0) scale(1); }
        }
        .trace-step:nth-child(2) .trace-icon  { animation: trace-icon-2 10s ease-out infinite; }
        .trace-step:nth-child(2) .trace-label { animation: trace-label-2 10s cubic-bezier(.2, .8, .3, 1.2) infinite; }

        @keyframes trace-icon-3 {
            0%, 21% { background: var(--af-paper); border-color: #c9dbbd; color: var(--af-forest-2); transform: scale(1); box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35); }
            24% { background: var(--af-forest-2); border-color: var(--af-forest-2); color: #fff; transform: scale(1.16); box-shadow: 0 0 0 7px rgba(76, 140, 74, .22), 0 10px 18px -6px rgba(31, 74, 44, .6); }
            27%, 88% { background: var(--af-forest-2); border-color: var(--af-forest-2); color: #fff; transform: scale(1.04); box-shadow: 0 0 0 4px rgba(76, 140, 74, .16), 0 8px 16px -6px rgba(31, 74, 44, .5); }
            95%, 100% { background: var(--af-paper); border-color: #c9dbbd; color: var(--af-forest-2); transform: scale(1); box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35); }
        }
        @keyframes trace-label-3 {
            0%, 21% { opacity: 0; transform: translate(-50%, calc(var(--dir) * -12px)) scale(.9); }
            26%, 88% { opacity: 1; transform: translate(-50%, 0) scale(1); }
            95%, 100% { opacity: 0; transform: translate(-50%, 0) scale(1); }
        }
        .trace-step:nth-child(3) .trace-icon  { animation: trace-icon-3 10s ease-out infinite; }
        .trace-step:nth-child(3) .trace-label { animation: trace-label-3 10s cubic-bezier(.2, .8, .3, 1.2) infinite; }

        @keyframes trace-icon-4 {
            0%, 31.5% { background: var(--af-paper); border-color: #c9dbbd; color: var(--af-forest-2); transform: scale(1); box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35); }
            34.5% { background: var(--af-forest-2); border-color: var(--af-forest-2); color: #fff; transform: scale(1.16); box-shadow: 0 0 0 7px rgba(76, 140, 74, .22), 0 10px 18px -6px rgba(31, 74, 44, .6); }
            37.5%, 88% { background: var(--af-forest-2); border-color: var(--af-forest-2); color: #fff; transform: scale(1.04); box-shadow: 0 0 0 4px rgba(76, 140, 74, .16), 0 8px 16px -6px rgba(31, 74, 44, .5); }
            95%, 100% { background: var(--af-paper); border-color: #c9dbbd; color: var(--af-forest-2); transform: scale(1); box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35); }
        }
        @keyframes trace-label-4 {
            0%, 31.5% { opacity: 0; transform: translate(-50%, calc(var(--dir) * -12px)) scale(.9); }
            36.5%, 88% { opacity: 1; transform: translate(-50%, 0) scale(1); }
            95%, 100% { opacity: 0; transform: translate(-50%, 0) scale(1); }
        }
        .trace-step:nth-child(4) .trace-icon  { animation: trace-icon-4 10s ease-out infinite; }
        .trace-step:nth-child(4) .trace-label { animation: trace-label-4 10s cubic-bezier(.2, .8, .3, 1.2) infinite; }

        @keyframes trace-icon-5 {
            0%, 42% { background: var(--af-paper); border-color: #c9dbbd; color: var(--af-forest-2); transform: scale(1); box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35); }
            45% { background: var(--af-forest-2); border-color: var(--af-forest-2); color: #fff; transform: scale(1.16); box-shadow: 0 0 0 7px rgba(76, 140, 74, .22), 0 10px 18px -6px rgba(31, 74, 44, .6); }
            48%, 88% { background: var(--af-forest-2); border-color: var(--af-forest-2); color: #fff; transform: scale(1.04); box-shadow: 0 0 0 4px rgba(76, 140, 74, .16), 0 8px 16px -6px rgba(31, 74, 44, .5); }
            95%, 100% { background: var(--af-paper); border-color: #c9dbbd; color: var(--af-forest-2); transform: scale(1); box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35); }
        }
        @keyframes trace-label-5 {
            0%, 42% { opacity: 0; transform: translate(-50%, calc(var(--dir) * -12px)) scale(.9); }
            47%, 88% { opacity: 1; transform: translate(-50%, 0) scale(1); }
            95%, 100% { opacity: 0; transform: translate(-50%, 0) scale(1); }
        }
        .trace-step:nth-child(5) .trace-icon  { animation: trace-icon-5 10s ease-out infinite; }
        .trace-step:nth-child(5) .trace-label { animation: trace-label-5 10s cubic-bezier(.2, .8, .3, 1.2) infinite; }

        @keyframes trace-icon-6 {
            0%, 52.5% { background: var(--af-paper); border-color: #c9dbbd; color: var(--af-forest-2); transform: scale(1); box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35); }
            55.5% { background: var(--af-forest-2); border-color: var(--af-forest-2); color: #fff; transform: scale(1.16); box-shadow: 0 0 0 7px rgba(76, 140, 74, .22), 0 10px 18px -6px rgba(31, 74, 44, .6); }
            58.5%, 88% { background: var(--af-forest-2); border-color: var(--af-forest-2); color: #fff; transform: scale(1.04); box-shadow: 0 0 0 4px rgba(76, 140, 74, .16), 0 8px 16px -6px rgba(31, 74, 44, .5); }
            95%, 100% { background: var(--af-paper); border-color: #c9dbbd; color: var(--af-forest-2); transform: scale(1); box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35); }
        }
        @keyframes trace-label-6 {
            0%, 52.5% { opacity: 0; transform: translate(-50%, calc(var(--dir) * -12px)) scale(.9); }
            57.5%, 88% { opacity: 1; transform: translate(-50%, 0) scale(1); }
            95%, 100% { opacity: 0; transform: translate(-50%, 0) scale(1); }
        }
        .trace-step:nth-child(6) .trace-icon  { animation: trace-icon-6 10s ease-out infinite; }
        .trace-step:nth-child(6) .trace-label { animation: trace-label-6 10s cubic-bezier(.2, .8, .3, 1.2) infinite; }

        @keyframes trace-icon-7 {
            0%, 63% { background: var(--af-paper); border-color: #c9dbbd; color: var(--af-forest-2); transform: scale(1); box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35); }
            66% { background: var(--af-forest-2); border-color: var(--af-forest-2); color: #fff; transform: scale(1.16); box-shadow: 0 0 0 7px rgba(76, 140, 74, .22), 0 10px 18px -6px rgba(31, 74, 44, .6); }
            69%, 88% { background: var(--af-forest-2); border-color: var(--af-forest-2); color: #fff; transform: scale(1.04); box-shadow: 0 0 0 4px rgba(76, 140, 74, .16), 0 8px 16px -6px rgba(31, 74, 44, .5); }
            95%, 100% { background: var(--af-paper); border-color: #c9dbbd; color: var(--af-forest-2); transform: scale(1); box-shadow: 0 4px 10px -4px rgba(31, 74, 44, .35); }
        }
        @keyframes trace-label-7 {
            0%, 63% { opacity: 0; transform: translate(-50%, calc(var(--dir) * -12px)) scale(.9); }
            68%, 88% { opacity: 1; transform: translate(-50%, 0) scale(1); }
            95%, 100% { opacity: 0; transform: translate(-50%, 0) scale(1); }
        }
        .trace-step:nth-child(7) .trace-icon  { animation: trace-icon-7 10s ease-out infinite; }
        .trace-step:nth-child(7) .trace-label { animation: trace-label-7 10s cubic-bezier(.2, .8, .3, 1.2) infinite; }

        /* Rótulo único bajo la línea: solo en móvil, sincronizado con la ruta */
        .trace-current {
            display: none;
            margin-top: 12px;
            text-align: center;
        }

        .trace-current-item {
            grid-area: 1 / 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            font-size: .95rem;
            font-weight: 700;
            color: var(--af-forest);
            opacity: 0;
        }

        .trace-current-item small {
            font-size: .6rem;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--af-muted);
        }

        @keyframes trace-current-1 {
            0% { opacity: 0; transform: translateY(6px); }
            2%, 9.5% { opacity: 1; transform: translateY(0); }
            10.5%, 100% { opacity: 0; transform: translateY(-4px); }
        }
        .trace-current-item:nth-child(1) { animation: trace-current-1 10s ease-out infinite; }
        @keyframes trace-current-2 {
            0%, 10.5% { opacity: 0; transform: translateY(6px); }
            12.5%, 20% { opacity: 1; transform: translateY(0); }
            21%, 100% { opacity: 0; transform: translateY(-4px); }
        }
        .trace-current-item:nth-child(2) { animation: trace-current-2 10s ease-out infinite; }
        @keyframes trace-current-3 {
            0%, 21% { opacity: 0; transform: translateY(6px); }
            23%, 30.5% { opacity: 1; transform: translateY(0); }
            31.5%, 100% { opacity: 0; transform: translateY(-4px); }
        }
        .trace-current-item:nth-child(3) { animation: trace-current-3 10s ease-out infinite; }
        @keyframes trace-current-4 {
            0%, 31.5% { opacity: 0; transform: translateY(6px); }
            33.5%, 41% { opacity: 1; transform: translateY(0); }
            42%, 100% { opacity: 0; transform: translateY(-4px); }
        }
        .trace-current-item:nth-child(4) { animation: trace-current-4 10s ease-out infinite; }
        @keyframes trace-current-5 {
            0%, 42% { opacity: 0; transform: translateY(6px); }
            44%, 51.5% { opacity: 1; transform: translateY(0); }
            52.5%, 100% { opacity: 0; transform: translateY(-4px); }
        }
        .trace-current-item:nth-child(5) { animation: trace-current-5 10s ease-out infinite; }
        @keyframes trace-current-6 {
            0%, 52.5% { opacity: 0; transform: translateY(6px); }
            54.5%, 62% { opacity: 1; transform: translateY(0); }
            63%, 100% { opacity: 0; transform: translateY(-4px); }
        }
        .trace-current-item:nth-child(6) { animation: trace-current-6 10s ease-out infinite; }
        @keyframes trace-current-7 {
            0%, 63% { opacity: 0; transform: translateY(6px); }
            65%, 88% { opacity: 1; transform: translateY(0); }
            95%, 100% { opacity: 0; transform: translateY(-4px); }
        }
        .trace-current-item:nth-child(7) { animation: trace-current-7 10s ease-out infinite; }

        @media (prefers-reduced-motion: reduce) {
            .trace-steps::after { animation: none; transform: scaleX(1); }
            .trace-step .trace-icon,
            .trace-step .trace-label { animation: none; }
        }

        /* ── Pie de página ── */
        .auth-copy {
            margin-top: 18px;
            text-align: center;
            font-size: .74rem;
            color: rgba(255, 255, 255, .85);
            text-shadow: 0 1px 3px rgba(0, 0, 0, .35);
        }

        /* ── Encabezado del formulario ── */
        .form-header { margin-bottom: 22px; text-align: center; }

        .form-header h2 {
            font-family: 'Bricolage Grotesque', 'Plus Jakarta Sans', sans-serif;
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--af-ink);
            margin-bottom: 4px;
        }

        .form-header p {
            font-size: .84rem;
            line-height: 1.5;
            color: var(--af-muted);
        }

        .form-header p strong { color: var(--af-ink-2); }

        /* ── Campos ── */
        .form-group { margin-bottom: 16px; }

        .form-group label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            color: var(--af-ink-2);
            margin-bottom: 6px;
        }

        .input-wrapper { position: relative; }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--af-muted);
            font-size: .85rem;
            pointer-events: none;
            transition: color .15s;
        }

        .form-control {
            width: 100%;
            padding: 11px 14px 11px 40px;
            background: var(--af-paper);
            border: 1px solid var(--af-line);
            border-radius: var(--af-radius);
            color: var(--af-ink);
            font-size: .9rem;
            font-family: inherit;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }

        .form-control::placeholder { color: #a7aea5; }

        .form-control:hover { border-color: #c7c2ae; }

        .form-control:focus {
            border-color: var(--af-leaf);
            box-shadow: 0 0 0 3px rgba(76, 140, 74, .18);
        }

        .input-wrapper:focus-within i { color: var(--af-forest-2); }

        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            padding-right: 38px;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8' fill='none'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%234a574c' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 12px 8px;
        }

        textarea.form-control {
            padding: 11px 14px;
            min-height: 110px;
            resize: vertical;
            line-height: 1.5;
        }

        .text-muted { color: var(--af-muted) !important; }
        small.text-muted { display: block; margin-top: 5px; font-size: .74rem; }

        /* ── Recordarme ── */
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 2px 0 20px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--af-forest-2);
            cursor: pointer;
        }

        .checkbox-wrapper label {
            margin: 0;
            font-size: .82rem;
            color: var(--af-ink-2);
            cursor: pointer;
        }

        /* ── Botón principal: al pasar el mouse recorre una mini ruta ── */
        .btn-login {
            position: relative;
            overflow: hidden;
            width: 100%;
            padding: 13px;
            background: var(--af-forest);
            border: none;
            border-radius: var(--af-radius);
            color: #fff;
            font-size: .95rem;
            font-weight: 700;
            font-family: inherit;
            letter-spacing: .01em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 6px 16px -6px rgba(31, 74, 44, .6);
            transition: transform .2s, box-shadow .2s;
        }

        /* línea dorada inferior, como la línea de la ruta */
        .btn-login::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--af-leaf), var(--af-harvest));
            transform: scaleX(0);
            transform-origin: left center;
            transition: transform .6s ease;
        }

        /* puntos como "estaciones" sobre la línea inferior; no ocupan espacio en la fila */
        .btn-login-dots {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            justify-content: space-around;
            padding: 0 18%;
            pointer-events: none;
            z-index: 1;
        }

        .btn-login-dots span {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .22);
            transition: background .2s ease;
        }

        /* flecha en el borde derecho: aparece al pasar el mouse */
        .btn-login .btn-login-arrow {
            position: absolute;
            right: 18px;
            top: 50%;
            margin-top: -.5em;
            line-height: 1;
            font-size: .85rem;
            opacity: 0;
            transition: opacity .25s ease, transform .25s ease;
        }

        .btn-login:hover,
        .btn-login:focus-visible {
            transform: translateY(-1px);
            box-shadow: 0 10px 22px -8px rgba(31, 74, 44, .7);
        }

        .btn-login:hover::after,
        .btn-login:focus-visible::after { transform: scaleX(1); }

        /* cada punto se enciende cuando la línea llega a él */
        .btn-login:hover .btn-login-dots span,
        .btn-login:focus-visible .btn-login-dots span { background: var(--af-harvest); transition-delay: .12s; }

        .btn-login:hover .btn-login-dots span:nth-child(2),
        .btn-login:focus-visible .btn-login-dots span:nth-child(2) { transition-delay: .28s; }

        .btn-login:hover .btn-login-dots span:nth-child(3),
        .btn-login:focus-visible .btn-login-dots span:nth-child(3) { transition-delay: .44s; }

        .btn-login:hover .btn-login-arrow,
        .btn-login:focus-visible .btn-login-arrow { opacity: 1; transform: translateX(4px); transition-delay: .2s; }

        .btn-login:active { transform: translateY(0); }

        .btn-login:focus-visible {
            outline: 3px solid rgba(200, 153, 63, .55);
            outline-offset: 2px;
        }

        .btn-login:disabled { opacity: .8; cursor: progress; transform: none; }

        /* ── Pie del formulario ── */
        .form-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--af-line);
        }

        .form-footer p { font-size: .82rem; color: var(--af-muted); }

        .form-footer a {
            color: var(--af-forest-2);
            font-weight: 600;
            text-decoration: none;
            border-bottom: 1px solid rgba(45, 106, 62, .3);
            transition: border-color .15s, color .15s;
        }

        .form-footer a:hover { color: var(--af-forest); border-color: var(--af-forest); }

        /* ── Alertas ── */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 10px;
            font-size: .82rem;
            margin-bottom: 18px;
            border: 1px solid;
        }

        .alert i { flex-shrink: 0; margin-top: 2px; }

        .alert-success {
            background: #eaf3e4;
            border-color: #c5dcb8;
            color: #2d5a2f;
        }

        .alert-danger {
            background: #fbece8;
            border-color: #efc9bf;
            color: var(--af-danger);
        }

        /* ── Ajustes para la vista de registro (sobre tarjeta clara) ── */
        .auth-card .rol-opcion label {
            border: 1.5px solid var(--af-line);
            background: var(--af-paper);
            color: var(--af-ink-2);
        }

        .auth-card .rol-opcion label i { color: var(--af-forest-2); }

        .auth-card .rol-opcion label:hover { border-color: #c7c2ae; }

        .auth-card .rol-opcion input:checked + label {
            border-color: var(--af-forest-2);
            background: var(--af-leaf-soft);
            color: var(--af-forest);
        }

        .auth-card .rol-opcion input:focus-visible + label {
            box-shadow: 0 0 0 3px rgba(76, 140, 74, .25);
        }

        .auth-card .phone-prefijo-combo {
            background: var(--af-paper);
            border-color: var(--af-line);
            border-radius: var(--af-radius);
        }

        .auth-card .phone-prefijo-combo:focus-within,
        .auth-card .phone-line .phone-numero-wrap input:focus {
            border-color: var(--af-leaf);
            box-shadow: 0 0 0 3px rgba(76, 140, 74, .18);
        }

        .auth-card .phone-prefijo-combo input { color: var(--af-ink); font-family: inherit; }
        .auth-card .phone-prefijo-btn { color: var(--af-muted); }
        .auth-card .phone-prefijo-btn:hover { color: var(--af-ink); }

        .auth-card .phone-line .phone-numero-wrap input {
            font-family: inherit;
            background: var(--af-paper);
            border-color: var(--af-line);
            border-radius: var(--af-radius);
            color: var(--af-ink);
        }

        .auth-card .phone-line .phone-numero-wrap i { color: var(--af-muted); }

        .auth-card .licencias-picker {
            --lp-border: var(--af-line);
            --lp-bg: var(--af-paper);
            --lp-bg-hover: #f3f1e8;
            --lp-text: var(--af-ink);
            --lp-muted: var(--af-muted);
            --lp-accent: var(--af-forest-2);
            --lp-accent-soft: var(--af-leaf-soft);
            --lp-accent-glow: rgba(76, 140, 74, .25);
            --lp-divider: var(--af-line);
        }

        .auth-card .fa-check-circle { color: var(--af-forest-2); }

        /* ── Responsive ── */
        @media (max-width: 520px) {
            .auth-layout { padding: 20px 12px; justify-content: center; }
            .auth-card { padding: 28px 20px 22px; border-radius: 22px; }
            .auth-card::before { inset: 6px; border-radius: 17px; }
            .auth-app-name { font-size: 2.3rem; }
            .trace { margin: 18px 0 22px; padding: 14px 0 12px; }
            .trace-title { font-size: .66rem; letter-spacing: .14em; }
            .trace-title { margin-bottom: 14px; }
            .trace-steps::before,
            .trace-steps::after { top: 18px; }
            .trace-step { padding: 0; }
            .trace-icon { width: 36px; height: 36px; font-size: .86rem; }
            .trace .trace-step .trace-label { display: none; }
            .trace-current { display: grid; }
        }

        /* Móvil sin animaciones: se vuelven a mostrar los nombres bajo cada ícono */
        @media (max-width: 520px) and (prefers-reduced-motion: reduce) {
            .trace-current { display: none; }
            .trace-step { padding: 30px 0; }
            .trace-steps::before,
            .trace-steps::after { top: 46.5px; }
            .trace .trace-step .trace-label { display: flex; justify-content: center; font-size: .56rem; top: calc(100% - 26px); }
            .trace-step:nth-child(even) .trace-label { bottom: calc(100% - 26px); }
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="auth-bg" aria-hidden="true"></div>

<div class="auth-layout">
    <div class="auth-card">

        <!-- Marca -->
        <div class="auth-brand">
            <div class="auth-brand-row">
                <span class="auth-app-name">AgroFusion</span>
            </div>
            <span class="auth-tagline">Sistema de Trazabilidad Agroindustrial</span>

            <!-- Ruta de trazabilidad (decorativa) -->
            <div class="trace" aria-label="Cadena de trazabilidad: semilla, cosecha, industrialización, empaquetado, mayorista, minorista y cliente final">
                <span class="trace-title">Del campo a la mesa</span>
                <ol class="trace-steps" aria-hidden="true">
                    <li class="trace-step"><span class="trace-icon"><i class="fas fa-seedling"></i></span><span class="trace-label">Semilla</span></li>
                    <li class="trace-step"><span class="trace-icon"><i class="fas fa-wheat-awn"></i></span><span class="trace-label">Cosecha</span></li>
                    <li class="trace-step"><span class="trace-icon"><i class="fas fa-industry"></i></span><span class="trace-label">Industrialización</span></li>
                    <li class="trace-step"><span class="trace-icon"><i class="fas fa-box"></i></span><span class="trace-label">Empaquetado</span></li>
                    <li class="trace-step"><span class="trace-icon"><i class="fas fa-warehouse"></i></span><span class="trace-label">Mayorista</span></li>
                    <li class="trace-step"><span class="trace-icon"><i class="fas fa-truck"></i></span><span class="trace-label">Minorista</span></li>
                    <li class="trace-step"><span class="trace-icon"><i class="fas fa-user"></i></span><span class="trace-label">Cliente final</span></li>
                </ol>
                <div class="trace-current" aria-hidden="true">
                    <span class="trace-current-item"><small>Paso 1 de 7</small>Semilla</span>
                    <span class="trace-current-item"><small>Paso 2 de 7</small>Cosecha</span>
                    <span class="trace-current-item"><small>Paso 3 de 7</small>Industrialización</span>
                    <span class="trace-current-item"><small>Paso 4 de 7</small>Empaquetado</span>
                    <span class="trace-current-item"><small>Paso 5 de 7</small>Mayorista</span>
                    <span class="trace-current-item"><small>Paso 6 de 7</small>Minorista</span>
                    <span class="trace-current-item"><small>Paso 7 de 7</small>Cliente final</span>
                </div>
            </div>
        </div>

        <!-- Content (login/register views inject here) -->
        @yield('content')

    </div>

    <p class="auth-copy">
        &copy; {{ date('Y') }} AgroFusion &middot; Tecnología para el campo
    </p>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
function limpiarEstadoLogin() {
    document.body.classList.remove('login-notif-modal-open', 'modal-open');
    document.querySelectorAll('.login-notif-scrim').forEach(function (el) {
        el.classList.remove('is-visible');
    });
}

document.addEventListener('DOMContentLoaded', limpiarEstadoLogin);
window.addEventListener('pageshow', limpiarEstadoLogin);
</script>
@stack('scripts')
</body>
</html>
