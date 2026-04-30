<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte AgroNexus</title>
    <style>
        @page {
            margin: 100px 25px 60px 25px;
            /* Margen para Header y Footer fijos */
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #2c3e50;
            line-height: 1.4;
        }

        /* Branding Colors */
        :root {
            --primary: #2c5530;
            /* Verde AgroNexus */
            --secondary: #4a7c59;
            /* Verde Claro */
            --accent: #e67e22;
            /* Naranja para totales/alertas */
            --light: #f8f9fa;
            --border: #dee2e6;
        }

        /* Header Fijo */
        header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 70px;
            border-bottom: 2px solid #2c5530;
        }

        header .logo {
            float: left;
            width: 200px;
        }

        header .logo h1 {
            margin: 0;
            color: #2c5530;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        header .logo span {
            color: #4a7c59;
            font-weight: 300;
        }

        header .company-info {
            float: right;
            text-align: right;
            font-size: 9px;
            color: #7f8c8d;
            padding-top: 5px;
        }

        /* Footer Fijo */
        footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #dee2e6;
            color: #7f8c8d;
            font-size: 9px;
            text-align: center;
            padding-top: 10px;
        }

        footer .page-number:after {
            content: "Página " counter(page);
        }

        /* Títulos de Reporte */
        .report-title {
            text-align: center;
            margin-bottom: 30px;
            margin-top: 0;
        }

        .report-title h2 {
            margin: 0;
            font-size: 20px;
            color: #2c5530;
            text-transform: uppercase;
        }

        .report-title p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #666;
        }

        /* Tarjetas de Resumen (KPIs) */
        .summary-cards {
            width: 100%;
            margin-bottom: 25px;
            border-spacing: 10px 0;
            /* Espacio entre celdas */
            border-collapse: separate;
        }

        .card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
        }

        .card .label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .card .value {
            display: block;
            font-size: 14px;
            font-weight: bold;
            color: #2c5530;
        }

        /* Tablas */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.data-table th,
        table.data-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }

        table.data-table th {
            background-color: #2c5530;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            vertical-align: middle;
        }

        table.data-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table.data-table td.numeric {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
            /* Alineación numérica mejorada */
        }

        /* Totales Finales */
        .total-section {
            width: 100%;
            margin-top: 20px;
            text-align: right;
        }

        .total-box {
            display: inline-block;
            background: #2c5530;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .total-box span {
            display: block;
        }

        .total-label {
            font-size: 10px;
            opacity: 0.9;
        }

        .total-value {
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <header>
        <div class="logo">
            <h1>Agro<span>Nexus</span></h1>
        </div>
        <div class="company-info">
            <p>Sistema de Gestión Integral</p>
            <p>Generado por: {{ auth()->user()->nombre ?? 'Sistema' }}</p>
            <p>{{ now()->format('d/m/Y H:i A') }}</p>
        </div>
    </header>

    <footer>
        <span class="page-number"></span> | AgroNexus &copy; {{ date('Y') }} - Documento Confidencial
    </footer>

    <main>
        @yield('content')
    </main>

</body>

</html>