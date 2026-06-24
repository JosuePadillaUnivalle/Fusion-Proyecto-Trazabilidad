export const MOCK_PLANTA_ID = 201;

export const MOCK_TRANSPORTISTA_ID = 301;

export const MOCK_MINORISTA_ID = 401;
export const MOCK_MAYORISTA_ID = 501;



const hoy = new Date().toISOString().split('T')[0];



// ─── PLANTA ───────────────────────────────────────────────────────────────────



export const mockTareasPlanta = [

  {

    id: 1,

    usuarioid: MOCK_PLANTA_ID,

    descripcion: 'Lavado y selección de tomate cherry',

    completada: false,

    fechaasignacion: `${hoy}T07:00:00`,

    etapa: { nombre: 'Lavado' },

    procesamiento: { nombre: 'Línea A — Tomate' },

    prioridad: { nombre: 'Alta' },

  },

  {

    id: 2,

    usuarioid: MOCK_PLANTA_ID,

    descripcion: 'Empaque en cajas de 5 kg',

    completada: false,

    fechaasignacion: `${hoy}T09:30:00`,

    etapa: { nombre: 'Empaque' },

    procesamiento: { nombre: 'Línea B — Empaque' },

    prioridad: { nombre: 'Normal' },

  },

  {

    id: 3,

    usuarioid: MOCK_PLANTA_ID,

    descripcion: 'Control de temperatura cámara fría',

    completada: true,

    fechaasignacion: `${hoy}T06:00:00`,

    fechacompletado: `${hoy}T06:45:00`,

    etapa: { nombre: 'Control calidad' },

    procesamiento: { nombre: 'Cámara fría #2' },

    prioridad: { nombre: 'Urgente' },

  },

];



export const mockRecepcionesPlanta = [

  {

    recepcionid: 1,

    codigo: 'REC-2026-0142',

    lote: { nombre: 'Lote Norte A', codigo_trazabilidad: 'TRZ-2026-001' },

    producto: 'Tomate cherry',

    cantidad: 850,

    unidad: 'kg',

    estado: 'pendiente',

    fecharecepcion: `${hoy}T07:30:00`,

    origen: 'Finca El Roble',

    transportista: 'Marco Vega',

  },

  {

    recepcionid: 2,

    codigo: 'REC-2026-0143',

    lote: { nombre: 'Lote Sur B', codigo_trazabilidad: 'TRZ-2026-002' },

    producto: 'Lechuga romana',

    cantidad: 420,

    unidad: 'kg',

    estado: 'confirmada',

    fecharecepcion: `${hoy}T06:00:00`,

    origen: 'Finca El Roble',

    transportista: 'Marco Vega',

  },

];



export const mockProcesamientos = [

  {

    id: 1,

    nombre: 'Línea A — Tomate cherry',

    estado: 'activo',

    fechainicio: `${hoy}T08:00:00`,

    lote: { nombre: 'Lote Norte A' },

    proceso_planta: { nombre: 'Lavado y selección' },

  },

  {

    id: 2,

    nombre: 'Línea B — Empaque',

    estado: 'activo',

    fechainicio: `${hoy}T09:00:00`,

    lote: { nombre: 'Lote Norte A' },

    proceso_planta: { nombre: 'Empaque final' },

  },

  {

    id: 3,

    nombre: 'Cámara fría #2',

    estado: 'completado',

    fechainicio: `${hoy}T05:00:00`,

    lote: { nombre: 'Lote Sur B' },

    proceso_planta: { nombre: 'Refrigeración' },

  },

];



export const mockMaquinas = [

  {

    maquinaplantaid: 1,

    nombre: 'Lavadora industrial LI-01',

    descripcion: 'Línea de lavado sector A',

    modelo: 'WashPro 500',

    activo: true,

    proceso_planta: { nombre: 'Lavado y selección' },

  },

  {

    maquinaplantaid: 2,

    nombre: 'Empacadora EP-03',

    descripcion: 'Empaque automático 5 kg',

    modelo: 'PackLine X2',

    activo: true,

    proceso_planta: { nombre: 'Empaque final' },

  },

  {

    maquinaplantaid: 3,

    nombre: 'Balanza digital BD-12',

    descripcion: 'Control de peso en línea',

    modelo: 'ScaleMax 100',

    activo: false,

    proceso_planta: { nombre: 'Control calidad' },

  },

];



