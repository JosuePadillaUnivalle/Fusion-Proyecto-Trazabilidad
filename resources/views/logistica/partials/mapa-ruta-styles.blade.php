<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
#mapaRutaEntrega {
    height: 360px;
    width: 100%;
    min-height: 360px;
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    background: #e8eef4;
    z-index: 1;
}
#mapaRutaEntrega.leaflet-container { font-family: inherit; }

/* Etiquetas de código de envío (divIcon): sin esto Leaflet usa 12×12px y el texto se alarga vertical */
.leaflet-div-icon.envio-mapa-marker {
    width: auto !important;
    height: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    background: transparent !important;
    border: none !important;
}
.envio-mapa-marker-label {
    display: inline-block;
    white-space: nowrap;
    line-height: 1.25;
    background: #17a2b8;
    color: #fff;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    border: 2px solid #fff;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.35);
    pointer-events: none;
}
.envio-mapa-marker-label.is-selected {
    background: #28a745;
}

.ruta-mapa-leyenda { font-size: .8rem; color: #64748b; }
.ruta-mapa-vacio {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #64748b;
    font-size: .9rem;
    text-align: center;
    padding: 1rem;
}
</style>
