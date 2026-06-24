import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { Colors } from '../constants/colors';

import DashboardScreen from '../screens/dashboard/DashboardScreen';
import ProfileScreen from '../screens/profile/ProfileScreen';

import LotesScreen from '../screens/lotes/LotesScreen';
import LoteDetailScreen from '../screens/lotes/LoteDetailScreen';
import LoteFormScreen from '../screens/lotes/LoteFormScreen';

import ActividadesScreen from '../screens/actividades/ActividadesScreen';
import MisActividadesScreen from '../screens/actividades/MisActividadesScreen';
import EvidenciasScreen from '../screens/actividades/EvidenciasScreen';
import ActividadFormScreen from '../screens/actividades/ActividadFormScreen';
import ActividadCompleteScreen from '../screens/actividades/ActividadCompleteScreen';
import ActividadDetailScreen from '../screens/actividades/ActividadDetailScreen';

import ProduccionesScreen from '../screens/producciones/ProduccionesScreen';
import ProduccionFormScreen from '../screens/producciones/ProduccionFormScreen';

import InsumosScreen from '../screens/insumos/InsumosScreen';
import InsumoFormScreen from '../screens/insumos/InsumoFormScreen';

import CertificacionesScreen from '../screens/certificaciones/CertificacionesScreen';
import ClimaScreen from '../screens/clima/ClimaScreen';

import AlmacenesScreen from '../screens/almacen/AlmacenesScreen';
import AlmacenDetailScreen from '../screens/almacen/AlmacenDetailScreen';
import MovimientosScreen from '../screens/almacen/MovimientosScreen';
import MovimientoFormScreen from '../screens/almacen/MovimientoFormScreen';
import TrasladosPlantaScreen from '../screens/almacen/TrasladosPlantaScreen';

import ProcesosPlantaScreen from '../screens/planta/ProcesosPlantaScreen';
import MaquinasScreen from '../screens/planta/MaquinasScreen';
import PlantillasScreen from '../screens/planta/PlantillasScreen';
import ProcesamientoScreen from '../screens/planta/ProcesamientoScreen';
import TareasPlantaScreen from '../screens/planta/TareasPlantaScreen';
import RecepcionPlantaScreen from '../screens/planta/RecepcionPlantaScreen';

import EnviosScreen from '../screens/logistica/EnviosScreen';
import EnvioDetailScreen from '../screens/logistica/EnvioDetailScreen';
import RutasScreen from '../screens/logistica/RutasScreen';
import RutaDetailScreen from '../screens/logistica/RutaDetailScreen';
import TransportistasScreen from '../screens/logistica/TransportistasScreen';
import VehiculosScreen from '../screens/logistica/VehiculosScreen';
import IncidentesScreen from '../screens/logistica/IncidentesScreen';
import DocumentosScreen from '../screens/logistica/DocumentosScreen';

import PedidosScreen from '../screens/pedidos/PedidosScreen';
import PedidoDetailScreen from '../screens/pedidos/PedidoDetailScreen';
import PedidosDistribucionScreen from '../screens/pedidos/PedidosDistribucionScreen';
import PuntosVentaScreen from '../screens/pedidos/PuntosVentaScreen';

import UsuariosScreen from '../screens/admin/UsuariosScreen';
import UsuarioDetailScreen from '../screens/admin/UsuarioDetailScreen';
import PlataformaPresentacionScreen from '../screens/presentation/PlataformaPresentacionScreen';

const Stack = createNativeStackNavigator();

const screenOptions = {
  headerStyle: { backgroundColor: Colors.primary },
  headerTintColor: '#FFF',
  headerTitleStyle: { fontWeight: '600' },
};

