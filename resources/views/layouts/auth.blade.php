<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Fusion-Proyectos | Acceso')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #1a3a1a 0%, #2c5530 50%, #3d7a44 100%);
            position: relative;
            overflow-x: hidden;
        }
        
        /* Patrón de fondo */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(255,255,255,0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.05) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(255,255,255,0.03) 0%, transparent 50%);
            pointer-events: none;
        }
        
        /* Lado izquierdo - Branding */
        .brand-side {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            color: white;
            position: relative;
        }
        
        .brand-side::after {
            content: '';
            position: absolute;
            right: 0;
            top: 10%;
            bottom: 10%;
            width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(255,255,255,0.2), transparent);
        }
        
        .brand-logo {
            width: 120px;
            height: 120px;
            background: rgba(255,255,255,0.15);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        .brand-logo i {
            font-size: 4rem;
            color: #90EE90;
        }
        
        .brand-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .brand-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            text-align: center;
            max-width: 400px;
            line-height: 1.8;
        }
        
        .features {
            margin-top: 50px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 25px;
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            background: rgba(255,255,255,0.15);
            transform: translateX(10px);
        }
        
        .feature-item i {
            font-size: 1.5rem;
            color: #90EE90;
            width: 40px;
            text-align: center;
        }
        
        .feature-item span {
            font-size: 1rem;
        }
        
        /* Lado derecho - Formulario */
        .form-side {
            width: 500px;
            min-height: 100vh;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            position: relative;
        }
        
        .form-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #2c5530, #4a7c59, #90EE90);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .form-header h2 {
            font-size: 2rem;
            color: #1a3a1a;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .form-header p {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
            font-size: 0.95rem;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            transition: color 0.3s;
        }
        
        .form-control {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #2c5530;
            background: white;
            box-shadow: 0 0 0 4px rgba(44, 85, 48, 0.1);
        }
        
        .form-control:focus + i,
        .input-wrapper:focus-within i {
            color: #2c5530;
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #2c5530, #3d7a44);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(44, 85, 48, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(44, 85, 48, 0.4);
            background: linear-gradient(135deg, #1a3a1a, #2c5530);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .form-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e9ecef;
        }
        
        .form-footer p {
            color: #6c757d;
        }
        
        .form-footer a {
            color: #2c5530;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .form-footer a:hover {
            color: #1a3a1a;
            text-decoration: underline;
        }
        
        /* Alertas */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        
        .alert i {
            font-size: 1.2rem;
        }
        
        /* Checkbox remember */
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .checkbox-wrapper input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #2c5530;
            cursor: pointer;
        }
        
        .checkbox-wrapper label {
            margin: 0;
            cursor: pointer;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .forgot-link {
            color: #2c5530;
            font-size: 0.9rem;
            text-decoration: none;
            font-weight: 500;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }
        
        /* Copyright */
        .copyright {
            text-align: center;
            margin-top: 40px;
            color: #adb5bd;
            font-size: 0.85rem;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            body {
                flex-direction: column;
            }
            
            .brand-side {
                padding: 40px 20px;
                min-height: auto;
            }
            
            .brand-side::after {
                display: none;
            }
            
            .features {
                display: none;
            }
            
            .brand-title {
                font-size: 2rem;
            }
            
            .brand-subtitle {
                font-size: 1rem;
            }
            
            .form-side {
                width: 100%;
                min-height: auto;
                padding: 40px 30px;
                border-radius: 30px 30px 0 0;
                margin-top: -30px;
            }
        }
        
        /* Animaciones de entrada */
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .brand-side > * {
            animation: slideInLeft 0.6s ease-out;
        }
        
        .form-side > * {
            animation: slideInRight 0.6s ease-out;
        }
        
        .feature-item:nth-child(1) { animation-delay: 0.1s; }
        .feature-item:nth-child(2) { animation-delay: 0.2s; }
        .feature-item:nth-child(3) { animation-delay: 0.3s; }
        .feature-item:nth-child(4) { animation-delay: 0.4s; }
    </style>
    
    @stack('styles')
</head>
<body>

<div class="brand-side">
    <div class="brand-logo">
        <i class="fas fa-seedling"></i>
    </div>
    <h1 class="brand-title">Fusion-Proyectos</h1>
    <p class="brand-subtitle">Sistema integral de gestión agrícola para optimizar tu producción y maximizar resultados</p>
    
    <div class="features">
        <div class="feature-item">
            <i class="fas fa-map-marked-alt"></i>
            <span>Gestión de lotes con mapas</span>
        </div>
        <div class="feature-item">
            <i class="fas fa-cloud-sun"></i>
            <span>Clima en tiempo real</span>
        </div>
        <div class="feature-item">
            <i class="fas fa-chart-line"></i>
            <span>Reportes y estadísticas</span>
        </div>
        <div class="feature-item">
            <i class="fas fa-boxes"></i>
            <span>Control de inventario</span>
        </div>
    </div>
</div>

<div class="form-side">
    @yield('content')
    
    <p class="copyright">
        <i class="fas fa-leaf"></i> {{ date('Y') }} Fusion-Proyectos - Tecnología para el campo
    </p>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
@stack('scripts')
</body>
</html>