export const mockMovimientosPlanta = [

  {

    movimientoid: 1,

    tipo_movimiento: { nombre: 'Ingreso producción' },

    tipo: 'ingreso',

    naturaleza: 'ingreso',

    almacen: { nombre: 'Cámara fría principal' },

    cantidad: 850,

    fechamovimiento: `${hoy}T07:45:00`,

  },

  {

    movimientoid: 2,

    tipo_movimiento: { nombre: 'Salida a distribución' },

    tipo: 'egreso',

    naturaleza: 'egreso',

    almacen: { nombre: 'Almacén despacho' },

    cantidad: 200,

    fechamovimiento: `${hoy}T10:00:00`,

  },

];



export function getMockTareasPlanta(userId = MOCK_PLANTA_ID) {

  return mockTareasPlanta.filter(t => t.usuarioid === userId);

}



export function getMockRecepcionesPlanta() {

  return mockRecepcionesPlanta;

}



export function getMockProcesamientos() {

  return mockProcesamientos;

}



export function getMockMaquinas() {

  return mockMaquinas;

}



export function getMockMovimientosPlanta(tipo = 'ingreso') {

  if (tipo === 'egreso') return mockMovimientosPlanta.filter(m => m.naturaleza === 'egreso');

  return mockMovimientosPlanta.filter(m => m.naturaleza === 'ingreso');

}



export function getMockMovimientosForRole(role, tipo = 'ingreso') {
  const items = getMockMovimientosPlanta(tipo);
  if (role === 'mayorista') {
    return items.map(m => ({
      ...m,
      almacen: { nombre: 'CD Mayorista Central', almacenid: 801 },
      tipo_movimiento: { nombre: m.naturaleza === 'ingreso' ? 'Recepción planta' : 'Despacho minorista' },
    }));
  }
  return items;
}



export function getMockPlantaDashboardStats(userId = MOCK_PLANTA_ID) {

  const mine = getMockTareasPlanta(userId);

  const recepciones = mockRecepcionesPlanta.filter(r => r.estado === 'pendiente');

  const distribucion = mockPedidosDistribucion.filter(p => p.estado === 'pendiente' || p.estado === 'en_preparacion');

  return {

    recepcionesPendientes: recepciones.length,

    pendientes: mine.filter(t => !t.completada).length,

    completadasHoy: mine.filter(t => t.completada).length,

    distribucionPendiente: distribucion.length,

    procesosActivos: mockProcesamientos.filter(p => p.estado === 'activo').length,

    alertas: mine.filter(t => !t.completada && t.prioridad?.nombre?.toLowerCase().includes('urgente')).length,

  };

}



// ─── TRANSPORTISTA ──────────────────────────────────────────────────────────



export const mockRutasTransportista = [

  {

    rutamultientregaid: 1,

    usuarioid: MOCK_TRANSPORTISTA_ID,

    envio_asignacionid: 101,

    nombre: 'Ruta Norte — Almacén central',

    descripcion: '3 paradas · Camión refrigerado ABC-1234',

    estado: 'en_ruta',

    fecharegistro: `${hoy}T08:00:00`,

    vehiculo: { vehiculoid: 1, placa: 'ABC-1234', nombre: 'Camión refrigerado' },

    conexion_vehiculo: 'online',

    gps_activo: true,

    ultima_senal: `${hoy}T08:05:00`,

    conexion_revisada: true,

    condiciones_verificadas: true,

    condiciones_estado: 'perfecto',

    paradas: [

      { paradaid: 1, nombre: 'Finca El Roble', direccion: 'Km 12 Carretera Norte', completada: true, orden: 1 },

      { paradaid: 2, nombre: 'Almacén central', direccion: 'Zona industrial', completada: false, orden: 2 },

      { paradaid: 3, nombre: 'Planta procesamiento', direccion: 'Av. Industrial 45', completada: false, orden: 3 },

    ],

  },

  {

    rutamultientregaid: 2,

    usuarioid: MOCK_TRANSPORTISTA_ID,

    envio_asignacionid: 102,

    nombre: 'Ruta Mercado Campesino',

    descripcion: '5 paradas · Furgón XYZ-5678',

    estado: 'pendiente',

    fecharegistro: `${hoy}T14:00:00`,

    vehiculo: { vehiculoid: 2, placa: 'XYZ-5678', nombre: 'Furgón' },

    conexion_vehiculo: 'offline',

    gps_activo: false,

    ultima_senal: `${hoy}T12:40:00`,

    conexion_revisada: false,

    condiciones_verificadas: false,

    condiciones_estado: null,

    paradas: [

      { paradaid: 4, nombre: 'Planta', direccion: 'Av. Industrial 45', completada: false, orden: 1 },

      { paradaid: 5, nombre: 'Mercado Campesino', direccion: 'Centro', completada: false, orden: 2 },

      { paradaid: 6, nombre: 'Punto Sur', direccion: 'Av. Busch', completada: false, orden: 3 },

      { paradaid: 7, nombre: 'Restaurante Verde', direccion: 'Zona Sur', completada: false, orden: 4 },

      { paradaid: 8, nombre: 'Mini mercado La Paz', direccion: 'El Alto', completada: false, orden: 5 },

    ],

  },

];



