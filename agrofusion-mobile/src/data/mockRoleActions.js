/**
 * Acciones mock mutables por rol — simulan funciones web sin API.
 * Solo activas cuando USE_MOCK_DATA = true.
 */

import {
  mockTareasPlanta,
  mockRecepcionesPlanta,
  mockEnviosTransportista,
  mockRutasTransportista,
  mockIncidentesTransportista,
  mockPedidosDistribucion,
  mockTrasladosPlantaMayorista,
  mockAlmacenesMayorista,
  mockMovimientosPlanta,
} from './mockWorkersData';
import {
  mockActividades,
  mockAlmacenesAgricultor,
  mockLotes,
} from './mockAgricultorData';

const ahora = () => new Date().toISOString();

// ─── AGRICULTOR ───────────────────────────────────────────────────────────────

export function completeMockActividad(actividadId, observaciones = null, evidenciaUrl = null) {
  const act = mockActividades.find(a => a.actividadid === Number(actividadId));
  if (!act || act.fechafin) return null;
  act.fechafin = ahora();
  if (observaciones) act.observaciones = observaciones;
  if (evidenciaUrl) {
    act.evidencia_foto_path = 'mock/evidencia-local.jpg';
    act.evidencia_foto_url = evidenciaUrl;
  }
  const tipo = (act.tipo_actividad?.nombre || '').toLowerCase();
  if (tipo.includes('siembra') && act.loteid) {
    const lote = mockLotes.find(l => l.loteid === act.loteid);
    if (lote) {
      lote.fechasiembra = ahora().split('T')[0];
      if (lote.estadolotetipoid === 2) {
        lote.estadolotetipoid = 3;
        lote.estadoTipo = { estadolotetipoid: 3, nombre: 'En crecimiento', slug: 'crecimiento' };
      }
    }
  }
  syncMockLoteActividades(act.loteid);
  return act;
}

export function syncMockLoteActividades(loteid) {
  if (!loteid) return;
  const lote = mockLotes.find(l => l.loteid === Number(loteid));
  if (lote) {
    lote.actividades = mockActividades.filter(a => a.loteid === lote.loteid);
  }
}

export function actividadRequiereComprobante(actividad) {
  if (!actividad) return false;
  if (actividad.requiere_evidencia) return true;
  const tipo = (actividad.tipo_actividad?.nombre || '').toLowerCase();
  return ['siembra', 'cosecha', 'fertilización', 'fertilizacion', 'control de plagas'].some(t => tipo.includes(t));
}

// ─── PLANTA ─────────────────────────────────────────────────────────────────

export function completeMockTareaPlanta(tareaId) {
  const t = mockTareasPlanta.find(x => x.id === Number(tareaId));
  if (!t || t.completada) return null;
  t.completada = true;
  t.fechacompletado = ahora();
  return t;
}

export function confirmMockRecepcionPlanta(recepcionId) {
  const r = mockRecepcionesPlanta.find(x => x.recepcionid === Number(recepcionId));
  if (!r || r.estado === 'confirmada') return null;
  r.estado = 'confirmada';
  return r;
}

export function updateMockPedidoPlantaEstado(pedidoId, estado) {
  const p = mockPedidosDistribucion.find(x => x.pedidodistribucionid === Number(pedidoId));
  if (!p) return null;
  p.estado = estado;
  return p;
}

// ─── TRANSPORTISTA ──────────────────────────────────────────────────────────

export function updateMockEnvioEstado(asignacionId, estadoenvio, estadoNombre) {
  const e = mockEnviosTransportista.find(x => x.asignacionid === Number(asignacionId));
  if (!e) return null;
  e.estadoenvio = estadoenvio;
  e.estado = { nombre: estadoNombre || estadoenvio };
  if (estadoenvio === 'entregado') {
    e.paradasCompletadas = e.paradas;
  }
  return e;
}

