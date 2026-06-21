export const ROLES = {
  ADMIN: 'admin',
  JEFE_AGRICULTOR: 'jefe_agricultor',
  AGRICULTOR: 'agricultor',
  JEFE_PLANTA: 'jefe_planta',
  PLANTA: 'planta',
  TRANSPORTISTA: 'transportista',
  MINORISTA: 'minorista',
};

export const ROLE_LABELS = {
  [ROLES.ADMIN]: 'Administrador',
  [ROLES.JEFE_AGRICULTOR]: 'Jefe Agrícola',
  [ROLES.AGRICULTOR]: 'Agricultor',
  [ROLES.JEFE_PLANTA]: 'Jefe de Planta',
  [ROLES.PLANTA]: 'Operador de Planta',
  [ROLES.TRANSPORTISTA]: 'Transportista',
  [ROLES.MINORISTA]: 'Minorista',
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

export const canAccessAdmin = (user) =>
  hasAnyRole(user, [ROLES.ADMIN]);

export const isManager = (user) =>
  hasAnyRole(user, [ROLES.ADMIN, ROLES.JEFE_AGRICULTOR, ROLES.JEFE_PLANTA]);
