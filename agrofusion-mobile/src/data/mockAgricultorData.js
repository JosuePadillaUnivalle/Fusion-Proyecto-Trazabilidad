/** Datos de ejemplo para diseño del rol agricultor (sin backend). */

export const MOCK_AGRICULTOR_ID = 101;

const hoy = new Date().toISOString().split('T')[0];
const ayer = new Date(Date.now() - 86400000).toISOString().split('T')[0];

export const mockLotes = [
  {
    loteid: 1,
    nombre: 'Lote Norte A',
    ubicacion: 'Finca El Roble, sector norte',
    superficie: 2.5,
    usuarioid: MOCK_AGRICULTOR_ID,
    fechasiembra: '2026-03-15',
    codigo_trazabilidad: 'TRZ-2026-001',
    latitud: '-17.7833',
    longitud: '-63.1821',
    estadolotetipoid: 3,
    estadoTipo: { estadolotetipoid: 3, nombre: 'En crecimiento', slug: 'crecimiento' },
    cultivo_etiqueta: 'Tomate cherry',
    cultivo: { nombre: 'Tomate cherry' },
    usuario: { nombre: 'Carlos', apellido: 'Mendoza' },
    actividades: [],
    producciones: [],
  },
  {
    loteid: 2,
    nombre: 'Lote Sur B',
    ubicacion: 'Finca El Roble, sector sur',
    superficie: 1.8,
    usuarioid: MOCK_AGRICULTOR_ID,
    fechasiembra: '2026-04-01',
    codigo_trazabilidad: 'TRZ-2026-002',
    latitud: '-17.7850',
    longitud: '-63.1840',
    estadolotetipoid: 2,
    estadoTipo: { estadolotetipoid: 2, nombre: 'Siembra', slug: 'siembra' },
    cultivo_etiqueta: 'Lechuga romana',
    cultivo: { nombre: 'Lechuga romana' },
    usuario: { nombre: 'Carlos', apellido: 'Mendoza' },
    actividades: [],
    producciones: [],
  },
];

export const mockActividades = [
  {
    actividadid: 1,
    usuarioid: MOCK_AGRICULTOR_ID,
    loteid: 1,
    descripcion: 'Riego programado sector norte',
    fechainicio: `${hoy}T08:00:00`,
    fechafin: null,
    observaciones: 'Verificar humedad del suelo antes de regar',
    evidencia_foto_path: null,
    tipo_actividad: { nombre: 'Riego' },
    prioridad: { nombre: 'Alta' },
    lote: mockLotes[0],
    usuario: { nombre: 'Carlos', apellido: 'Mendoza' },
  },
  {
    actividadid: 2,
    usuarioid: MOCK_AGRICULTOR_ID,
    loteid: 1,
    descripcion: 'Control de plagas en hojas',
    fechainicio: `${hoy}T10:00:00`,
    fechafin: null,
    observaciones: null,
    evidencia_foto_path: null,
    tipo_actividad: { nombre: 'Control de plagas' },
    prioridad: { nombre: 'Urgente' },
    lote: mockLotes[0],
    usuario: { nombre: 'Carlos', apellido: 'Mendoza' },
  },
  {
    actividadid: 3,
    usuarioid: MOCK_AGRICULTOR_ID,
    loteid: 2,
    descripcion: 'Fertilización foliar',
    fechainicio: `${ayer}T07:30:00`,
    fechafin: `${hoy}T09:15:00`,
    observaciones: 'Aplicación completada sin incidencias. Buena cobertura.',
    evidencia_foto_path: 'mock/evidencia-3.jpg',
    evidencia_foto_url: 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800',
    tipo_actividad: { nombre: 'Fertilización' },
    prioridad: { nombre: 'Media' },
    lote: mockLotes[1],
    usuario: { nombre: 'Carlos', apellido: 'Mendoza' },
  },
  {
    actividadid: 4,
    usuarioid: MOCK_AGRICULTOR_ID,
    loteid: 1,
    descripcion: 'Monitoreo de brotes',
    fechainicio: `${hoy}T06:00:00`,
    fechafin: `${hoy}T07:00:00`,
    observaciones: 'Brotes en buen estado, sin signos de enfermedad.',
    evidencia_foto_path: 'mock/evidencia-4.jpg',
    evidencia_foto_url: 'https://images.unsplash.com/photo-1464226184884-fa280b87f399?w=800',
    tipo_actividad: { nombre: 'Monitoreo' },
    prioridad: { nombre: 'Normal' },
    lote: mockLotes[0],
    usuario: { nombre: 'Carlos', apellido: 'Mendoza' },
  },
  {
    actividadid: 5,
    usuarioid: MOCK_AGRICULTOR_ID,
    loteid: 2,
    descripcion: 'Siembra de lechuga romana — surcos 1 al 8',
    fechainicio: `${hoy}T07:00:00`,
    fechafin: null,
    observaciones: 'Usar semilla certificada. Distancia entre surcos 30 cm.',
    evidencia_foto_path: null,
    requiere_evidencia: true,
    tipo_comprobante: 'foto_siembra',
    tipo_actividad: { nombre: 'Siembra' },
    prioridad: { nombre: 'Alta' },
    lote: mockLotes[1],
    usuario: { nombre: 'Carlos', apellido: 'Mendoza' },
  },
];

mockLotes[0].actividades = mockActividades.filter(a => a.loteid === 1);
mockLotes[1].actividades = mockActividades.filter(a => a.loteid === 2);

