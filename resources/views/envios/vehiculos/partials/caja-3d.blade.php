@php
    $dims = $capacidadResumen['dimensiones'] ?? [];
    $largo = (float) ($dims['largo_m'] ?? 2);
    $ancho = (float) ($dims['ancho_m'] ?? 1.6);
    $alto = (float) ($dims['alto_m'] ?? 1.2);
    $tipoCodigo = strtoupper($vehiculo->tipoVehiculo?->codigo ?? 'CAMIONETA');
    $tipoNombre = $vehiculo->tipoVehiculo?->nombre ?? 'Vehículo';
@endphp
<div class="card veh-det-panel h-100">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <span><i class="fas fa-truck mr-1 text-success"></i> Vehículo — proporciones de carga</span>
        <span class="badge badge-success">{{ $tipoNombre }}</span>
    </div>
    <div class="card-body">
        <div id="veh-caja-3d"
             class="veh-caja-3d"
             data-largo="{{ $largo }}"
             data-ancho="{{ $ancho }}"
             data-alto="{{ $alto }}"
             data-tipo="{{ $tipoCodigo }}"
             data-nombre="{{ $tipoNombre }}"></div>
        <div class="veh-caja-3d__leyenda small text-muted text-center mt-2 mb-1">
            <span class="veh-caja-3d__leyenda-item"><i class="veh-caja-3d__swatch veh-caja-3d__swatch--cabina"></i> Cabina</span>
            <span class="veh-caja-3d__leyenda-item ml-3"><i class="veh-caja-3d__swatch veh-caja-3d__swatch--carga"></i> Caja ({{ number_format($largo, 2) }} × {{ number_format($ancho, 2) }} × {{ number_format($alto, 2) }} m)</span>
        </div>
        <div class="veh-caja-3d__medidas mt-2">
            <div class="row text-center small">
                <div class="col-4">
                    <span class="text-muted d-block">Largo carga</span>
                    <strong>{{ number_format($largo, 2) }} m</strong>
                </div>
                <div class="col-4">
                    <span class="text-muted d-block">Ancho</span>
                    <strong>{{ number_format($ancho, 2) }} m</strong>
                </div>
                <div class="col-4">
                    <span class="text-muted d-block">Alto</span>
                    <strong>{{ number_format($alto, 2) }} m</strong>
                </div>
            </div>
            @if(($dims['factor_volumen_util'] ?? null) !== null)
            <p class="text-muted small mb-0 mt-2 text-center">
                Volumen bruto {{ number_format($dims['volumen_m3'] ?? 0, 1) }} m³ ·
                útil {{ number_format($dims['m3_util'] ?? 0, 1) }} m³
                ({{ number_format((float) ($dims['factor_volumen_util'] ?? 0.85) * 100, 0) }}%)
            </p>
            @endif
        </div>
        <p class="text-muted small mb-0 mt-2"><i class="fas fa-mouse mr-1"></i> Arrastre para rotar · rueda para zoom</p>
    </div>
</div>

@push('scripts')
<script type="importmap">
{
    "imports": {
        "three": "https://cdn.jsdelivr.net/npm/three@0.170.0/build/three.module.js",
        "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.170.0/examples/jsm/"
    }
}
</script>
<script type="module">
import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

