import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { DrawerContentScrollView, DrawerItem } from '@react-navigation/drawer';
import { useAuth } from '../context/AuthContext';
import { Colors } from '../constants/colors';
import {
  ROLES, hasRole, canAccessAgricultural, canAccessPlant, canAccessLogistics,
  canAccessRetail, canAccessAdmin, ROLE_LABELS,
} from '../constants/roles';

export default function DrawerContent(props) {
  const { user, logout } = useAuth();
  const { navigation } = props;
  const roleName = user?.roles?.[0]?.name || '';

  const menuSections = [];

  menuSections.push({
    title: 'General',
    items: [
      { label: 'Inicio', icon: 'home-outline', screen: 'Dashboard' },
      { label: 'Mi Perfil', icon: 'person-circle-outline', screen: 'Profile' },
    ],
  });

  if (canAccessAgricultural(user)) {
    menuSections.push({
      title: 'Producción Agrícola',
      items: [
        { label: 'Lotes', icon: 'map-outline', screen: 'Lotes' },
        { label: 'Actividades', icon: 'calendar-outline', screen: 'Actividades' },
        { label: 'Cosechas', icon: 'basket-outline', screen: 'Producciones' },
        { label: 'Insumos', icon: 'flask-outline', screen: 'Insumos' },
        { label: 'Certificaciones', icon: 'shield-checkmark-outline', screen: 'Certificaciones' },
        { label: 'Clima', icon: 'cloud-outline', screen: 'Clima' },
      ],
    });
  }

  if (canAccessAgricultural(user) || canAccessPlant(user)) {
    menuSections.push({
      title: 'Almacén',
      items: [
        { label: 'Almacenes', icon: 'business-outline', screen: 'Almacenes' },
        { label: 'Movimientos', icon: 'swap-horizontal-outline', screen: 'Movimientos' },
      ],
    });
  }

  if (canAccessPlant(user)) {
    menuSections.push({
      title: 'Planta de Procesamiento',
      items: [
        { label: 'Procesamiento', icon: 'business-outline', screen: 'Procesamiento' },
        { label: 'Procesos', icon: 'cog-outline', screen: 'ProcesosPlanta' },
        { label: 'Máquinas', icon: 'hardware-chip-outline', screen: 'Maquinas' },
        { label: 'Plantillas', icon: 'document-text-outline', screen: 'Plantillas' },
        { label: 'Mis Tareas', icon: 'list-circle-outline', screen: 'TareasPlanta' },
      ],
    });
  }

  if (canAccessLogistics(user)) {
    menuSections.push({
      title: 'Logística',
      items: [
        { label: 'Envíos', icon: 'cube-outline', screen: 'Envios' },
        { label: 'Rutas', icon: 'git-branch-outline', screen: 'Rutas' },
        { label: 'Transportistas', icon: 'car-sport-outline', screen: 'Transportistas' },
        { label: 'Vehículos', icon: 'car-outline', screen: 'Vehiculos' },
        { label: 'Incidentes', icon: 'warning-outline', screen: 'Incidentes' },
      ],
    });
  }

  if (canAccessRetail(user)) {
    menuSections.push({
      title: 'Comercialización',
      items: [
        { label: 'Pedidos', icon: 'cart-outline', screen: 'Pedidos' },
        { label: 'Puntos de Venta', icon: 'storefront-outline', screen: 'PuntosVenta' },
        { label: 'Distribución', icon: 'send-outline', screen: 'PedidosDistribucion' },
      ],
    });
  }

  if (canAccessAdmin(user)) {
    menuSections.push({
      title: 'Administración',
      items: [
        { label: 'Usuarios', icon: 'people-outline', screen: 'Usuarios' },
      ],
    });
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <View style={styles.avatar}>
          <Ionicons name="leaf" size={28} color="#FFF" />
        </View>
        <Text style={styles.userName}>{user?.nombre} {user?.apellido}</Text>
        <Text style={styles.userRole}>{ROLE_LABELS[roleName] || roleName}</Text>
      </View>

      <DrawerContentScrollView {...props} style={styles.scrollView}>
        {menuSections.map((section, si) => (
          <View key={si}>
            <Text style={styles.sectionTitle}>{section.title}</Text>
            {section.items.map((item, ii) => (
              <DrawerItem
                key={ii}
                label={item.label}
                icon={({ color, size }) => <Ionicons name={item.icon} size={size} color={color} />}
                onPress={() => navigation.navigate(item.screen)}
                labelStyle={styles.drawerLabel}
                activeBackgroundColor={Colors.primary + '30'}
                activeTintColor={Colors.primary}
                inactiveTintColor={Colors.sidebarText}
              />
            ))}
          </View>
        ))}
      </DrawerContentScrollView>

      <TouchableOpacity style={styles.logoutButton} onPress={logout}>
        <Ionicons name="log-out-outline" size={22} color="#FFF" />
        <Text style={styles.logoutText}>Cerrar Sesión</Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.sidebar },
  header: { padding: 24, paddingTop: 48, backgroundColor: '#161b22', alignItems: 'center' },
  avatar: {
    width: 56, height: 56, borderRadius: 28, backgroundColor: Colors.primary,
    justifyContent: 'center', alignItems: 'center', marginBottom: 12,
    shadowColor: Colors.primary, shadowOffset: { width: 0, height: 0 }, shadowOpacity: 0.4, shadowRadius: 8, elevation: 4,
  },
  userName: { fontSize: 18, fontWeight: 'bold', color: '#FFF' },
  userRole: { fontSize: 13, color: 'rgba(255,255,255,0.5)', marginTop: 4 },
  scrollView: { flex: 1 },
  sectionTitle: {
    fontSize: 11, fontWeight: '700', color: 'rgba(255,255,255,0.4)',
    textTransform: 'uppercase', paddingHorizontal: 16, paddingTop: 16, paddingBottom: 4, letterSpacing: 0.5,
  },
  drawerLabel: { fontSize: 14 },
  logoutButton: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    padding: 16, borderTopWidth: 1, borderTopColor: 'rgba(255,255,255,0.1)', gap: 8,
  },
  logoutText: { color: '#FFF', fontSize: 15, fontWeight: '600' },
});
