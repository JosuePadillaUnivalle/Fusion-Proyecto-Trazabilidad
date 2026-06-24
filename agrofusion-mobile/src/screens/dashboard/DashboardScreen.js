import React, { useEffect, useState } from 'react';

import {

  View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl,

} from 'react-native';

import { Ionicons } from '@expo/vector-icons';

import { useAuth } from '../../context/AuthContext';

import { Colors } from '../../constants/colors';

import {

  canAccessAgricultural, canAccessPlant, canAccessLogistics,

  canAccessRetail, canAccessAdmin, ROLE_LABELS,

  isMobileWorker,

} from '../../constants/roles';

import { WORKER_ROLE_CONFIG, getWorkerRoleKey } from '../../constants/roleFeatures';

import { USE_MOCK_DATA } from '../../constants/designMode';

import { getMockDashboardStats } from '../../data/mockAgricultorData';

import {

  getMockPlantaDashboardStats,

  getMockTransportistaDashboardStats,

  getMockMinoristaDashboardStats,

  getMockMayoristaDashboardStats,

} from '../../data/mockWorkersData';

import WorkerDashboardLayout from '../../components/WorkerDashboardLayout';

import { lotesApi, produccionesApi } from '../../api/client';



const STAT_COLORS = {

  primary: Colors.primary,

  warning: Colors.warning,

  success: Colors.success,

  error: Colors.error,

  info: Colors.info,

};



const STAT_GETTERS = {

  agricultor: getMockDashboardStats,

  planta: getMockPlantaDashboardStats,

  transportista: getMockTransportistaDashboardStats,

  minorista: getMockMinoristaDashboardStats,

  mayorista: getMockMayoristaDashboardStats,

};



function buildStatsRows(config, stats, navigation) {

  const rows = [];

  for (let i = 0; i < config.stats.length; i += 2) {

    rows.push(

      config.stats.slice(i, i + 2).map((s) => ({

        icon: s.icon,

        label: s.label,

        value: String(stats[s.key] ?? 0),

        color: STAT_COLORS[s.colorKey] || Colors.primary,

        onPress: () => navigation.navigate(s.screen),

      })),

    );

  }

  return rows;

}



function buildMenuItems(config, navigation) {

  return config.menu.map((item) => ({

    title: item.title,

    subtitle: item.subtitle,

    icon: item.icon,

    color: item.color || (item.colorKey ? STAT_COLORS[item.colorKey] : Colors.primary),

    onPress: () => navigation.navigate(item.screen),

  }));

}



