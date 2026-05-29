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
