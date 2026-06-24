/**

 * Capacidades móviles por rol — alineadas con config/permission_matrix.php

 * y dashboards web (resources/views/dashboard/inicio/*.blade.php).

 */



import { ROLES } from './roles';



export const WORKER_ROLE_CONFIG = {

  [ROLES.AGRICULTOR]: {

    label: 'Agricultor de campo',

    icon: 'leaf-outline',

    permissions: ['panel_agricultor.view', 'lotes.view', 'certificaciones.view', 'inventario.view', 'actividades'],

    stats: [

      { key: 'actividadesPendientes', icon: 'time-outline', label: 'Pendientes', screen: 'MisActividades', colorKey: 'warning' },

      { key: 'actividadesHoy', icon: 'checkmark-done-outline', label: 'Completadas hoy', screen: 'MisActividades', colorKey: 'success' },

      { key: 'lotesAsignados', icon: 'map-outline', label: 'Lotes asignados', screen: 'Lotes', colorKey: 'primary' },

      { key: 'alertas', icon: 'warning-outline', label: 'Alertas', screen: 'MisActividades', colorKey: 'error' },

    ],

    menu: [

      { title: 'Mis Actividades', subtitle: 'Ver y completar tareas', icon: 'calendar-outline', screen: 'MisActividades' },

      { title: 'Mis Lotes', subtitle: 'Parcelas y mapa', icon: 'map-outline', screen: 'Lotes' },

      { title: 'Mis Cosechas', subtitle: 'Producción registrada', icon: 'basket-outline', screen: 'Producciones' },

      { title: 'Certificaciones', subtitle: 'Calidad de mis lotes', icon: 'shield-checkmark-outline', screen: 'Certificaciones' },

      { title: 'Insumos', subtitle: 'Consulta de catálogo', icon: 'flask-outline', screen: 'Insumos' },

      { title: 'Almacenes', subtitle: 'Destinos de cosecha', icon: 'business-outline', screen: 'Almacenes' },

      { title: 'Clima', subtitle: 'Condiciones en mis lotes', icon: 'cloud-outline', screen: 'Clima' },

      { title: 'Evidencias', subtitle: 'Fotos enviadas', icon: 'images-outline', screen: 'Evidencias' },

      { title: 'Mi Perfil', subtitle: 'Cuenta', icon: 'person-circle-outline', screen: 'Profile', color: '#475569' },

    ],

  },



  [ROLES.PLANTA]: {

    label: 'Operador de planta',

    icon: 'cog-outline',

    permissions: [

      'panel_planta.view', 'recepcion_planta.view', 'recepcion_planta.confirm',

      'lote_produccion.view', 'inventario.view', 'almacen.movimientos.view',

      'pedidos_distribucion.view', 'pedidos_distribucion.update',

    ],

    stats: [

      { key: 'recepcionesPendientes', icon: 'download-outline', label: 'Recepciones', screen: 'RecepcionPlanta', colorKey: 'warning' },

      { key: 'pendientes', icon: 'time-outline', label: 'Tareas pendientes', screen: 'TareasPlanta', colorKey: 'info' },

      { key: 'completadasHoy', icon: 'checkmark-done-outline', label: 'Completadas hoy', screen: 'TareasPlanta', colorKey: 'success' },

      { key: 'distribucionPendiente', icon: 'send-outline', label: 'Distribución', screen: 'PedidosDistribucion', colorKey: 'primary' },

    ],

    menu: [

      { title: 'Mis Tareas', subtitle: 'Tareas de planta', icon: 'list-circle-outline', screen: 'TareasPlanta' },

      { title: 'Recepción', subtitle: 'Confirmar ingresos', icon: 'download-outline', screen: 'RecepcionPlanta' },

      { title: 'Procesamiento', subtitle: 'Líneas activas', icon: 'business-outline', screen: 'Procesamiento' },

      { title: 'Máquinas', subtitle: 'Equipos asignados', icon: 'hardware-chip-outline', screen: 'Maquinas' },

      { title: 'Movimientos', subtitle: 'Ingresos y salidas', icon: 'swap-horizontal-outline', screen: 'Movimientos' },

      { title: 'Distribución', subtitle: 'Pedidos a puntos de venta', icon: 'send-outline', screen: 'PedidosDistribucion' },

      { title: 'Mi Perfil', subtitle: 'Cuenta', icon: 'person-circle-outline', screen: 'Profile', color: '#475569' },

    ],

  },



  [ROLES.TRANSPORTISTA]: {

    label: 'Transportista',

    icon: 'car-sport-outline',

    permissions: [

      'panel_transportista.view', 'envios.view', 'envios.update', 'asignaciones.view',

      'rutas_multi.view', 'documentos.view', 'incidentes.view', 'incidentes.create',

      'pedidos.view', 'pedidos_distribucion.view',

    ],

    stats: [

      { key: 'asignados', icon: 'cube-outline', label: 'Por iniciar', screen: 'Envios', colorKey: 'warning' },

      { key: 'enRuta', icon: 'navigate-outline', label: 'En ruta', screen: 'Envios', colorKey: 'info' },

      { key: 'entregadosHoy', icon: 'checkmark-done-outline', label: 'Entregados hoy', screen: 'Envios', colorKey: 'success' },

      { key: 'incidentes', icon: 'warning-outline', label: 'Incidentes', screen: 'Incidentes', colorKey: 'error' },

    ],

    menu: [

      { title: 'Mis Envíos', subtitle: 'Despachos asignados', icon: 'cube-outline', screen: 'Envios' },

      { title: 'Mis Rutas', subtitle: 'Paradas del día', icon: 'git-branch-outline', screen: 'Rutas' },

      { title: 'Documentos', subtitle: 'Notas y comprobantes', icon: 'document-text-outline', screen: 'Documentos' },

      { title: 'Incidentes', subtitle: 'Reportar problemas', icon: 'warning-outline', screen: 'Incidentes', colorKey: 'error' },

      { title: 'Mi Perfil', subtitle: 'Cuenta', icon: 'person-circle-outline', screen: 'Profile', color: '#475569' },

    ],

  },



  [ROLES.MINORISTA]: {

    label: 'Minorista',

    icon: 'storefront-outline',

    permissions: [

      'punto_venta.view', 'punto_venta.create', 'punto_venta.update',

      'pedidos_distribucion.view', 'pedidos_distribucion.create', 'pedidos_distribucion.update',

    ],

    stats: [

      { key: 'puntosVenta', icon: 'storefront-outline', label: 'Puntos de venta', screen: 'PuntosVenta', colorKey: 'primary' },

      { key: 'pedidosActivos', icon: 'clipboard-outline', label: 'Pedidos activos', screen: 'PedidosDistribucion', colorKey: 'info' },

      { key: 'pendientesPlanta', icon: 'hourglass-outline', label: 'En revisión', screen: 'PedidosDistribucion', colorKey: 'warning' },

      { key: 'enTransito', icon: 'airplane-outline', label: 'En tránsito', screen: 'PedidosDistribucion', colorKey: 'success' },

    ],

    menu: [

      { title: 'Mis Pedidos', subtitle: 'Pedidos de distribución', icon: 'cart-outline', screen: 'PedidosDistribucion' },

      { title: 'Puntos de Venta', subtitle: 'Mis locales', icon: 'storefront-outline', screen: 'PuntosVenta' },

      { title: 'Mi Perfil', subtitle: 'Cuenta', icon: 'person-circle-outline', screen: 'Profile', color: '#475569' },

    ],

  },



  [ROLES.MAYORISTA]: {

    label: 'Mayorista',

    icon: 'warehouse-outline',

    permissions: [

      'panel_mayorista.view', 'inventario.view', 'almacen.movimientos.view',

      'pedidos_distribucion.view', 'envios.view', 'documentos.view', 'asignaciones.view',

    ],

    stats: [

      { key: 'almacenes', icon: 'business-outline', label: 'Almacenes', screen: 'Almacenes', colorKey: 'primary' },

      { key: 'pedidosPendientes', icon: 'mail-outline', label: 'Por revisar', screen: 'PedidosDistribucion', colorKey: 'warning' },

      { key: 'pedidosActivos', icon: 'clipboard-outline', label: 'Pedidos activos', screen: 'PedidosDistribucion', colorKey: 'info' },

      { key: 'productosStock', icon: 'cube-outline', label: 'En stock', screen: 'Almacenes', colorKey: 'success' },

    ],

    menu: [

      { title: 'Mis Almacenes', subtitle: 'Inventario y stock', icon: 'business-outline', screen: 'Almacenes' },

      { title: 'Pedidos Minoristas', subtitle: 'Aceptar y coordinar', icon: 'mail-outline', screen: 'PedidosDistribucion' },

      { title: 'Traslados Planta', subtitle: 'Recepciones desde planta', icon: 'truck-outline', screen: 'TrasladosPlanta' },

      { title: 'Movimientos', subtitle: 'Ingresos y salidas', icon: 'swap-horizontal-outline', screen: 'Movimientos' },

      { title: 'Envíos', subtitle: 'Seguimiento logístico', icon: 'cube-outline', screen: 'Envios' },

      { title: 'Documentos', subtitle: 'Notas y comprobantes', icon: 'document-text-outline', screen: 'Documentos' },

      { title: 'Mi Perfil', subtitle: 'Cuenta', icon: 'person-circle-outline', screen: 'Profile', color: '#475569' },

    ],

  },

};



export function getWorkerRoleKey(user) {

  if (!user?.roles?.length) return null;

  const names = user.roles.map(r => r.name);

  if (names.includes(ROLES.AGRICULTOR) && !names.includes(ROLES.ADMIN) && !names.includes(ROLES.JEFE_AGRICULTOR)) {

    return ROLES.AGRICULTOR;

  }

  if (names.includes(ROLES.PLANTA) && !names.includes(ROLES.ADMIN) && !names.includes(ROLES.JEFE_PLANTA)) {

    return ROLES.PLANTA;

  }

  if (names.includes(ROLES.TRANSPORTISTA) && !names.includes(ROLES.ADMIN)) {

    return ROLES.TRANSPORTISTA;

  }

  if (names.includes(ROLES.MINORISTA) && !names.includes(ROLES.ADMIN) && !names.includes(ROLES.MAYORISTA)) {

    return ROLES.MINORISTA;

  }

  if (names.includes(ROLES.MAYORISTA) && !names.includes(ROLES.ADMIN)) {

    return ROLES.MAYORISTA;

  }

  return null;

}