(function () {
    const host = document.getElementById('veh-caja-3d');
    if (!host) return;

    const cargoL = Math.max(0.5, parseFloat(host.dataset.largo) || 2);
    const cargoW = Math.max(0.5, parseFloat(host.dataset.ancho) || 1.6);
    const cargoH = Math.max(0.5, parseFloat(host.dataset.alto) || 1.2);
    const tipo = (host.dataset.tipo || 'CAMIONETA').toUpperCase();
    const nombre = host.dataset.nombre || tipo;

    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0xe8eef4);
    scene.fog = new THREE.Fog(0xe8eef4, 30, 80);

    const vehicle = new THREE.Group();
    scene.add(vehicle);

    const paletas = {
        CAMIONETA: { cab: 0x3b6ea8, carga: 0x48bb78, chasis: 0x2d3748, rueda: 0x111827 },
        CAMION_PQ: { cab: 0x4a5568, carga: 0x2f855a, chasis: 0x1a202c, rueda: 0x0f1419 },
        CAMION_GR: { cab: 0x334155, carga: 0x276749, chasis: 0x0f172a, rueda: 0x000000 },
    };
    const pal = paletas[tipo] || paletas.CAMIONETA;

    function mat(color, opts = {}) {
        return new THREE.MeshStandardMaterial({ color, metalness: 0.25, roughness: 0.55, ...opts });
    }
    function matCarga(color) {
        return new THREE.MeshPhysicalMaterial({
            color, transparent: true, opacity: 0.48, metalness: 0.05, roughness: 0.4, side: THREE.DoubleSide,
        });
    }

    function caja(w, h, d, material, x, y, z, parent = vehicle) {
        const mesh = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), material);
        mesh.position.set(x, y, z);
        mesh.castShadow = true;
        parent.add(mesh);
        return mesh;
    }

    function borde(mesh, color = 0x14532d) {
        const e = new THREE.LineSegments(new THREE.EdgesGeometry(mesh.geometry), new THREE.LineBasicMaterial({ color }));
        e.position.copy(mesh.position);
        vehicle.add(e);
    }

    function rueda(x, z, r, parent = vehicle) {
        const g = new THREE.CylinderGeometry(r, r, cargoW * 0.2, 20);
        const m = new THREE.Mesh(g, mat(pal.rueda));
        m.rotation.x = Math.PI / 2;
        m.position.set(x, r * 0.92, z);
        parent.add(m);
        const hub = new THREE.Mesh(new THREE.CylinderGeometry(r * 0.45, r * 0.45, cargoW * 0.22, 12), mat(0x9ca3af, { metalness: 0.8 }));
        hub.rotation.x = Math.PI / 2;
        hub.position.copy(m.position);
        parent.add(hub);
    }

    const wheelR = Math.max(0.28, Math.min(0.55, cargoH * 0.2));
    let totalLen = cargoL;
    const yBase = wheelR * 0.95;

    if (tipo === 'CAMION_GR' || tipo === 'CAMION_PQ') {
        const cabinL = cargoL * (tipo === 'CAMION_GR' ? 0.22 : 0.26);
        totalLen = cabinL + cargoL;
        const cabinH = cargoH * 0.9;
        caja(cabinL, cabinH, cargoW * 0.98, mat(pal.cab), -totalLen / 2 + cabinL / 2, yBase + cabinH / 2, 0);
        caja(cabinL * 0.35, cabinH * 0.45, cargoW * 0.92, mat(0x93c5fd, { transparent: true, opacity: 0.5 }), -totalLen / 2 + cabinL * 0.72, yBase + cabinH * 0.72, 0);
        const carga = caja(cargoL * 0.96, cargoH, cargoW * 0.96, matCarga(pal.carga), totalLen / 2 - cargoL / 2, yBase + cargoH / 2, 0);
        borde(carga);
        caja(totalLen * 0.98, 0.14, cargoW * 0.9, mat(pal.chasis), 0, yBase, 0);
        const zW = cargoW * 0.38;
        const rR = wheelR * (tipo === 'CAMION_GR' ? 1.15 : 1.05);
        rueda(-totalLen / 2 + cabinL * 0.7, -zW, rR);
        rueda(-totalLen / 2 + cabinL * 0.7, zW, rR);
        rueda(totalLen / 2 - cargoL * 0.2, -zW, rR);
        rueda(totalLen / 2 - cargoL * 0.2, zW, rR);
        if (tipo === 'CAMION_GR') {
            rueda(totalLen / 2 - cargoL * 0.06, -zW, rR);
            rueda(totalLen / 2 - cargoL * 0.06, zW, rR);
            caja(cargoL * 0.15, cargoH * 0.12, cargoW * 1.02, mat(0x64748b), totalLen / 2 - cargoL * 0.5, yBase + cargoH + 0.06, 0);
        }
    } else {
        const cabinL = cargoL * 0.62;
        const bedL = cargoL * 0.88;
        const bedH = cargoH * 0.45;
        totalLen = cabinL + bedL;
        caja(cabinL, cargoH * 0.72, cargoW * 0.94, mat(pal.cab), -totalLen / 2 + cabinL / 2, yBase + cargoH * 0.36, 0);
        caja(cabinL * 0.28, cargoH * 0.38, cargoW * 0.86, mat(0x93c5fd, { transparent: true, opacity: 0.5 }), -totalLen / 2 + cabinL * 0.78, yBase + cargoH * 0.52, 0);
        const carga = caja(bedL * 0.92, bedH, cargoW * 0.92, matCarga(pal.carga), totalLen / 2 - bedL / 2, yBase + bedH / 2 + 0.08, 0);
        borde(carga, 0x22543d);
        caja(bedL * 0.94, bedH * 0.08, cargoW * 0.94, mat(0x8b7355), totalLen / 2 - bedL / 2, yBase + bedH + 0.04, 0);
        caja(totalLen * 0.96, 0.1, cargoW * 0.86, mat(pal.chasis), 0, yBase, 0);
        const zW = cargoW * 0.35;
        rueda(-totalLen / 2 + cabinL * 0.72, -zW, wheelR);
        rueda(-totalLen / 2 + cabinL * 0.72, zW, wheelR);
        rueda(totalLen / 2 - bedL * 0.22, -zW, wheelR);
        rueda(totalLen / 2 - bedL * 0.22, zW, wheelR);
    }

    const maxDim = Math.max(totalLen, cargoW, cargoH + wheelR * 2);
    const centerY = yBase + cargoH * 0.45;

    const camera = new THREE.PerspectiveCamera(40, host.clientWidth / Math.max(host.clientHeight, 260), 0.1, 200);
    camera.position.set(maxDim * 1.35, maxDim * 0.75, maxDim * 1.45);

    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setSize(host.clientWidth, Math.max(host.clientHeight, 280));
    renderer.shadowMap.enabled = true;
    host.appendChild(renderer.domElement);

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.target.set(0, centerY, 0);
    controls.maxPolarAngle = Math.PI / 2.05;

    scene.add(new THREE.AmbientLight(0xffffff, 0.55));
    const sun = new THREE.DirectionalLight(0xfff8f0, 1.0);
    sun.position.set(10, 14, 8);
    sun.castShadow = true;
    scene.add(sun);
    const fill = new THREE.DirectionalLight(0xb8cce8, 0.4);
    fill.position.set(-8, 6, -6);
    scene.add(fill);

    const grid = new THREE.GridHelper(maxDim * 3, 16, 0x94a3b8, 0xcbd5e1);
    scene.add(grid);

    const canvas = document.createElement('canvas');
    canvas.width = 256; canvas.height = 64;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = 'rgba(255,255,255,0.92)';
    ctx.fillRect(0, 0, 256, 64);
    ctx.fillStyle = '#1e293b';
    ctx.font = 'bold 22px system-ui,sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(nombre, 128, 40);
    const labelTex = new THREE.CanvasTexture(canvas);
    const label = new THREE.Mesh(
        new THREE.PlaneGeometry(maxDim * 0.55, maxDim * 0.14),
        new THREE.MeshBasicMaterial({ map: labelTex, transparent: true, depthWrite: false })
    );
    label.position.set(0, yBase + cargoH + maxDim * 0.12, 0);
    vehicle.add(label);

    function animate() {
        requestAnimationFrame(animate);
        controls.update();
        renderer.render(scene, camera);
    }
    animate();

    new ResizeObserver(() => {
        const w = host.clientWidth;
        const h = Math.max(host.clientHeight, 280);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        renderer.setSize(w, h);
    }).observe(host);
})();
</script>
@endpush