export default function MainNavigator() {
  return (
    <Stack.Navigator screenOptions={screenOptions}>
      <Stack.Screen name="Dashboard" component={DashboardScreen} options={{ title: 'AgroFusion' }} />
      <Stack.Screen name="Profile" component={ProfileScreen} options={{ title: 'Mi Perfil' }} />

      <Stack.Screen name="Lotes" component={LotesScreen} options={{ title: 'Lotes' }} />
      <Stack.Screen name="LoteDetail" component={LoteDetailScreen} options={{ title: 'Detalle del Lote' }} />
      <Stack.Screen name="LoteForm" component={LoteFormScreen} options={({ route }) => ({ title: route.params?.id ? 'Editar Lote' : 'Nuevo Lote' })} />

      <Stack.Screen name="Actividades" component={ActividadesScreen} options={{ title: 'Actividades' }} />
      <Stack.Screen name="MisActividades" component={MisActividadesScreen} options={{ title: 'Mis Actividades' }} />
      <Stack.Screen name="Evidencias" component={EvidenciasScreen} options={{ title: 'Mis Evidencias' }} />
      <Stack.Screen name="ActividadForm" component={ActividadFormScreen} options={({ route }) => ({ title: route.params?.id ? 'Editar Actividad' : 'Nueva Actividad' })} />
      <Stack.Screen name="ActividadComplete" component={ActividadCompleteScreen} options={{ title: 'Completar Actividad' }} />
      <Stack.Screen name="ActividadDetail" component={ActividadDetailScreen} options={{ title: 'Detalle Actividad' }} />

      <Stack.Screen name="Producciones" component={ProduccionesScreen} options={{ title: 'Cosechas' }} />
      <Stack.Screen name="ProduccionForm" component={ProduccionFormScreen} options={({ route }) => ({ title: route.params?.id ? 'Editar Cosecha' : 'Nueva Cosecha' })} />

      <Stack.Screen name="Insumos" component={InsumosScreen} options={{ title: 'Insumos' }} />
      <Stack.Screen name="InsumoForm" component={InsumoFormScreen} options={({ route }) => ({ title: route.params?.id ? 'Editar Insumo' : 'Nuevo Insumo' })} />

      <Stack.Screen name="Certificaciones" component={CertificacionesScreen} options={{ title: 'Certificaciones' }} />
      <Stack.Screen name="Clima" component={ClimaScreen} options={{ title: 'Clima' }} />

      <Stack.Screen name="Almacenes" component={AlmacenesScreen} options={{ title: 'Almacenes' }} />
      <Stack.Screen name="AlmacenDetail" component={AlmacenDetailScreen} options={{ title: 'Detalle del Almacén' }} />
      <Stack.Screen name="Movimientos" component={MovimientosScreen} options={{ title: 'Movimientos' }} />
      <Stack.Screen name="MovimientoForm" component={MovimientoFormScreen} options={{ title: 'Nuevo Movimiento' }} />
      <Stack.Screen name="TrasladosPlanta" component={TrasladosPlantaScreen} options={{ title: 'Traslados desde Planta' }} />

      <Stack.Screen name="ProcesosPlanta" component={ProcesosPlantaScreen} options={{ title: 'Procesos de Planta' }} />
      <Stack.Screen name="Maquinas" component={MaquinasScreen} options={{ title: 'Máquinas' }} />
      <Stack.Screen name="Plantillas" component={PlantillasScreen} options={{ title: 'Plantillas' }} />
      <Stack.Screen name="Procesamiento" component={ProcesamientoScreen} options={{ title: 'Procesamiento' }} />
      <Stack.Screen name="TareasPlanta" component={TareasPlantaScreen} options={{ title: 'Mis Tareas' }} />
      <Stack.Screen name="RecepcionPlanta" component={RecepcionPlantaScreen} options={{ title: 'Recepción' }} />

      <Stack.Screen name="Envios" component={EnviosScreen} options={{ title: 'Envíos' }} />
      <Stack.Screen name="EnvioDetail" component={EnvioDetailScreen} options={{ title: 'Detalle del Envío' }} />
      <Stack.Screen name="Rutas" component={RutasScreen} options={{ title: 'Rutas' }} />
      <Stack.Screen name="RutaDetail" component={RutaDetailScreen} options={{ title: 'Detalle de Ruta' }} />
      <Stack.Screen name="Transportistas" component={TransportistasScreen} options={{ title: 'Transportistas' }} />
      <Stack.Screen name="Vehiculos" component={VehiculosScreen} options={{ title: 'Vehículos' }} />
      <Stack.Screen name="Incidentes" component={IncidentesScreen} options={{ title: 'Incidentes' }} />
      <Stack.Screen name="Documentos" component={DocumentosScreen} options={{ title: 'Documentos' }} />

      <Stack.Screen name="Pedidos" component={PedidosScreen} options={{ title: 'Pedidos' }} />
      <Stack.Screen name="PedidoDetail" component={PedidoDetailScreen} options={{ title: 'Detalle del Pedido' }} />
      <Stack.Screen name="PedidosDistribucion" component={PedidosDistribucionScreen} options={{ title: 'Distribución' }} />
      <Stack.Screen name="PuntosVenta" component={PuntosVentaScreen} options={{ title: 'Puntos de Venta' }} />

      <Stack.Screen name="Usuarios" component={UsuariosScreen} options={{ title: 'Usuarios' }} />
      <Stack.Screen name="UsuarioDetail" component={UsuarioDetailScreen} options={{ title: 'Detalle del Usuario' }} />
      <Stack.Screen name="PlataformaPresentacion" component={PlataformaPresentacionScreen} options={{ headerShown: false }} />
    </Stack.Navigator>
  );
}