export default function DashboardScreen({ navigation }) {

  const { user } = useAuth();

  const [refreshing, setRefreshing] = useState(false);

  const [managerStats, setManagerStats] = useState({ lotes: 0, producciones: 0 });



  const workerRoleKey = getWorkerRoleKey(user);

  const esTrabajador = isMobileWorker(user);



  const loadManagerStats = async () => {

    if (esTrabajador) return;

    try {

      const [lotesRes, prodRes] = await Promise.allSettled([

        canAccessAgricultural(user) ? lotesApi.list() : Promise.resolve({ data: [] }),

        canAccessAgricultural(user) ? produccionesApi.list() : Promise.resolve({ data: [] }),

      ]);

      setManagerStats({

        lotes: lotesRes.status === 'fulfilled' ? (lotesRes.value.data?.data || lotesRes.value.data || []).length : 0,

        producciones: prodRes.status === 'fulfilled' ? (prodRes.value.data?.data || prodRes.value.data || []).length : 0,

      });

    } catch (e) {}

  };



  useEffect(() => { loadManagerStats(); }, []);



  const onRefresh = async () => {

    setRefreshing(true);

    await loadManagerStats();

    setRefreshing(false);

  };



  const userName = user ? `${user.nombre || ''} ${user.apellido || ''}`.trim() : 'Usuario';

  const userRole = user?.roles?.[0]?.name || 'Sin rol';



  if (workerRoleKey && WORKER_ROLE_CONFIG[workerRoleKey]) {

    const config = WORKER_ROLE_CONFIG[workerRoleKey];

    const getter = STAT_GETTERS[workerRoleKey];

    const stats = USE_MOCK_DATA

      ? getter(user?.usuarioid)

      : Object.fromEntries(config.stats.map((s) => [s.key, 0]));



    return (

      <WorkerDashboardLayout

        userName={userName}

        userRole={userRole}

        roleIcon={config.icon}

        refreshing={refreshing}

        onRefresh={onRefresh}

        statsRows={buildStatsRows(config, stats, navigation)}

        menuItems={buildMenuItems(config, navigation)}

      />

    );

  }



  const menuItems = [];

  if (canAccessAgricultural(user)) {

    menuItems.push(

      { title: 'Lotes', subtitle: 'Parcelas', icon: 'map-outline', color: Colors.primary, onPress: () => navigation.navigate('Lotes') },

      { title: 'Actividades', subtitle: 'Tareas', icon: 'calendar-outline', color: Colors.primary, onPress: () => navigation.navigate('Actividades') },

      { title: 'Cosechas', subtitle: 'Producción', icon: 'basket-outline', color: Colors.primary, onPress: () => navigation.navigate('Producciones') },

    );

  }

  if (canAccessPlant(user)) {

    menuItems.push(

      { title: 'Procesamiento', subtitle: 'Planta', icon: 'business-outline', color: '#475569', onPress: () => navigation.navigate('Procesamiento') },

      { title: 'Mis Tareas', subtitle: 'Asignadas', icon: 'list-circle-outline', color: '#475569', onPress: () => navigation.navigate('TareasPlanta') },

    );

  }

  if (canAccessLogistics(user)) {

    menuItems.push(

      { title: 'Envíos', subtitle: 'Despachos', icon: 'cube-outline', color: '#475569', onPress: () => navigation.navigate('Envios') },

      { title: 'Rutas', subtitle: 'Entregas', icon: 'git-branch-outline', color: '#475569', onPress: () => navigation.navigate('Rutas') },

    );

  }

  if (canAccessRetail(user)) {

    menuItems.push(

      { title: 'Pedidos', subtitle: 'Órdenes', icon: 'cart-outline', color: '#475569', onPress: () => navigation.navigate('Pedidos') },

    );

  }

  if (canAccessAdmin(user)) {

    menuItems.push(

      { title: 'Usuarios', subtitle: 'Gestión', icon: 'people-outline', color: Colors.accent, onPress: () => navigation.navigate('Usuarios') },

    );

  }

  menuItems.push(

    { title: 'Mi Perfil', subtitle: 'Cuenta', icon: 'person-circle-outline', color: '#475569', onPress: () => navigation.navigate('Profile') },

  );



  return (

    <ScrollView

      style={styles.container}

      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}

    >

      <View style={styles.header}>

        <View style={styles.headerTop}>

          <View>

            <Text style={styles.greeting}>Panel de control</Text>

            <Text style={styles.userName}>{userName}</Text>

          </View>

          <View style={styles.avatar}>

            <Text style={styles.avatarText}>{(userName[0] || 'U').toUpperCase()}</Text>

          </View>

        </View>

        <View style={styles.roleBadge}>

          <Ionicons name="shield-checkmark-outline" size={12} color={Colors.primaryDark} />

          <Text style={styles.roleBadgeText}>{ROLE_LABELS[userRole] || userRole}</Text>

        </View>

      </View>



      {canAccessAgricultural(user) && (

        <View style={styles.statsSection}>

          <StatCard icon="map-outline" label="Lotes activos" value={String(managerStats.lotes)} color={Colors.primary} onPress={() => navigation.navigate('Lotes')} />

          <StatCard icon="basket-outline" label="Cosechas" value={String(managerStats.producciones)} color={Colors.primary} onPress={() => navigation.navigate('Producciones')} />

        </View>

      )}



      <View style={styles.section}>

        <Text style={styles.sectionTitle}>Módulos del sistema</Text>

        <View style={styles.grid}>

          {menuItems.map((item) => (

            <TouchableOpacity key={item.title} style={styles.menuCard} onPress={item.onPress} activeOpacity={0.7}>

              <View style={[styles.iconCircle, { backgroundColor: item.color + '15' }]}>

                <Ionicons name={item.icon} size={22} color={item.color} />

              </View>

              <Text style={styles.menuTitle}>{item.title}</Text>

              <Text style={styles.menuSubtitle} numberOfLines={1}>{item.subtitle}</Text>

            </TouchableOpacity>

          ))}

        </View>

      </View>

    </ScrollView>

  );

}