export function getMockActividades(userId = MOCK_AGRICULTOR_ID) {
  return mockActividades.filter(a => a.usuarioid === userId);
}

export function getMockActividad(id) {
  return mockActividades.find(a => a.actividadid === Number(id)) || null;
}

export function getMockLotes(userId = MOCK_AGRICULTOR_ID) {
  return mockLotes
    .filter(l => l.usuarioid === userId)
    .map(l => ({
      ...l,
      actividades: mockActividades.filter(a => a.loteid === l.loteid),
    }));
}

export function getMockLote(id) {
  const lote = mockLotes.find(l => l.loteid === Number(id));
  if (!lote) return null;
  return {
    ...lote,
    actividades: mockActividades.filter(a => a.loteid === l.loteid),
  };
}

export function getMockEvidenciaUrl(actividad) {
  if (actividad?.evidencia_foto_url) return actividad.evidencia_foto_url;
  return null;
}

export const mockCertificaciones = [
  {
    certificacionid: 1,
    nombre: 'GlobalGAP',
    tipo_certificacion: 'GlobalGAP',
    lote: { nombre: 'Lote Norte A' },
    aprobado: true,
    fechacertificacion: '2026-05-10',
    entidad_certificadora: 'CertAgro Bolivia',
  },
  {
    certificacionid: 2,
    nombre: 'Orgánico en trámite',
    tipo_certificacion: 'Orgánico',
    lote: { nombre: 'Lote Sur B' },
    aprobado: false,
    fechacertificacion: `${hoy}T00:00:00`,
    entidad_certificadora: 'Senasag',
  },
];

export const mockInsumosCatalogo = [
  { insumoid: 1, nombre: 'Fertilizante NPK 15-15-15', tipo_insumo: { nombre: 'Fertilizante' }, unidad: 'kg', stock: 120, activo: true },
  { insumoid: 2, nombre: 'Semilla tomate cherry F1', tipo_insumo: { nombre: 'Semilla' }, unidad: 'sobre', stock: 45, activo: true },
  { insumoid: 3, nombre: 'Insecticida biológico', tipo_insumo: { nombre: 'Fitosanitario' }, unidad: 'L', stock: 18, activo: true },
];

export const mockProducciones = [
  {
    produccionid: 1,
    loteid: 1,
    cantidad: 850,
    fechacosecha: `${hoy}T06:00:00`,
    lote: mockLotes[0],
    unidad: { nombre: 'kg', abreviatura: 'kg' },
    destino: 'Almacén central',
  },
  {
    produccionid: 2,
    loteid: 2,
    cantidad: 420,
    fechacosecha: '2026-06-15',
    lote: mockLotes[1],
    unidad: { nombre: 'kg', abreviatura: 'kg' },
    destino: 'Planta procesamiento',
  },
];

export function getMockCertificaciones(userId = MOCK_AGRICULTOR_ID) {
  const lotesIds = getMockLotes(userId).map(l => l.loteid);
  return mockCertificaciones.filter(c => lotesIds.includes(
    mockLotes.find(l => l.nombre === c.lote?.nombre)?.loteid
  ));
}

export function getMockInsumosCatalogo() {
  return mockInsumosCatalogo;
}

export function getMockProducciones(userId = MOCK_AGRICULTOR_ID) {
  const lotesIds = getMockLotes(userId).map(l => l.loteid);
  return mockProducciones.filter(p => lotesIds.includes(p.loteid));
}

export const mockAlmacenesAgricultor = [
  {
    almacenid: 901,
    nombre: 'Almacén agrícola central',
    ubicacion: 'Finca El Roble — bodega principal',
    activo: true,
    ambito: 'agricola',
    tipo_almacen: { nombre: 'Destino cosecha' },
  },
  {
    almacenid: 902,
    nombre: 'Cámara prefrío sector sur',
    ubicacion: 'Finca El Roble — sector sur',
    activo: true,
    ambito: 'agricola',
    tipo_almacen: { nombre: 'Refrigeración' },
  },
];

export function getMockAlmacenesAgricultor() {
  return mockAlmacenesAgricultor;
}

export function getMockDashboardStats(userId = MOCK_AGRICULTOR_ID) {
  const mine = getMockActividades(userId);
  const lotes = getMockLotes(userId);
  return {
    actividadesPendientes: mine.filter(a => !a.fechafin).length,
    actividadesHoy: mine.filter(a => a.fechafin && a.fechafin.startsWith(hoy)).length,
    lotesAsignados: lotes.length,
    alertas: mine.filter(a => !a.fechafin && a.prioridad?.nombre?.toLowerCase().includes('urgente')).length,
  };
}

export const mockClima = [
  {
    climaid: 1,
    loteid: 1,
    lote: { nombre: 'Lote Norte A' },
    temperatura: 24,
    humedad: 68,
    precipitacion: 0,
    velocidad_viento: 12,
    fecharegistro: `${hoy}T06:00:00`,
  },
  {
    climaid: 2,
    loteid: 2,
    lote: { nombre: 'Lote Sur B' },
    temperatura: 22,
    humedad: 72,
    precipitacion: 2.5,
    velocidad_viento: 8,
    fecharegistro: `${hoy}T08:00:00`,
  },
];

export function getMockClima(userId = MOCK_AGRICULTOR_ID) {
  const lotesIds = getMockLotes(userId).map(l => l.loteid);
  return mockClima.filter(c => lotesIds.includes(c.loteid));
}
