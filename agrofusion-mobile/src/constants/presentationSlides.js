/**
 * Contenido narrativo de la presentación AgroFusion (solo diseño móvil).
 */

export const PRESENTATION_ACTORS = [
  { key: 'agricultor', label: 'Agricultores', icon: 'leaf-outline' },
  { key: 'supervisor', label: 'Supervisores', icon: 'eye-outline' },
  { key: 'transportista', label: 'Transportistas', icon: 'car-sport-outline' },
  { key: 'planta', label: 'Plantas procesadoras', icon: 'business-outline' },
  { key: 'mayorista', label: 'Mayoristas', icon: 'cube-outline' },
  { key: 'minorista', label: 'Minoristas', icon: 'storefront-outline' },
];

export const PRESENTATION_TIMELINE = [
  { etapa: 'Planificación', icon: 'calendar-outline' },
  { etapa: 'Siembra', icon: 'leaf-outline' },
  { etapa: 'Crecimiento', icon: 'trending-up-outline' },
  { etapa: 'Cosecha', icon: 'basket-outline' },
  { etapa: 'Certificación', icon: 'shield-checkmark-outline' },
  { etapa: 'Distribución', icon: 'git-branch-outline' },
];

export const PRESENTATION_SLIDES = [
  {
    id: 'intro',
    type: 'hero',
    image: 'https://images.unsplash.com/photo-1500382017468-904029fed407?w=1200',
    title: '¿Qué pasaría si pudieras conocer la historia completa de un producto agrícola?',
    body: 'Desde la siembra hasta llegar al consumidor.',
  },
  {
    id: 'problema',
    type: 'text',
    image: 'https://images.unsplash.com/photo-1625246333195-78d9c038ad12?w=1200',
    title: 'El reto del campo',
    body: 'Hoy, miles de actividades agrícolas se registran de forma manual, dificultando el control, la supervisión y la confianza en los procesos.\n\nPero eso está cambiando.',
  },
  {
    id: 'plataforma',
    type: 'hero',
    image: 'https://images.unsplash.com/photo-1464226184884-fa280b87f399?w=1200',
    title: 'Presentamos AgroFusion',
    body: 'Una plataforma inteligente de trazabilidad agrícola que conecta a todos los actores de la cadena productiva.',
    highlight: true,
  },
  {
    id: 'actores',
    type: 'actors',
    image: 'https://images.unsplash.com/photo-1574943328600-fca21e97c6a9?w=1200',
    title: 'Un solo ecosistema digital',
    body: 'Todos los actores de la cadena, conectados en tiempo real.',
  },
  {
    id: 'web',
    type: 'web-mock',
    image: 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1200',
    title: 'Gestión desde la web',
    body: 'Desde la plataforma web, los administradores gestionan lotes, asignan responsables y monitorean cada etapa de producción en tiempo real.',
  },
  {
    id: 'lote',
    type: 'text',
    image: 'https://images.unsplash.com/photo-1592982537748-46c9a3d1f6a0?w=1200',
    title: 'Cada lote tiene una historia',
    body: 'Y cada historia comienza con un registro.\n\nCódigo de trazabilidad, responsable, cultivo y ubicación en un solo lugar.',
  },
  {
    id: 'movil',
    type: 'mobile-mock',
    image: 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1200',
    title: 'Actividades en el campo',
    body: 'Los agricultores reciben sus actividades directamente en la aplicación móvil.',
  },
  {
    id: 'evidencia',
    type: 'evidence',
    image: 'https://images.unsplash.com/photo-1464226184884-fa280b87f399?w=1200',
    title: 'La confianza se demuestra',
    body: 'Con un solo toque pueden registrar evidencias fotográficas de las tareas realizadas.\n\nPorque la confianza no se supone. Se demuestra.',
  },
  {
    id: 'timeline',
    type: 'timeline',
    image: 'https://images.unsplash.com/photo-1500382017468-904029fed407?w=1200',
    title: 'Trazabilidad completa',
    body: 'Cada evidencia queda asociada al lote, al responsable y al momento exacto en que ocurrió.\n\nTransparente. Verificable. Accesible.',
  },
  {
    id: 'cadena',
    type: 'chain',
    image: 'https://images.unsplash.com/photo-1625246333195-78d9c038ad12?w=1200',
    title: 'De la planificación a la mesa',
    body: 'Desde la planificación hasta la cosecha. Desde la certificación hasta la distribución.',
  },
  {
    id: 'transicion',
    type: 'text',
    image: 'https://images.unsplash.com/photo-1574943328600-fca21e97c6a9?w=1200',
    title: 'Datos que generan confianza',
    body: 'Conoce dónde estuvo el producto, quién participó en el proceso y qué actividades fueron realizadas.\n\nTransformando datos en confianza. Procesos en evidencia. Producción en información.',
  },
  {
    id: 'cierre',
    type: 'closing',
    image: 'https://images.unsplash.com/photo-1500382017468-904029fed407?w=1200',
    title: 'Más que una aplicación',
    body: 'Una herramienta que impulsa la digitalización agrícola y fortalece toda la cadena de valor.',
  },
  {
    id: 'logo',
    type: 'logo',
    image: null,
    title: 'AgroFusion',
    body: 'La nueva generación de trazabilidad agrícola.',
  },
];
