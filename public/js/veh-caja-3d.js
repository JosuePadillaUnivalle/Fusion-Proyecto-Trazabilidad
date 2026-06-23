/**
 * Modelado 3D de vehículo (mismo que envíos → detalle de vehículo).
 * Uso: VehCaja3D.mount(hostElement, { largo, ancho, alto, tipo, nombre, fillPct? })
 */
import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

const instances = new WeakMap();

function mat(color, opts = {}) {
    return new THREE.MeshStandardMaterial({ color, metalness: 0.25, roughness: 0.55, ...opts });
}

function matCarga(color) {
    return new THREE.MeshPhysicalMaterial({
        color, transparent: true, opacity: 0.48, metalness: 0.05, roughness: 0.4, side: THREE.DoubleSide,
    });
}

function matFill(color) {
    return new THREE.MeshStandardMaterial({
        color, transparent: true, opacity: 0.88, metalness: 0.1, roughness: 0.45,
    });
}

export function disposeVehCaja3d(host) {
    if (!host) return;
    const state = instances.get(host);
    if (state) {
        state.disposed = true;
        if (state.animationId) cancelAnimationFrame(state.animationId);
        if (state.resizeObserver) state.resizeObserver.disconnect();
        if (state.renderer) state.renderer.dispose();
        instances.delete(host);
    }
    host.innerHTML = '';
}

export function mountVehCaja3d(host, options) {
    if (!host) return;
    disposeVehCaja3d(host);
    host.innerHTML = '';
    const cargoL = Math.max(0.5, parseFloat(options.largo) || 2);
    const cargoW = Math.max(0.5, parseFloat(options.ancho) || 1.6);
    const cargoH = Math.max(0.5, parseFloat(options.alto) || 1.2);
    const tipo = String(options.tipo || 'CAMIONETA').toUpperCase();
    const nombre = options.nombre || tipo;
    const fillPct = Math.min(100, Math.max(0, parseFloat(options.fillPct) || 0));

    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0xe8eef4);
    scene.fog = new THREE.Fog(0xe8eef4, 30, 80);

    const vehicle = new THREE.Group();
    scene.add(vehicle);

    const paletas = {
        CAMIONETA: { cab: 0x3b6ea8, carga: 0x48bb78, chasis: 0x2d3748, rueda: 0x111827, fill: 0xf59e0b },
        CAMION_PQ: { cab: 0x4a5568, carga: 0x2f855a, chasis: 0x1a202c, rueda: 0x0f1419, fill: 0xfb923c },
        CAMION_GR: { cab: 0x334155, carga: 0x276749, chasis: 0x0f172a, rueda: 0x000000, fill: 0xea580c },
    };
    const pal = paletas[tipo] || paletas.CAMIONETA;

    function caja(w, h, d, material, x, y, z, parent = vehicle) {
        const mesh = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), material);
        mesh.position.set(x, y, z);
        mesh.castShadow = true;
        parent.add(mesh);
        return mesh;
    }

    function borde(mesh, color = 0x14532d) {
        const e = new THREE.LineSegments(
            new THREE.EdgesGeometry(mesh.geometry),
            new THREE.LineBasicMaterial({ color })
        );
        e.position.copy(mesh.position);
        vehicle.add(e);
    }

    function rueda(x, z, r) {
        const g = new THREE.CylinderGeometry(r, r, cargoW * 0.2, 20);
        const m = new THREE.Mesh(g, mat(pal.rueda));
        m.rotation.x = Math.PI / 2;
        m.position.set(x, r * 0.92, z);
        vehicle.add(m);
        const hub = new THREE.Mesh(
            new THREE.CylinderGeometry(r * 0.45, r * 0.45, cargoW * 0.22, 12),
            mat(0x9ca3af, { metalness: 0.8 })
        );
        hub.rotation.x = Math.PI / 2;
        hub.position.copy(m.position);
        vehicle.add(hub);
    }

    function addFill(cargoMesh, cargoHeight, isCamioneta) {
        if (fillPct <= 0 || !cargoMesh) return;
        const pct = fillPct / 100;
        const maxH = isCamioneta ? cargoHeight * 0.45 : cargoHeight;
        const fillH = Math.max(maxH * 0.06, maxH * pct * 0.92);
        const geo = cargoMesh.geometry;
        const fillW = geo.parameters.width * 0.86;
        const fillD = geo.parameters.depth * 0.86;
        const fillY = cargoMesh.position.y - geo.parameters.height / 2 + fillH / 2;
        caja(fillW, fillH, fillD, matFill(pal.fill), cargoMesh.position.x, fillY, cargoMesh.position.z);
    }

    const wheelR = Math.max(0.28, Math.min(0.55, cargoH * 0.2));
    let totalLen = cargoL;
    const yBase = wheelR * 0.95;
    let cargaMesh = null;

    if (tipo === 'CAMION_GR' || tipo === 'CAMION_PQ') {
        const cabinL = cargoL * (tipo === 'CAMION_GR' ? 0.22 : 0.26);
        totalLen = cabinL + cargoL;
        const cabinH = cargoH * 0.9;
        caja(cabinL, cabinH, cargoW * 0.98, mat(pal.cab), -totalLen / 2 + cabinL / 2, yBase + cabinH / 2, 0);
        caja(cabinL * 0.35, cabinH * 0.45, cargoW * 0.92, mat(0x93c5fd, { transparent: true, opacity: 0.5 }), -totalLen / 2 + cabinL * 0.72, yBase + cabinH * 0.72, 0);
        cargaMesh = caja(cargoL * 0.96, cargoH, cargoW * 0.96, matCarga(pal.carga), totalLen / 2 - cargoL / 2, yBase + cargoH / 2, 0);
        borde(cargaMesh);
        addFill(cargaMesh, cargoH, false);
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
        cargaMesh = caja(bedL * 0.92, bedH, cargoW * 0.92, matCarga(pal.carga), totalLen / 2 - bedL / 2, yBase + bedH / 2 + 0.08, 0);
        borde(cargaMesh, 0x22543d);
        addFill(cargaMesh, bedH, true);
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

    const h0 = Math.max(host.clientHeight || 0, 280);
    const w0 = host.clientWidth || 560;

    const camera = new THREE.PerspectiveCamera(40, w0 / h0, 0.1, 200);
    camera.position.set(maxDim * 1.35, maxDim * 0.75, maxDim * 1.45);

    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setSize(w0, h0);
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

    scene.add(new THREE.GridHelper(maxDim * 3, 16, 0x94a3b8, 0xcbd5e1));

    const canvas = document.createElement('canvas');
    canvas.width = 256;
    canvas.height = 64;
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

    const state = { disposed: false, renderer, animationId: null, resizeObserver: null };

    function animate() {
        if (state.disposed) return;
        state.animationId = requestAnimationFrame(animate);
        controls.update();
        renderer.render(scene, camera);
    }
    animate();

    state.resizeObserver = new ResizeObserver(() => {
        if (state.disposed) return;
        const w = host.clientWidth || w0;
        const h = Math.max(host.clientHeight || 0, 280);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        renderer.setSize(w, h);
    });
    state.resizeObserver.observe(host);
    instances.set(host, state);
}

window.VehCaja3D = { mount: mountVehCaja3d, dispose: disposeVehCaja3d };
