import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'http://192.168.1.129:8080/api';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 15000,
});

apiClient.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      await AsyncStorage.multiRemove(['auth_token', 'user_data']);
    }
    return Promise.reject(error);
  }
);

export const authApi = {
  login: (email, password) => apiClient.post('/login', { email, password }),
  register: (data) => apiClient.post('/register', data),
  me: () => apiClient.get('/me'),
  logout: () => apiClient.post('/logout'),
};

export const lotesApi = {
  list: (params) => apiClient.get('/lotes', { params }),
  get: (id) => apiClient.get(`/lotes/${id}`),
  create: (data) => apiClient.post('/lotes', data),
  update: (id, data) => apiClient.put(`/lotes/${id}`, data),
  delete: (id) => apiClient.delete(`/lotes/${id}`),
};

export const actividadesApi = {
  list: () => apiClient.get('/actividades'),
  get: (id) => apiClient.get(`/actividades/${id}`),
  create: (data) => apiClient.post('/actividades', data),
  update: (id, data) => apiClient.put(`/actividades/${id}`, data),
  delete: (id) => apiClient.delete(`/actividades/${id}`),
};

export const produccionesApi = {
  list: () => apiClient.get('/producciones'),
  get: (id) => apiClient.get(`/producciones/${id}`),
  create: (data) => apiClient.post('/producciones', data),
  update: (id, data) => apiClient.put(`/producciones/${id}`, data),
  delete: (id) => apiClient.delete(`/producciones/${id}`),
};

export const insumosApi = {
  list: (params) => apiClient.get('/insumos', { params }),
  get: (id) => apiClient.get(`/insumos/${id}`),
  create: (data) => apiClient.post('/insumos', data),
  update: (id, data) => apiClient.put(`/insumos/${id}`, data),
  delete: (id) => apiClient.delete(`/insumos/${id}`),
};

export const certificacionesApi = {
  list: () => apiClient.get('/certificaciones'),
  create: (data) => apiClient.post('/certificaciones', data),
};

export const climasApi = {
  list: () => apiClient.get('/climas'),
  get: (id) => apiClient.get(`/climas/${id}`),
  create: (data) => apiClient.post('/climas', data),
  update: (id, data) => apiClient.put(`/climas/${id}`, data),
  delete: (id) => apiClient.delete(`/climas/${id}`),
};

export const almacenesApi = {
  list: () => apiClient.get('/almacenes'),
  get: (id) => apiClient.get(`/almacenes/${id}`),
  create: (data) => apiClient.post('/almacenes', data),
  update: (id, data) => apiClient.put(`/almacenes/${id}`, data),
  delete: (id) => apiClient.delete(`/almacenes/${id}`),
};

export const movimientosApi = {
  list: (naturaleza) => apiClient.get(`/almacen-movimientos/${naturaleza}`),
  create: (data) => apiClient.post('/almacen-movimientos', data),
};

export const procesosPlantaApi = {
  list: () => apiClient.get('/procesos-planta'),
  get: (id) => apiClient.get(`/procesos-planta/${id}`),
  create: (data) => apiClient.post('/procesos-planta', data),
  update: (id, data) => apiClient.put(`/procesos-planta/${id}`, data),
  delete: (id) => apiClient.delete(`/procesos-planta/${id}`),
};

export const maquinasApi = {
  list: () => apiClient.get('/maquinas-planta'),
  get: (id) => apiClient.get(`/maquinas-planta/${id}`),
  create: (data) => apiClient.post('/maquinas-planta', data),
  update: (id, data) => apiClient.put(`/maquinas-planta/${id}`, data),
  delete: (id) => apiClient.delete(`/maquinas-planta/${id}`),
};

export const plantillasApi = {
  list: () => apiClient.get('/plantillas-transformacion'),
  get: (id) => apiClient.get(`/plantillas-transformacion/${id}`),
  create: (data) => apiClient.post('/plantillas-transformacion', data),
  update: (id, data) => apiClient.put(`/plantillas-transformacion/${id}`, data),
  delete: (id) => apiClient.delete(`/plantillas-transformacion/${id}`),
};

export const enviosApi = {
  list: () => apiClient.get('/asignaciones-multiples'),
  create: (data) => apiClient.post('/asignaciones-multiples', data),
  createBatch: (data) => apiClient.post('/asignaciones-multiples/lote', data),
};

export const rutasApi = {
  list: () => apiClient.get('/rutas-multi'),
  get: (id) => apiClient.get(`/rutas-multi/${id}`),
  create: (data) => apiClient.post('/rutas-multi', data),
  update: (id, data) => apiClient.patch(`/rutas-multi/${id}`, data),
  reorder: (id, data) => apiClient.patch(`/rutas-multi/${id}/reordenar`, data),
};

export const incidentesApi = {
  list: () => apiClient.get('/incidentes'),
  create: (data) => apiClient.post('/incidentes', data),
  resolve: (id) => apiClient.patch(`/incidentes/${id}/resolver`),
};

export const documentosApi = {
  list: () => apiClient.get('/documentos-entrega'),
  create: (data) => apiClient.post('/documentos-entrega', data),
};

export const pedidosApi = {
  list: () => apiClient.get('/pedidos'),
  get: (id) => apiClient.get(`/pedidos/${id}`),
  create: (data) => apiClient.post('/pedidos', data),
  update: (id, data) => apiClient.put(`/pedidos/${id}`, data),
  delete: (id) => apiClient.delete(`/pedidos/${id}`),
};

export const usuariosApi = {
  list: (params) => apiClient.get('/usuarios', { params }),
  get: (id) => apiClient.get(`/usuarios/${id}`),
  create: (data) => apiClient.post('/usuarios', data),
  update: (id, data) => apiClient.put(`/usuarios/${id}`, data),
  delete: (id) => apiClient.delete(`/usuarios/${id}`),
};

export const rolesApi = {
  list: () => apiClient.get('/roles'),
  get: (id) => apiClient.get(`/roles/${id}`),
  create: (data) => apiClient.post('/roles', data),
  update: (id, data) => apiClient.put(`/roles/${id}`, data),
  delete: (id) => apiClient.delete(`/roles/${id}`),
};

export const catalogosApi = {
  tipoActividades: () => apiClient.get('/tipoactividades'),
  prioridades: () => apiClient.get('/prioridades'),
  tipoInsumos: () => apiClient.get('/tipoinsumos'),
  unidadesMedida: () => apiClient.get('/unidadesmedida'),
  cultivos: () => apiClient.get('/cultivos'),
  estadoLoteTipos: () => apiClient.get('/estadolote-tipos'),
  destinoProducciones: () => apiClient.get('/destinoproducciones'),
  estadoLoteInsumos: () => apiClient.get('/estadolote-insumos'),
  tipoAlmacenes: () => apiClient.get('/tipo-almacenes'),
  usuarios: (params) => apiClient.get('/usuarios', { params }),
  insumosSemilla: (params) => apiClient.get('/insumos', { params }),
};

export default apiClient;
