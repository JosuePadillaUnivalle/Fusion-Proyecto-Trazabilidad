<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Error del servidor | AgroFusion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif; min-height: 100vh; background: #080d0f;
            display: flex; align-items: center; justify-content: center; -webkit-font-smoothing: antialiased; overflow: hidden; position: relative;
        }
        .bg { position: fixed; inset: 0; z-index: 0; overflow: hidden; }
        .bg-gradient {
            position: absolute; inset: 0;
            background: radial-gradient(ellipse 80% 60% at 20% 40%, rgba(124,58,237,.1) 0%, transparent 60%),
                        radial-gradient(ellipse 60% 80% at 80% 70%, rgba(109,40,217,.07) 0%, transparent 60%);
        }
        .bg-grid {
            position: absolute; inset: 0;
            background-image: linear-gradient(rgba(255,255,255,.018) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.018) 1px, transparent 1px);
            background-size: 48px 48px;
        }
        .orb { position: absolute; border-radius: 50%; filter: blur(80px); animation: float 8s ease-in-out infinite; }
        .orb-1 { width: 400px; height: 400px; background: rgba(124,58,237,.04); top: -100px; left: -80px; }
        .orb-2 { width: 350px; height: 350px; background: rgba(109,40,217,.05); bottom: -80px; right: -60px; animation-delay: -3s; }
        @keyframes float {
            0%, 100% { transform: translate(0,0) scale(1); }
            33% { transform: translate(25px,-18px) scale(1.04); }
            66% { transform: translate(-18px,12px) scale(0.97); }
        }
        .card {
            position: relative; z-index: 10; width: 440px; max-width: calc(100vw - 32px);
            background: rgba(255,255,255,.032); border: 1px solid rgba(255,255,255,.07); border-radius: 20px;
            padding: 44px 40px; backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 0 0 1px rgba(124,58,237,.07), 0 32px 64px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.05);
            text-align: center;
        }
        .icon-wrap {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, rgba(124,58,237,.15), rgba(109,40,217,.1));
            border: 1px solid rgba(124,58,237,.18); border-radius: 18px;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 22px;
            box-shadow: 0 0 0 6px rgba(124,58,237,.07), 0 8px 24px rgba(124,58,237,.12);
        }
        .icon-wrap i { font-size: 1.6rem; color: #a78bfa; }
        .code {
            font-size: .7rem; font-weight: 700; letter-spacing: .15em; text-transform: uppercase;
            color: #a78bfa; background: rgba(124,58,237,.1); border: 1px solid rgba(124,58,237,.2);
            padding: 3px 10px; border-radius: 20px; display: inline-block; margin-bottom: 14px;
        }
        h1 { font-size: 1.5rem; font-weight: 800; color: #f1f5f9; margin-bottom: 10px; letter-spacing: -.01em; }
        p { font-size: .85rem; color: #64748b; line-height: 1.6; margin-bottom: 28px; }
        .btn-wrap { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: 7px; padding: 10px 20px;
            border-radius: 10px; font-size: .84rem; font-weight: 600; font-family: 'Inter', sans-serif;
            text-decoration: none; cursor: pointer; border: none; transition: all .16s;
        }
        .btn-primary { background: linear-gradient(135deg, #059669, #10b981); color: #fff; box-shadow: 0 4px 14px rgba(16,185,129,.25); }
        .btn-primary:hover { background: linear-gradient(135deg, #047857, #059669); transform: translateY(-1px); }
        .btn-ghost { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08); color: #94a3b8; }
        .btn-ghost:hover { background: rgba(255,255,255,.09); color: #e2e8f0; transform: translateY(-1px); }
        .brand { display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 32px; text-decoration: none; }
        .brand-icon { width: 30px; height: 30px; background: linear-gradient(135deg, #059669, #10b981); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .brand-icon i { color: #fff; font-size: .85rem; }
        .brand-name { font-size: .9rem; font-weight: 700; color: #f1f5f9; }
    </style>
</head>
<body>
<div class="bg">
    <div class="bg-gradient"></div>
    <div class="bg-grid"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
</div>
<div class="card">
    <a href="{{ url('/') }}" class="brand">
        <div class="brand-icon"><i class="fas fa-seedling"></i></div>
        <span class="brand-name">AgroFusion</span>
    </a>
    <div class="icon-wrap"><i class="fas fa-server"></i></div>
    <span class="code">500 &middot; Error interno</span>
    <h1>Algo salió mal</h1>
    <p>Ocurrió un error inesperado en el servidor.<br>Intentá de nuevo en unos instantes.</p>
    <div class="btn-wrap">
        <a href="{{ url('/dashboard') }}" class="btn btn-primary">
            <i class="fas fa-home"></i> Ir al Dashboard
        </a>
        <button class="btn btn-ghost" onclick="window.location.reload()">
            <i class="fas fa-redo"></i> Reintentar
        </button>
    </div>
</div>
</body>
</html>