function StatCard({ icon, label, value, color, onPress }) {

  return (

    <TouchableOpacity style={styles.statCard} onPress={onPress} activeOpacity={0.8}>

      <View style={styles.statHeader}>

        <View style={[styles.statIcon, { backgroundColor: color + '15' }]}>

          <Ionicons name={icon} size={22} color={color} />

        </View>

        <Text style={styles.statLabel}>{label}</Text>

      </View>

      <Text style={styles.statNumber}>{value}</Text>

    </TouchableOpacity>

  );

}



const styles = StyleSheet.create({

  container: { flex: 1, backgroundColor: Colors.background },

  header: {

    backgroundColor: Colors.surface, margin: 16, marginBottom: 12,

    borderRadius: 16, padding: 20, borderWidth: 1, borderColor: Colors.border,

  },

  headerTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },

  greeting: { fontSize: 13, color: Colors.textMuted, textTransform: 'uppercase', letterSpacing: 1 },

  userName: { fontSize: 22, fontWeight: '700', color: Colors.text, marginTop: 4 },

  avatar: {

    width: 48, height: 48, borderRadius: 14, backgroundColor: Colors.divider,

    justifyContent: 'center', alignItems: 'center', borderWidth: 1, borderColor: Colors.border,

  },

  avatarText: { fontSize: 18, fontWeight: '700', color: Colors.textSecondary },

  roleBadge: {

    flexDirection: 'row', alignItems: 'center', gap: 6, backgroundColor: Colors.divider,

    paddingHorizontal: 12, paddingVertical: 6, borderRadius: 8, marginTop: 16, alignSelf: 'flex-start',

    borderWidth: 1, borderColor: Colors.border,

  },

  roleBadgeText: { color: Colors.textSecondary, fontSize: 12, fontWeight: '600' },

  statsSection: { flexDirection: 'row', paddingHorizontal: 16, gap: 12, marginBottom: 8 },

  statCard: {

    flex: 1, backgroundColor: Colors.surface, borderRadius: 16, padding: 16,

    borderWidth: 1, borderColor: Colors.border,

  },

  statHeader: { flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 10 },

  statIcon: { width: 40, height: 40, borderRadius: 10, justifyContent: 'center', alignItems: 'center' },

  statLabel: { fontSize: 13, color: Colors.textSecondary, fontWeight: '600' },

  statNumber: { fontSize: 28, fontWeight: '700', color: Colors.text },

  section: { padding: 16, paddingTop: 8 },

  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.text, marginBottom: 12 },

  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },

  menuCard: {

    width: '47.4%', backgroundColor: Colors.surface, borderRadius: 16, padding: 16,

    borderWidth: 1, borderColor: Colors.border,

  },

  iconCircle: {

    width: 44, height: 44, borderRadius: 12,

    justifyContent: 'center', alignItems: 'center', marginBottom: 14,

  },

  menuTitle: { fontSize: 15, fontWeight: '600', color: Colors.text },

  menuSubtitle: { fontSize: 12, color: Colors.textMuted, marginTop: 3 },

});


