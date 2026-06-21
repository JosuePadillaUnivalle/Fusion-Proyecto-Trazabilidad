export const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('es-BO', { year: 'numeric', month: '2-digit', day: '2-digit' });
};

export const formatDateTime = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('es-BO', {
    year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit',
  });
};

export const getStatusColor = (status) => {
  const colors = {
    activo: '#388E3C',
    activo_plena_produccion: '#388E3C',
    en_preparacion: '#FF8F00',
    en_cosecha: '#1976D2',
    inactivo: '#9E9E9E',
    pendiente: '#FF8F00',
    en_transito: '#1976D2',
    entregado: '#388E3C',
    completado: '#388E3C',
    cancelado: '#D32F2F',
    rechazado: '#D32F2F',
    aprobado: '#388E3C',
    PENDIENTE: '#FF8F00',
    APROBADO: '#388E3C',
    RECHAZADO: '#D32F2F',
  };
  return colors[status] || '#757575';
};

export const getStatusLabel = (status) => {
  const labels = {
    activo: 'Activo',
    activo_plena_produccion: 'En Producción',
    en_preparacion: 'En Preparación',
    en_cosecha: 'En Cosecha',
    inactivo: 'Inactivo',
    pendiente: 'Pendiente',
    en_transito: 'En Tránsito',
    entregado: 'Entregado',
    completado: 'Completado',
    cancelado: 'Cancelado',
    rechazado: 'Rechazado',
    aprobado: 'Aprobado',
    PENDIENTE: 'Pendiente',
    APROBADO: 'Aprobado',
    RECHAZADO: 'Rechazado',
  };
  return labels[status] || status;
};

export const truncate = (str, len = 50) => {
  if (!str) return '';
  return str.length > len ? str.substring(0, len) + '...' : str;
};
