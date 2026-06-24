export const ROLES = {
  ADMIN: 'admin',
  JEFE_AGRICULTOR: 'jefe_agricultor',
  AGRICULTOR: 'agricultor',
  JEFE_PLANTA: 'jefe_planta',
  PLANTA: 'planta',
  TRANSPORTISTA: 'transportista',
  MINORISTA: 'minorista',
  MAYORISTA: 'mayorista',
};

export const ROLE_LABELS = {
  [ROLES.ADMIN]: 'Administrador',
  [ROLES.JEFE_AGRICULTOR]: 'Jefe Agrícola',
  [ROLES.AGRICULTOR]: 'Agricultor',
  [ROLES.JEFE_PLANTA]: 'Jefe de Planta',
  [ROLES.PLANTA]: 'Operador de Planta',
  [ROLES.TRANSPORTISTA]: 'Transportista',
  [ROLES.MINORISTA]: 'Minorista',
  [ROLES.MAYORISTA]: 'Mayorista',
};

export const hasRole = (user, role) => {
  if (!user || !user.roles) return false;
  return user.roles.some(r => r.name === role);
};

export const hasAnyRole = (user, roles) => {
  return roles.some(role => hasRole(user, role));
};

export const canAccessAgricultural = (user) =>
  hasAnyRole(user, [ROLES.ADMIN, ROLES.JEFE_AGRICULTOR, ROLES.AGRICULTOR]);

export const canAccessPlant = (user) =>
  hasAnyRole(user, [ROLES.ADMIN, ROLES.JEFE_PLANTA, ROLES.PLANTA]);

export const canAccessLogistics = (user) =>
  hasAnyRole(user, [ROLES.ADMIN, ROLES.TRANSPORTISTA]);

export const canAccessRetail = (user) =>
  hasAnyRole(user, [ROLES.ADMIN, ROLES.MINORISTA]);

export const canAccessMayorista = (user) =>
  hasAnyRole(user, [ROLES.ADMIN, ROLES.MAYORISTA]);

export const canAccessAdmin = (user) =>
  hasAnyRole(user, [ROLES.ADMIN]);

export const isManager = (user) =>
  hasAnyRole(user, [ROLES.ADMIN, ROLES.JEFE_AGRICULTOR, ROLES.JEFE_PLANTA]);

/** Agricultor de campo (app móvil) — distinto del jefe agrícola web. */
export const isAgricultor = (user) =>
  hasRole(user, ROLES.AGRICULTOR) && !hasAnyRole(user, [ROLES.ADMIN, ROLES.JEFE_AGRICULTOR]);

/** Operador de planta (rol `planta`), no jefe de planta. */
export const isOperadorPlanta = (user) =>
  hasRole(user, ROLES.PLANTA) && !hasAnyRole(user, [ROLES.ADMIN, ROLES.JEFE_PLANTA]);

/** Transportista de campo, no admin logística. */
export const isTransportista = (user) =>
  hasRole(user, ROLES.TRANSPORTISTA) && !hasRole(user, ROLES.ADMIN);

/** Minorista / punto de venta. */
export const isMinorista = (user) =>
  hasRole(user, ROLES.MINORISTA) && !hasAnyRole(user, [ROLES.ADMIN, ROLES.MAYORISTA]);

/** Mayorista / centro de distribución. */
export const isMayorista = (user) =>
  hasRole(user, ROLES.MAYORISTA) && !hasRole(user, ROLES.ADMIN);

/** Trabajador móvil con panel simplificado (no jefe ni admin). */
export const isMobileWorker = (user) =>
  isAgricultor(user) || isOperadorPlanta(user) || isTransportista(user)
  || isMinorista(user) || isMayorista(user);

export const canManageLotes = (user) =>
  hasAnyRole(user, [ROLES.ADMIN, ROLES.JEFE_AGRICULTOR]);

export const canCreateActividad = (user) =>
  hasAnyRole(user, [ROLES.ADMIN, ROLES.JEFE_AGRICULTOR, ROLES.AGRICULTOR]);
