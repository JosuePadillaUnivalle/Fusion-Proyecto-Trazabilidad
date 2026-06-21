{{-- KPI / small-box ejecutivo — sin gradientes LED. Incluir tras cada modulo-*-styles --}}
<style>
[class*="modulo-"] .small-box,
.envios-wrap .metric-card.small-box {
    background: #fff !important;
    color: #1e293b !important;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: none !important;
    overflow: hidden;
}
[class*="modulo-"] .small-box,
.envios-wrap .metric-card.small-box,
.small-box {
    transition: none !important;
}
[class*="modulo-"] .small-box:hover,
.envios-wrap .metric-card.small-box:hover,
.small-box:hover {
    transform: none !important;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .06) !important;
}
[class*="modulo-"] .small-box .inner h3,
.envios-wrap .metric-card .inner h3 {
    font-size: 1.5rem;
    font-weight: 700;
}
[class*="modulo-"] .small-box .inner p,
.envios-wrap .metric-card .inner p {
    color: #64748b;
    font-size: .78rem;
}
/* Iconos decorativos — fijos, esquina superior derecha (como Lotes) */
.small-box .icon,
.small-box > .icon,
.envios-wrap .metric-card .icon {
    position: absolute !important;
    right: 10px !important;
    top: 10px !important;
    bottom: auto !important;
    left: auto !important;
    transform: none !important;
    transition: none !important;
    opacity: 1 !important;
    font-size: 60px !important;
    line-height: 1 !important;
    z-index: 0;
    color: rgba(100, 116, 139, 0.18) !important;
}
.small-box:hover .icon,
.small-box:hover > .icon,
.small-box .icon:hover,
.small-box > .icon:hover {
    transform: none !important;
    transition: none !important;
}
.small-box .icon > i,
.small-box > .icon > i {
    color: inherit !important;
}
.small-box-green .icon,
.small-box-green > .icon,
.bg-success.small-box .icon,
.envios-wrap .small-box.bg-success .icon {
    color: rgba(44, 85, 48, 0.18) !important;
}
.small-box-blue .icon,
.small-box-blue > .icon,
.bg-info.small-box .icon,
.bg-primary.small-box .icon,
.envios-wrap .small-box.bg-info .icon,
.envios-wrap .small-box.bg-primary .icon {
    color: rgba(37, 99, 235, 0.18) !important;
}
.small-box-yellow .icon,
.small-box-yellow > .icon,
.bg-warning.small-box .icon,
.envios-wrap .small-box.bg-warning .icon {
    color: rgba(217, 119, 6, 0.18) !important;
}
.small-box-orange .icon,
.small-box-orange > .icon,
.envios-wrap .small-box.bg-orange .icon {
    color: rgba(234, 88, 12, 0.18) !important;
}
.small-box-purple .icon,
.small-box-purple > .icon,
.bg-purple.small-box .icon {
    color: rgba(109, 40, 217, 0.18) !important;
}
.small-box-red .icon,
.small-box-red > .icon,
.bg-danger.small-box .icon {
    color: rgba(185, 28, 28, 0.18) !important;
}
.small-box-teal .icon,
.small-box-teal > .icon {
    color: rgba(13, 148, 136, 0.18) !important;
}
.small-box-indigo .icon,
.small-box-indigo > .icon {
    color: rgba(79, 70, 229, 0.18) !important;
}
.small-box-brand .icon,
.small-box-brand > .icon {
    color: rgba(44, 85, 48, 0.18) !important;
}
[class*="modulo-"] .small-box .small-box-footer,
.envios-wrap .metric-card .small-box-footer {
    background: #f8fafc !important;
    color: #64748b !important;
    border-top: 1px solid #e2e8f0;
    text-align: center;
    padding: 10px 18px 12px !important;
}
[class*="modulo-"] .small-box-green,
.envios-wrap .small-box.bg-success {
    border-top: 3px solid #2c5530;
}
[class*="modulo-"] .small-box-green .inner h3 { color: #2c5530 !important; }
[class*="modulo-"] .small-box-blue,
.envios-wrap .small-box.bg-info,
.envios-wrap .small-box.bg-primary {
    border-top: 3px solid #2563eb;
}
[class*="modulo-"] .small-box-blue .inner h3 { color: #2563eb !important; }
[class*="modulo-"] .small-box-yellow,
.envios-wrap .small-box.bg-warning {
    border-top: 3px solid #d97706;
}
[class*="modulo-"] .small-box-yellow .inner h3 { color: #d97706 !important; }
[class*="modulo-"] .small-box-purple {
    border-top: 3px solid #6d28d9;
}
[class*="modulo-"] .small-box-purple .inner h3 { color: #6d28d9 !important; }
[class*="modulo-"] .small-box-red {
    border-top: 3px solid #b91c1c;
}
[class*="modulo-"] .small-box-red .inner h3 { color: #b91c1c !important; }
[class*="modulo-"] .small-box-orange,
.envios-wrap .small-box.bg-orange {
    border-top: 3px solid #ea580c;
}
[class*="modulo-"] .small-box-orange .inner h3 { color: #ea580c !important; }
[class*="modulo-"] .small-box-teal {
    border-top: 3px solid #0d9488;
}
[class*="modulo-"] .small-box-teal .inner h3 { color: #0d9488 !important; }
[class*="modulo-"] .small-box-indigo {
    border-top: 3px solid #4f46e5;
}
[class*="modulo-"] .small-box-indigo .inner h3 { color: #4f46e5 !important; }
</style>