export const mockEnviosTransportista = [

  {

    asignacionid: 101,

    usuarioid: MOCK_TRANSPORTISTA_ID,

    descripcion: 'Entrega Lote Norte → Almacén central',

    estado: { nombre: 'En ruta' },

    estadoenvio: 'en_ruta',

    fechaenvio: `${hoy}T08:00:00`,

    fecharegistro: `${hoy}T08:00:00`,

    origen: 'Finca El Roble',

    destino: 'Almacén central',

    vehiculo: { placa: 'ABC-1234', nombre: 'Camión refrigerado' },

    transportista: { nombre: 'Marco', apellido: 'Vega' },

    paradas: 3,

    paradasCompletadas: 1,

    rutamultientregaid: 1,

    cargas: [

      { descripcion: 'Tomate cherry', cantidad: 850, unidad: 'kg' },

      { descripcion: 'Lechuga romana', cantidad: 120, unidad: 'kg' },

    ],

  },

  {

    asignacionid: 102,

    usuarioid: MOCK_TRANSPORTISTA_ID,

    descripcion: 'Ruta multi-entrega — Mercado Campesino',

    estado: { nombre: 'Pendiente' },

    estadoenvio: 'pendiente',

    fechaenvio: `${hoy}T14:00:00`,

    fecharegistro: `${hoy}T13:00:00`,

    origen: 'Planta procesamiento',

    destino: 'Varios puntos de venta',

    vehiculo: { placa: 'XYZ-5678', nombre: 'Furgón' },

    transportista: { nombre: 'Marco', apellido: 'Vega' },

    paradas: 5,

    paradasCompletadas: 0,

    rutamultientregaid: 2,

    cargas: [

      { descripcion: 'Mix verduras', cantidad: 350, unidad: 'kg' },

    ],

  },

  {

    asignacionid: 103,

    usuarioid: MOCK_TRANSPORTISTA_ID,

    descripcion: 'Despacho planta → Punto venta Sur',

    estado: { nombre: 'Entregado' },

    estadoenvio: 'entregado',

    fechaenvio: `${hoy}T05:30:00`,

    fecharegistro: `${hoy}T05:00:00`,

    origen: 'Planta procesamiento',

    destino: 'Punto Sur — Av. Busch',

    vehiculo: { placa: 'ABC-1234', nombre: 'Camión refrigerado' },

    transportista: { nombre: 'Marco', apellido: 'Vega' },

    paradas: 2,

    paradasCompletadas: 2,

    cargas: [

      { descripcion: 'Tomate procesado', cantidad: 200, unidad: 'kg' },

    ],

  },

];



