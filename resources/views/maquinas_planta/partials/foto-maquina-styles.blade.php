<style>
.modulo-prod .foto-maquina-img-thumb {
    width: 48px;
    height: 48px;
    object-fit: cover;
    cursor: zoom-in;
    border: 2px solid #e9ecef;
    transition: transform .15s ease, box-shadow .15s ease;
}
.modulo-prod .foto-maquina-thumb:hover .foto-maquina-img-thumb {
    transform: scale(1.06);
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
}
.modulo-prod .preview-imagen,
.modulo-prod .foto-maquina-img-lg,
.modulo-prod .detalle-hero-img {
    max-width: 100%;
    max-height: 200px;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 8px;
    border: 2px solid #dee2e6;
    background: #fff;
    display: block;
    margin: 0 auto;
}
.modulo-prod .detalle-hero-img {
    max-height: 120px;
    width: 120px;
    height: 120px;
    object-fit: cover;
}
.modulo-prod .zona-preview-foto {
    min-height: 160px;
    border: 2px dashed #ced4da;
    border-radius: 10px;
    background: #f8f9fa;
    padding: 16px;
    text-align: center;
}
.modulo-prod .zona-preview-foto.has-foto {
    border-style: solid;
    border-color: #2c5530;
    background: #fff;
}
.modulo-prod .zona-preview-foto .preview-placeholder {
    color: #6c757d;
    font-size: 0.875rem;
}
</style>