export function iniciarMockEnvio(asignacionId) {
  return updateMockEnvioEstado(asignacionId, 'en_ruta', 'En ruta');
}

export function entregarMockEnvio(asignacionId) {
  return updateMockEnvioEstado(asignacionId, 'entregado', 'Entregado');
}

export function verificarMockConexionVehiculo(rutaId) {
  const ruta = mockRutasTransportista.find(r => r.rutamultientregaid === Number(rutaId));
  if (!ruta) return null;
  ruta.conexion_revisada = true;
  ruta.ultima_senal = ahora();
  // Simula lectura del dispositivo GPS del vehículo asignado
  if (ruta.rutamultientregaid === 2) {
    ruta.conexion_vehiculo = 'debil';
    ruta.gps_activo = true;
  } else {
    ruta.conexion_vehiculo = 'online';
    ruta.gps_activo = true;
  }
  return ruta;
}

export function verificarMockCondicionesVehiculo(rutaId, estado = 'revisado') {
  const ruta = mockRutasTransportista.find(r => r.rutamultientregaid === Number(rutaId));
  if (!ruta) return null;
  ruta.condiciones_verificadas = true;
  ruta.condiciones_estado = estado;
  return ruta;
}

export function puedeIniciarMockRuta(ruta) {
  if (!ruta) return { ok: false, motivo: 'Ruta no encontrada' };
  if (ruta.estado !== 'pendiente') return { ok: false, motivo: 'La ruta ya fue iniciada' };
  if (!ruta.conexion_revisada) {
    return { ok: false, motivo: 'Debe verificar la conexión del vehículo antes de iniciar' };
  }
  if (!ruta.condiciones_verificadas) {
    return { ok: false, motivo: 'Debe registrar las condiciones del vehículo antes de iniciar' };
  }
  return { ok: true, conexion: ruta.conexion_vehiculo };
}

export function iniciarMockRuta(rutaId) {
  const ruta = mockRutasTransportista.find(r => r.rutamultientregaid === Number(rutaId));
  const check = puedeIniciarMockRuta(ruta);
  if (!check.ok) return { error: check.motivo };
  ruta.estado = 'en_ruta';
  if (ruta.envio_asignacionid) {
    iniciarMockEnvio(ruta.envio_asignacionid);
  }
  return ruta;
}

export function completeMockParada(rutaId, paradaId) {
  const ruta = mockRutasTransportista.find(r => r.rutamultientregaid === Number(rutaId));
  if (!ruta?.paradas) return null;
  if (ruta.estado !== 'en_ruta') return null;
  const parada = ruta.paradas.find(p => p.paradaid === Number(paradaId));
  if (!parada || parada.completada) return null;
  parada.completada = true;
  const envio = mockEnviosTransportista.find(
    e => e.rutamultientregaid === ruta.rutamultientregaid || e.asignacionid === ruta.envio_asignacionid
  );
  if (envio) {
    envio.paradasCompletadas = ruta.paradas.filter(p => p.completada).length;
  }
  const todas = ruta.paradas.every(p => p.completada);
  if (todas) {
    ruta.estado = 'completada';
    if (envio) entregarMockEnvio(envio.asignacionid);
  }
  return parada;
}

export function resolveMockIncidente(incidenteId) {
  const i = mockIncidentesTransportista.find(x => (x.incidenteid || x.id) === Number(incidenteId));
  if (!i || i.resuelto) return null;
  i.resuelto = true;
  i.estado = 'resuelto';
  return i;
}

export function addMockIncidente({ descripcion, envioAsignacionId = 101 }) {
  const envio = mockEnviosTransportista.find(e => e.asignacionid === envioAsignacionId);
  const nuevo = {
    incidenteid: mockIncidentesTransportista.length + 10,
    id: mockIncidentesTransportista.length + 10,
    tipo: 'Reporte',
    titulo: descripcion?.slice(0, 40) || 'Nuevo incidente',
    descripcion: descripcion || 'Incidente reportado desde app móvil',
    estado: 'abierto',
    resuelto: false,
    fechaincidente: ahora(),
    fecharegistro: ahora(),
    envio: { descripcion: envio?.descripcion || 'Envío', asignacionid: envioAsignacionId },
  };
  mockIncidentesTransportista.unshift(nuevo);
  return nuevo;
}