export const mockIncidentesTransportista = [

  {

    incidenteid: 1,

    id: 1,

    tipo: 'Retraso',

    titulo: 'Retraso por lluvia',

    descripcion: 'Lluvia intensa en km 8 retrasa llegada ~45 min. Cliente notificado.',

    estado: 'abierto',

    resuelto: false,

    fechaincidente: `${hoy}T09:15:00`,

    fecharegistro: `${hoy}T09:15:00`,

    envio: { descripcion: 'Entrega Lote Norte → Almacén central', asignacionid: 101 },

  },

  {

    incidenteid: 2,

    id: 2,

    tipo: 'Daño de carga',

    titulo: 'Caja dañada en tránsito',

    descripcion: '2 cajas de tomate con golpe menor. Fotos adjuntas en sistema.',

    estado: 'resuelto',

    resuelto: true,

    fechaincidente: `${hoy}T06:30:00`,

    fecharegistro: `${hoy}T06:30:00`,

    envio: { descripcion: 'Despacho planta → Punto venta Sur', asignacionid: 103 },

  },

];



export function getMockEnviosTransportista(userId = MOCK_TRANSPORTISTA_ID) {

  return mockEnviosTransportista.filter(e => e.usuarioid === userId);

}



export function getMockEnvioById(id) {

  return mockEnviosTransportista.find(e => e.asignacionid === Number(id)) || null;

}



export function getMockRutasTransportista(userId = MOCK_TRANSPORTISTA_ID) {

  return mockRutasTransportista.filter(r => r.usuarioid === userId);

}



export function getMockRutaById(id) {

  return mockRutasTransportista.find(r => r.rutamultientregaid === Number(id)) || null;

}



export function getMockIncidentesTransportista() {

  return mockIncidentesTransportista;

}



export function getMockTransportistaDashboardStats(userId = MOCK_TRANSPORTISTA_ID) {

  const mine = getMockEnviosTransportista(userId);

  return {

    asignados: mine.filter(e => e.estadoenvio === 'pendiente').length,

    enRuta: mine.filter(e => e.estadoenvio === 'en_ruta').length,

    entregadosHoy: mine.filter(e => e.estadoenvio === 'entregado').length,

    incidentes: mockIncidentesTransportista.filter(i => !i.resuelto).length,

  };

}



// ─── MINORISTA (pedidos de distribución, no pedidos comerciales genéricos) ───



export const mockPedidosDistribucion = [

  {

    pedidodistribucionid: 601,

    usuarioid: MOCK_MINORISTA_ID,

    punto_venta: { nombre: 'Punto Sur — Av. Busch', direccion: 'Av. Busch esq. 3er Anillo' },

    estado: 'pendiente',

    fechapedido: `${hoy}T10:00:00`,

    fecharegistro: `${hoy}T10:00:00`,

    total: 1250.50,

    observaciones: 'Entrega preferente en la mañana',

    detalles: [

      { producto: { nombre: 'Tomate cherry 5kg' }, cantidad: 10, precio_unitario: 45 },

      { producto: { nombre: 'Lechuga romana' }, cantidad: 20, precio_unitario: 8.5 },

    ],

  },

  {

    pedidodistribucionid: 602,

    usuarioid: MOCK_MINORISTA_ID,

    punto_venta: { nombre: 'Punto Norte — El Alto', direccion: 'Zona Satélite, El Alto' },

    estado: 'en_revision',

    fechapedido: `${hoy}T11:30:00`,

    fecharegistro: `${hoy}T11:30:00`,

    total: 890.00,

    detalles: [

      { producto: { nombre: 'Mix verduras' }, cantidad: 15, precio_unitario: 35 },

    ],

  },

  {

    pedidodistribucionid: 603,

    usuarioid: MOCK_MINORISTA_ID,

    punto_venta: { nombre: 'Punto Sur — Av. Busch', direccion: 'Av. Busch esq. 3er Anillo' },

    estado: 'en_transito',

    fechapedido: `${hoy}T07:00:00`,

    fecharegistro: `${hoy}T07:00:00`,

    total: 2100.75,

    detalles: [

      { producto: { nombre: 'Tomate procesado' }, cantidad: 30, precio_unitario: 42 },

      { producto: { nombre: 'Lechuga empacada' }, cantidad: 25, precio_unitario: 12 },

    ],

  },

  {

    pedidodistribucionid: 604,

    usuarioid: MOCK_MINORISTA_ID,

    punto_venta: { nombre: 'Punto Norte — El Alto', direccion: 'Zona Satélite, El Alto' },

    estado: 'entregado',

    fechapedido: `${hoy}T06:00:00`,

    fecharegistro: `${hoy}T06:00:00`,

    total: 560.00,

    detalles: [

      { producto: { nombre: 'Tomate cherry 5kg' }, cantidad: 8, precio_unitario: 45 },

    ],

  },

];



