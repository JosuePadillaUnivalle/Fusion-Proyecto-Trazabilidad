<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'AgroNexus | Acceso')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            background: #080d0f;
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
            overflow: hidden;
            position: relative;
        }

        /* ── Animated background ── */
        .auth-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .auth-bg-gradient {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 40%, rgba(5, 150, 105, 0.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 70%, rgba(16, 185, 129, 0.08) 0%, transparent 60%),
                radial-gradient(ellipse 100% 100% at 50% 0%, rgba(2, 44, 34, 0.6) 0%, transparent 70%);
        }

        .auth-bg-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* Glowing orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            animation: float 8s ease-in-out infinite;
        }

        .orb-1 {
            width: 500px; height: 500px;
            background: rgba(16, 185, 129, 0.06);
            top: -150px; left: -100px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 400px; height: 400px;
            background: rgba(5, 150, 105, 0.08);
            bottom: -100px; right: -80px;
            animation-delay: -3s;
        }

        .orb-3 {
            width: 300px; height: 300px;
            background: rgba(52, 211, 153, 0.04);
            top: 40%; left: 60%;
            animation-delay: -5s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%       { transform: translate(30px, -20px) scale(1.05); }
            66%       { transform: translate(-20px, 15px) scale(0.97); }
        }

        /* ── Card ── */
        .auth-card {
            position: relative;
            z-index: 10;
            width: 420px;
            max-width: calc(100vw - 32px);
            background: rgba(255, 255, 255, .035);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 20px;
            padding: 40px 36px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow:
                0 0 0 1px rgba(16, 185, 129, .08),
                0 32px 64px rgba(0, 0, 0, .5),
                inset 0 1px 0 rgba(255,255,255,.06);
        }

        /* ── Logo / Brand ── */
        .auth-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 36px;
        }

        .auth-logo {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #059669, #10b981);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, .15), 0 8px 24px rgba(16, 185, 129, .25);
        }

        .auth-logo i { color: #fff; font-size: 1.3rem; }

        .auth-app-name {
            font-size: 1.25rem;
            font-weight: 800;
            color: #f1f5f9;
            letter-spacing: -.01em;
        }

        .auth-tagline {
            font-size: .75rem;
            color: #475569;
            margin-top: 3px;
        }

        /* ── Heading ── */
        .auth-heading { margin-bottom: 28px; }

        .auth-heading h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #e2e8f0;
            margin-bottom: 4px;
        }

        .auth-heading p {
            font-size: .82rem;
            color: #64748b;
        }

        /* ── Alerts ── */
        .auth-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 10px;
            font-size: .82rem;
            margin-bottom: 20px;
        }

        .auth-alert i { margin-top: 1px; flex-shrink: 0; }

        .auth-alert.success {
            background: rgba(16, 185, 129, .1);
            border: 1px solid rgba(16, 185, 129, .2);
            color: #6ee7b7;
        }

        .auth-alert.danger {
            background: rgba(239, 68, 68, .1);
            border: 1px solid rgba(239, 68, 68, .18);
            color: #fca5a5;
        }

        /* ── Form ── */
        .auth-form-group { margin-bottom: 18px; }

        .auth-label {
            display: block;
            font-size: .78rem;
            font-weight: 500;
            color: #94a3b8;
            margin-bottom: 7px;
            letter-spacing: .01em;
        }

        .auth-input-wrap { position: relative; }

        .auth-input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
            font-size: .82rem;
            pointer-events: none;
            transition: color .15s;
        }

        .auth-input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 10px;
            color: #e2e8f0;
            font-size: .88rem;
            font-family: 'Inter', sans-serif;
            transition: border .15s, box-shadow .15s, background .15s;
            outline: none;
        }

        .auth-input::placeholder { color: #334155; }

        .auth-input:focus {
            background: rgba(255,255,255,.06);
            border-color: rgba(16, 185, 129, .5);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, .12);
        }

        .auth-input:focus + .auth-input-icon { color: #10b981; }
        .auth-input-wrap:focus-within .auth-input-icon { color: #10b981; }

        /* ── Remember row ── */
        .auth-remember {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .auth-check-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .8rem;
            color: #64748b;
            cursor: pointer;
        }

        .auth-check-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #10b981;
            cursor: pointer;
        }

        /* ── Submit button ── */
        .auth-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #059669, #10b981);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: .9rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all .18s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 16px rgba(16, 185, 129, .25);
            letter-spacing: .01em;
        }

        .auth-btn:hover {
            background: linear-gradient(135deg, #047857, #059669);
            box-shadow: 0 8px 24px rgba(16, 185, 129, .35);
            transform: translateY(-1px);
        }

        .auth-btn:active { transform: translateY(0); }

        /* ── Footer link ── */
        .auth-footer {
            text-align: center;
            margin-top: 22px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,.06);
        }

        .auth-footer p { font-size: .8rem; color: #475569; }

        .auth-footer a {
            color: #10b981;
            font-weight: 600;
            text-decoration: none;
            transition: color .15s;
        }

        .auth-footer a:hover { color: #6ee7b7; }

        /* ── Bottom copyright ── */
        .auth-copy {
            position: fixed;
            bottom: 20px;
            left: 0; right: 0;
            text-align: center;
            font-size: .72rem;
            color: #1e293b;
            z-index: 5;
        }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .auth-card { padding: 30px 22px; }
            .auth-heading h2 { font-size: 1.15rem; }
        }

        /* Compatibility: view uses .alert, .form-group, .form-control, .input-wrapper ── */
        .form-header { margin-bottom: 28px; }
        .form-header h2 { font-size: 1.35rem; font-weight: 700; color: #e2e8f0; margin-bottom: 4px; }
        .form-header p  { font-size: .82rem; color: #64748b; }

        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            font-size: .78rem;
            font-weight: 500;
            color: #94a3b8;
            margin-bottom: 7px;
        }

        .input-wrapper { position: relative; }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
            font-size: .82rem;
            pointer-events: none;
            transition: color .15s;
        }

        .form-control {
            width: 100%;
            padding: 11px 14px 11px 40px;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 10px;
            color: #e2e8f0;
            font-size: .88rem;
            font-family: 'Inter', sans-serif;
            transition: border .15s, box-shadow .15s, background .15s;
            outline: none;
        }

        .form-control::placeholder { color: #334155; }

        .form-control:focus {
            background: rgba(255,255,255,.06);
            border-color: rgba(16,185,129,.5);
            box-shadow: 0 0 0 3px rgba(16,185,129,.12);
        }

        .form-control:focus + i { color: #10b981; }
        .input-wrapper:focus-within i { color: #10b981; }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: #10b981;
            cursor: pointer;
        }

        .checkbox-wrapper label {
            margin: 0;
            font-size: .8rem;
            color: #64748b;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #059669, #10b981);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: .9rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all .18s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 16px rgba(16,185,129,.25);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #047857, #059669);
            box-shadow: 0 8px 24px rgba(16,185,129,.35);
            transform: translateY(-1px);
        }

        .form-footer {
            text-align: center;
            margin-top: 22px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,.06);
        }

        .form-footer p  { font-size: .8rem; color: #475569; }
        .form-footer a  { color: #10b981; font-weight: 600; text-decoration: none; }
        .form-footer a:hover { color: #6ee7b7; }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 10px;
            font-size: .82rem;
            margin-bottom: 20px;
            border: 1px solid;
        }

        .alert-success {
            background: rgba(16,185,129,.1);
            border-color: rgba(16,185,129,.2);
            color: #6ee7b7;
        }

        .alert-danger {
            background: rgba(239,68,68,.1);
            border-color: rgba(239,68,68,.18);
            color: #fca5a5;
        }

        .alert i { flex-shrink: 0; }

        .copyright {
            text-align: center;
            margin-top: 26px;
            font-size: .72rem;
            color: #1e293b;
        }
    </style>

    @stack('styles')
</head>
<body>

<!-- Background -->
<div class="auth-bg">
    <div class="auth-bg-gradient"></div>
    <div class="auth-bg-grid"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<!-- Card -->
<div class="auth-card">

    <!-- Brand -->
    <div class="auth-brand">
        <div class="auth-logo">
            <i class="fas fa-seedling"></i>
        </div>
        <span class="auth-app-name">AgroNexus</span>
        <span class="auth-tagline">Sistema integral de gestión agrícola</span>
    </div>

    <!-- Content (login/register views inject here) -->
    @yield('content')

</div>

<p class="auth-copy">
    &copy; {{ date('Y') }} AgroNexus &middot; Tecnología para el campo
</p>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
@stack('scripts')
</body>
</html>
