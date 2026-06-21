import React, { useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../context/AuthContext';
import { Colors } from '../../constants/colors';
import {
  ROLES, hasRole, canAccessAgricultural, canAccessPlant, canAccessLogistics,
  canAccessRetail, canAccessAdmin, ROLE_LABELS,
} from '../../constants/roles';
import { lotesApi, produccionesApi } from '../../api/client';

export default function DashboardScreen({ navigation }) {
  const { user } = useAuth();
  const [stats, setStats] = useState({ lotes: 0, producciones: 0 });
  const [refreshing, setRefreshing] = useState(false);

  const loadStats = async () => {
    try {
      const [lotesRes, prodRes] = await Promise.allSettled([
        canAccessAgricultural(user) ? lotesApi.list() : Promise.resolve({ data: [] }),
        canAccessAgricultural(user) ? produccionesApi.list() : Promise.resolve({ data: [] }),
      ]);
      setStats({
        lotes: lotesRes.status === 'fulfilled' ? (lotesRes.value.data?.data || lotesRes.value.data || []).length : 0,
        producciones: prodRes.status === 'fulfilled' ? (prodRes.value.data?.data || prodRes.value.data || []).length : 0,
      });
    } catch (e) {}
  };

  useEffect(() => { loadStats(); }, []);

  const onRefresh = async () => { setRefreshing(true); await loadStats(); setRefreshing(false); };

  const menuItems = [];

  if (canAccessAgricultural(user)) {
    menuItems.push(
      { title: 'Lotes', subtitle: 'Gestión de parcelas', icon: 'map-outline', color: Colors.primary, screen: 'Lotes' },
      { title: 'Actividades', subtitle: 'Tareas y calendario', icon: 'calendar-outline', color: Colors.info, screen: 'Actividades' },
      { title: 'Cosechas', subtitle: 'Registro de producciones', icon: 'basket-outline', color: Colors.warning, screen: 'Producciones' },
      { title: 'Insumos', subtitle: 'Inventario de materiales', icon: 'flask-outline', color: Colors.purple, screen: 'Insumos' },
      { title: 'Certificaciones', subtitle: 'Certificados de campo', icon: 'shield-checkmark-outline', color: Colors.success, screen: 'Certificaciones' },
      { title: 'Clima', subtitle: 'Datos meteorológicos', icon: 'cloud-outline', color: Colors.info, screen: 'Clima' },
    );
  }

  if (canAccessAgricultural(user) || canAccessPlant(user)) {
    menuItems.push(
      { title: 'Almacenes', subtitle: 'Gestión de almacenes', icon: 'business-outline', color: '#5D4037', screen: 'Almacenes' },
      { title: 'Movimientos', subtitle: 'Ingresos y egresos', icon: 'swap-horizontal-outline', color: '#455A64', screen: 'Movimientos' },
    );
  }

  if (canAccessPlant(user)) {
    menuItems.push(
      { title: 'Procesamiento', subtitle: 'Procesos de planta', icon: 'business-outline', color: '#E65100', screen: 'Procesamiento' },
      { title: 'Procesos', subtitle: 'Catálogo de procesos', icon: 'cog-outline', color: '#BF360C', screen: 'ProcesosPlanta' },
      { title: 'Máquinas', subtitle: 'Equipos de planta', icon: 'hardware-chip-outline', color: '#4E342E', screen: 'Maquinas' },
      { title: 'Plantillas', subtitle: 'Plantillas de transformación', icon: 'document-text-outline', color: '#1A237E', screen: 'Plantillas' },
      { title: 'Mis Tareas', subtitle: 'Tareas asignadas', icon: 'list-circle-outline', color: '#00695C', screen: 'TareasPlanta' },
    );
  }

  if (canAccessLogistics(user)) {
    menuItems.push(
      { title: 'Envíos', subtitle: 'Asignaciones y envíos', icon: 'cube-outline', color: Colors.info, screen: 'Envios' },
      { title: 'Rutas', subtitle: 'Rutas multi-entrega', icon: 'git-branch-outline', color: '#283593', screen: 'Rutas' },
      { title: 'Transportistas', subtitle: 'Gestión de conductores', icon: 'car-sport-outline', color: '#37474F', screen: 'Transportistas' },
      { title: 'Vehículos', subtitle: 'Flota vehicular', icon: 'car-outline', color: '#546E7A', screen: 'Vehiculos' },
      { title: 'Incidentes', subtitle: 'Reportes de incidentes', icon: 'warning-outline', color: Colors.error, screen: 'Incidentes' },
    );
  }

  if (canAccessRetail(user)) {
    menuItems.push(
      { title: 'Pedidos', subtitle: 'Pedidos y órdenes', icon: 'cart-outline', color: '#AD1457', screen: 'Pedidos' },
      { title: 'Puntos de Venta', subtitle: 'Puntos de distribución', icon: 'storefront-outline', color: '#6A1B9A', screen: 'PuntosVenta' },
      { title: 'Distribución', subtitle: 'Pedidos de distribución', icon: 'send-outline', color: '#4527A0', screen: 'PedidosDistribucion' },
    );
  }

  if (canAccessAdmin(user)) {
    menuItems.push(
      { title: 'Usuarios', subtitle: 'Gestión de usuarios', icon: 'people-outline', color: Colors.primary, screen: 'Usuarios' },
    );
  }

  menuItems.push(
    { title: 'Mi Perfil', subtitle: 'Ver y editar perfil', icon: 'person-circle-outline', color: '#424242', screen: 'Profile' },
  );

  const userName = user ? `${user.nombre || ''} ${user.apellido || ''}`.trim() : 'Usuario';
  const userRole = user?.roles?.[0]?.name || 'Sin rol';

  return (
    <ScrollView
      style={styles.container}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
    >
      <View style={styles.welcomeCard}>
        <View style={styles.welcomeContent}>
          <Text style={styles.welcomeText}>Bienvenido</Text>
          <Text style={styles.userName}>{userName}</Text>
          <View style={styles.roleBadge}>
            <Text style={styles.roleBadgeText}>{ROLE_LABELS[userRole] || userRole}</Text>
          </View>
        </View>
        <View style={styles.logoCircle}>
          <Ionicons name="leaf" size={36} color="rgba(255,255,255,0.3)" />
        </View>
      </View>

      {canAccessAgricultural(user) && (
        <View style={styles.statsRow}>
          <View style={styles.statCard}>
            <Text style={styles.statNumber}>{stats.lotes}</Text>
            <Text style={styles.statLabel}>Lotes</Text>
          </View>
          <View style={styles.statCard}>
            <Text style={styles.statNumber}>{stats.producciones}</Text>
            <Text style={styles.statLabel}>Cosechas</Text>
          </View>
        </View>
      )}

      <Text style={styles.sectionTitle}>Módulos</Text>
      <View style={styles.grid}>
        {menuItems.map((item, index) => (
          <TouchableOpacity
            key={index}
            style={styles.menuCard}
            onPress={() => navigation.navigate(item.screen)}
            activeOpacity={0.7}
          >
            <View style={[styles.menuIcon, { backgroundColor: item.color + '20' }]}>
              <Ionicons name={item.icon} size={26} color={item.color} />
            </View>
            <Text style={styles.menuTitle}>{item.title}</Text>
            <Text style={styles.menuSubtitle} numberOfLines={1}>{item.subtitle}</Text>
          </TouchableOpacity>
        ))}
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  welcomeCard: {
    backgroundColor: Colors.primary, padding: 24, flexDirection: 'row',
    justifyContent: 'space-between', alignItems: 'center',
  },
  welcomeContent: { flex: 1 },
  welcomeText: { fontSize: 14, color: 'rgba(255,255,255,0.8)' },
  userName: { fontSize: 24, fontWeight: 'bold', color: '#FFF', marginTop: 4 },
  roleBadge: {
    backgroundColor: 'rgba(255,255,255,0.2)', paddingHorizontal: 12, paddingVertical: 4,
    borderRadius: 12, marginTop: 8, alignSelf: 'flex-start',
  },
  roleBadgeText: { color: '#FFF', fontSize: 12, fontWeight: '600' },
  logoCircle: { width: 60, height: 60, borderRadius: 30, backgroundColor: 'rgba(255,255,255,0.15)', justifyContent: 'center', alignItems: 'center' },
  statsRow: { flexDirection: 'row', padding: 16, gap: 12 },
  statCard: {
    flex: 1, backgroundColor: Colors.surface, borderRadius: 12, padding: 16,
    alignItems: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1, shadowRadius: 3, elevation: 2,
  },
  statNumber: { fontSize: 28, fontWeight: 'bold', color: Colors.primary },
  statLabel: { fontSize: 13, color: Colors.textSecondary, marginTop: 4 },
  sectionTitle: { fontSize: 18, fontWeight: '700', color: Colors.text, paddingHorizontal: 16, marginBottom: 12 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', padding: 12, gap: 12 },
  menuCard: {
    width: '47%', backgroundColor: Colors.surface, borderRadius: 12, padding: 16,
    shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.1,
    shadowRadius: 3, elevation: 2,
  },
  menuIcon: { width: 48, height: 48, borderRadius: 12, justifyContent: 'center', alignItems: 'center', marginBottom: 12 },
  menuTitle: { fontSize: 15, fontWeight: '600', color: Colors.text },
  menuSubtitle: { fontSize: 12, color: Colors.textMuted, marginTop: 2 },
});