export const mockPuntosVenta = [

  { puntoventaid: 1, nombre: 'Punto Sur — Av. Busch', direccion: 'Av. Busch esq. 3er Anillo', activo: true, telefono: '70012345', stock_bajo: 2 },

  { puntoventaid: 2, nombre: 'Punto Norte — El Alto', direccion: 'Zona Satélite, El Alto', activo: true, telefono: '70198765', stock_bajo: 0 },

];



export function getMockPuntosVenta() {

  return mockPuntosVenta;

}



export function getMockPedidosDistribucion(userId) {

  if (userId == null) return mockPedidosDistribucion;

  return mockPedidosDistribucion.filter(p => p.usuarioid === userId);

}



export function getMockPedidoDistribucion(id) {

  return mockPedidosDistribucion.find(p => p.pedidodistribucionid === Number(id)) || null;

}



/** @deprecated Usar getMockPedidosDistribucion — minorista no tiene pedidos.view en web */

export function getMockPedidosMinorista(userId = MOCK_MINORISTA_ID) {

  return getMockPedidosDistribucion(userId).map(p => ({

    pedidoid: p.pedidodistribucionid,

    usuarioid: p.usuarioid,

    cliente: { nombre: p.punto_venta?.nombre },

    estado: p.estado === 'en_revision' ? 'confirmado' : p.estado === 'en_transito' ? 'confirmado' : p.estado,

    fechapedido: p.fechapedido,

    total: p.total,

    items: p.detalles?.length || 0,

    _distribucion: true,

  }));

}



export function getMockMinoristaDashboardStats(userId = MOCK_MINORISTA_ID) {

  const mine = getMockPedidosDistribucion(userId);

  return {

    puntosVenta: mockPuntosVenta.length,

    pedidosActivos: mine.filter(p => ['pendiente', 'en_revision', 'en_transito', 'en_preparacion'].includes(p.estado)).length,

    pendientesPlanta: mine.filter(p => p.estado === 'en_revision' || p.estado === 'pendiente').length,

    enTransito: mine.filter(p => p.estado === 'en_transito').length,

    alertas: mockPuntosVenta.filter(p => (p.stock_bajo || 0) > 0).length,

    entregadosHoy: mine.filter(p => p.estado === 'entregado').length,

  };

}



// ─── MAYORISTA ────────────────────────────────────────────────────────────────



export const mockAlmacenesMayorista = [

  {

    almacenid: 801,

    nombre: 'CD Norte — Zona industrial',

    ubicacion: 'Parque industrial, km 8',

    activo: true,

    productos_stock: 45,

    stock_bajo: 2,

    ambito: 'mayorista',

    tipo_almacen: { nombre: 'Centro de distribución' },

  },

  {

    almacenid: 802,

    nombre: 'CD Sur — Av. Busch',

    ubicacion: 'Av. Busch esq. 3er Anillo',

    activo: true,

    productos_stock: 32,

    stock_bajo: 0,

    ambito: 'mayorista',

    tipo_almacen: { nombre: 'Centro de distribución' },

  },

];



export const mockTrasladosPlantaMayorista = [

  {

    id: 1,

    codigo: 'TRAS-2026-0088',

    origen: 'Planta procesamiento',

    producto: 'Tomate procesado',

    cantidad: 500,

    unidad: 'kg',

    estado: 'esperando_firma',

    fecha: `${hoy}T09:00:00`,

  },

  {

    id: 2,

    codigo: 'TRAS-2026-0089',

    origen: 'Planta procesamiento',

    producto: 'Mix verduras empacadas',

    cantidad: 280,

    unidad: 'kg',

    estado: 'en_camino',

    fecha: `${hoy}T07:30:00`,

  },

  {

    id: 3,

    codigo: 'TRAS-2026-0087',

    origen: 'Planta procesamiento',

    producto: 'Lechuga empacada',

    cantidad: 150,

    unidad: 'kg',

    estado: 'recibido',

    fecha: `${hoy}T06:00:00`,

  },

];