// ─── MINORISTA ────────────────────────────────────────────────────────────────

export function createMockPedidoDistribucionMinorista({ puntoVenta, detalles, usuarioid = 401 }) {
  const nextId = Math.max(...mockPedidosDistribucion.map(p => p.pedidodistribucionid), 600) + 1;
  const total = (detalles || []).reduce((s, d) => s + (d.cantidad * (d.precio_unitario || 0)), 0);
  const pedido = {
    pedidodistribucionid: nextId,
    usuarioid,
    numero_solicitud: `SOL-${nextId}`,
    punto_venta: puntoVenta,
    estado: 'pendiente',
    fechapedido: ahora(),
    fecharegistro: ahora(),
    total,
    detalles: detalles || [],
  };
  mockPedidosDistribucion.unshift(pedido);
  return pedido;
}

// ─── MAYORISTA ────────────────────────────────────────────────────────────────

export function aprobarMockPedidoDistribucion(pedidoId) {
  const p = mockPedidosDistribucion.find(x => x.pedidodistribucionid === Number(pedidoId));
  if (!p) return null;
  p.estado = 'en_transito';
  return p;
}

export function rechazarMockPedidoDistribucion(pedidoId) {
  const p = mockPedidosDistribucion.find(x => x.pedidodistribucionid === Number(pedidoId));
  if (!p) return null;
  p.estado = 'rechazado';
  return p;
}

export function firmarMockTrasladoPlanta(trasladoId) {
  const t = mockTrasladosPlantaMayorista.find(x => x.id === Number(trasladoId));
  if (!t || t.estado === 'recibido') return null;
  t.estado = 'recibido';
  return t;
}

export function getMockAlmacenById(id) {
  const all = [...mockAlmacenesAgricultor, ...mockAlmacenesMayorista];
  return all.find(a => a.almacenid === Number(id)) || null;
}

export function getMockAlmacenesForForm(role) {
  if (role === 'mayorista') return [...mockAlmacenesMayorista];
  if (role === 'agricultor') return [...mockAlmacenesAgricultor];
  return [...mockAlmacenesMayorista, ...mockAlmacenesAgricultor];
}

export function addMockMovimiento({ naturaleza, cantidad, almacenid, observaciones }) {
  const almacen = getMockAlmacenById(almacenid);
  const nextId = Math.max(...mockMovimientosPlanta.map(m => m.movimientoid || m.id || 0), 0) + 1;
  const mov = {
    movimientoid: nextId,
    id: nextId,
    naturaleza,
    cantidad: parseFloat(cantidad),
    observaciones: observaciones || null,
    almacen: almacen ? { nombre: almacen.nombre, almacenid: almacen.almacenid } : { nombre: 'Almacén', almacenid },
    tipo_movimiento: { nombre: naturaleza === 'ingreso' ? 'Ingreso manual' : 'Egreso manual' },
    fechamovimiento: ahora(),
    fecharegistro: ahora(),
  };
  mockMovimientosPlanta.unshift(mov);
  return mov;
}

export function getMockPedidoActions(pedido, role) {
  if (!pedido) return [];
  if (role === 'mayorista' && ['pendiente', 'en_revision'].includes(pedido.estado)) {
    return [
      { key: 'aprobar', label: 'Aprobar pedido' },
      { key: 'rechazar', label: 'Rechazar pedido' },
    ];
  }
  if (role === 'planta' && ['pendiente', 'en_revision'].includes(pedido.estado)) {
    return [{ key: 'preparar', label: 'Marcar en preparación' }];
  }
  return [];
}
