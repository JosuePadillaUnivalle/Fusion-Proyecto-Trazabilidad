<style>
    :root { --primary-color: #2c5530; --secondary-color: #4a7c59; }
    .lote-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white; border-radius: 15px; padding: 25px; margin-bottom: 20px;
    }
    .lote-header h2 { margin: 0; font-weight: 700; }
    .estado-badge { padding: 8px 20px; border-radius: 25px; font-weight: 600; font-size: 0.9rem; }
    .small-box {
        border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05); transition: transform 0.3s ease; margin-bottom: 20px;
    }
    .small-box:hover { transform: translateY(-2px); }
    .small-box .icon { font-size: 70px !important; }
    .small-box-green { background: linear-gradient(135deg, #28a745, #34ce57) !important; }
    .small-box-blue { background: linear-gradient(135deg, #17a2b8, #20c997) !important; }
    .small-box-yellow { background: linear-gradient(135deg, #ffc107, #ffca2c) !important; }
    .small-box-red { background: linear-gradient(135deg, #dc3545, #e74a3b) !important; }
    .lote-section-card {
        border-radius: 12px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border: none; margin-bottom: 20px;
    }
    .lote-section-nav .nav-link {
        border-radius: 8px; font-weight: 600; color: #6c757d; padding: 10px 18px; margin-right: 6px;
    }
    .lote-section-nav .nav-link.active {
        background: var(--primary-color); color: #fff;
    }
    .lote-section-nav .nav-link:not(.active):hover { background: #e8f5e9; color: var(--primary-color); }
    .info-table td { padding: 10px 0; border-bottom: 1px solid #f1f3f4; }
    .info-table td:first-child { font-weight: 600; color: #495057; width: 40%; }
    .btn-action { border-radius: 8px; padding: 10px 20px; font-weight: 500; }
    #map { height: 420px; width: 100%; border-radius: 10px; border: 2px solid #e9ecef; }
    .timeline { position: relative; padding: 20px 0; }
    .timeline::before {
        content: ''; position: absolute; left: 20px; top: 0; bottom: 0; width: 3px;
        background: linear-gradient(to bottom, var(--primary-color), #e9ecef);
    }
    .timeline-item { position: relative; padding-left: 60px; padding-bottom: 25px; }
    .timeline-item:last-child { padding-bottom: 0; }
    .timeline-icon {
        position: absolute; left: 5px; width: 34px; height: 34px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; color: white; font-size: 14px; z-index: 1;
    }
    .timeline-icon.success { background: #28a745; }
    .timeline-icon.info { background: #17a2b8; }
    .timeline-icon.warning { background: #ffc107; color: #1a252f; }
    .timeline-icon.primary { background: var(--primary-color); }
    .timeline-content { background: #f8f9fc; border-radius: 10px; padding: 15px; border-left: 3px solid #dee2e6; }
    .timeline-content.success { border-left-color: #28a745; }
    .timeline-content.info { border-left-color: #17a2b8; }
    .timeline-content.warning { border-left-color: #ffc107; }
    .timeline-content.primary { border-left-color: var(--primary-color); }
    .timeline-date { font-size: 0.8rem; color: #6c757d; margin-bottom: 5px; }
    .timeline-title { font-weight: 600; color: #1a252f; margin-bottom: 5px; }
    .timeline-desc { font-size: 0.9rem; color: #495057; margin: 0; }
    .timeline-user { font-size: 0.8rem; color: #6c757d; margin-top: 5px; }
    .empty-timeline { text-align: center; padding: 40px; color: #6c757d; }
    .empty-timeline i { font-size: 3rem; margin-bottom: 15px; opacity: 0.5; }
</style>