export const mockDocumentosLogistica = [

  {

    id: 1,

    documentoid: 1,

    titulo: 'Nota de entrega #NE-042',

    tipo: 'nota_entrega',

    referencia: 'Envío ABC-1234',

    fecha: `${hoy}T08:30:00`,

    usuarioid: MOCK_TRANSPORTISTA_ID,

  },

  {

    id: 2,

    documentoid: 2,

    titulo: 'Comprobante recepción TRAS-0087',

    tipo: 'comprobante',

    referencia: 'Traslado desde planta',

    fecha: `${hoy}T06:15:00`,

    usuarioid: MOCK_MAYORISTA_ID,

  },

  {

    id: 3,

    documentoid: 3,

    titulo: 'Guía de despacho #GD-115',

    tipo: 'guia',

    referencia: 'Pedido distribución #601',

    fecha: `${hoy}T10:00:00`,

    usuarioid: MOCK_MAYORISTA_ID,

  },

];



export function getMockAlmacenesMayorista() {

  return mockAlmacenesMayorista;

}



export function getMockTrasladosPlanta(filtro = 'todos') {

  if (filtro === 'esperando_firma') return mockTrasladosPlantaMayorista.filter(t => t.estado === 'esperando_firma');

  if (filtro === 'en_camino') return mockTrasladosPlantaMayorista.filter(t => t.estado === 'en_camino');

  return mockTrasladosPlantaMayorista;

}



export function getMockPedidosMayorista() {

  return mockPedidosDistribucion.map(p => ({

    ...p,

    numero_solicitud: `SOL-${p.pedidodistribucionid}`,

    minorista: p.punto_venta?.nombre,

  }));

}



export function getMockDocumentos(userId) {

  if (!userId) return mockDocumentosLogistica;

  return mockDocumentosLogistica.filter(d => d.usuarioid === userId);

}



export function getMockMayoristaDashboardStats() {

  const pedidos = getMockPedidosMayorista();

  const stockTotal = mockAlmacenesMayorista.reduce((sum, a) => sum + (a.productos_stock || 0), 0);

  return {

    almacenes: mockAlmacenesMayorista.length,

    pedidosPendientes: pedidos.filter(p => p.estado === 'pendiente' || p.estado === 'en_revision').length,

    pedidosActivos: pedidos.filter(p => ['pendiente', 'en_revision', 'en_transito', 'en_preparacion'].includes(p.estado)).length,

    recepcionesPendienteFirma: mockTrasladosPlantaMayorista.filter(t => t.estado === 'esperando_firma').length,

    recepcionesEnCamino: mockTrasladosPlantaMayorista.filter(t => t.estado === 'en_camino').length,

    enTransito: pedidos.filter(p => p.estado === 'en_transito').length,

    productosStock: stockTotal,

    stockBajo: mockAlmacenesMayorista.reduce((sum, a) => sum + (a.stock_bajo || 0), 0),

  };

}



export const DEMO_USERS = {

  agricultor: {

    usuarioid: 101,

    nombre: 'Carlos',

    apellido: 'Mendoza',

    email: 'agricultor.demo@agrofusion.local',

    role: 'agricultor',

  },

  planta: {

    usuarioid: MOCK_PLANTA_ID,

    nombre: 'Ana',

    apellido: 'Quispe',

    email: 'planta.demo@agrofusion.local',

    role: 'planta',

  },

  transportista: {

    usuarioid: MOCK_TRANSPORTISTA_ID,

    nombre: 'Marco',

    apellido: 'Vega',

    email: 'transportista.demo@agrofusion.local',

    role: 'transportista',

  },

  minorista: {

    usuarioid: MOCK_MINORISTA_ID,

    nombre: 'Lucía',

    apellido: 'Ramos',

    email: 'minorista.demo@agrofusion.local',

    role: 'minorista',

  },

  mayorista: {

    usuarioid: MOCK_MAYORISTA_ID,

    nombre: 'Roberto',

    apellido: 'Salazar',

    email: 'mayorista.demo@agrofusion.local',

    role: 'mayorista',

  },

};


