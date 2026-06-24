import React, { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../context/AuthContext';
import { Colors } from '../../constants/colors';
import { ROLE_LABELS } from '../../constants/roles';
import { formatDate, formatDateTime } from '../../utils/helpers';

export default function ProfileScreen({ navigation }) {
  const { user, logout } = useAuth();
  const roleName = user?.roles?.[0]?.name || '';

  const handleLogout = () => {
    Alert.alert('Cerrar Sesión', '¿Estás seguro de cerrar sesión?', [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Cerrar Sesión', style: 'destructive', onPress: logout },
    ]);
  };

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <View style={styles.avatar}>
          <Ionicons name="leaf" size={36} color="#FFF" />
        </View>
        <Text style={styles.name}>{user?.nombre} {user?.apellido}</Text>
        <Text style={styles.email}>{user?.email}</Text>
        <View style={styles.roleBadge}>
          <Text style={styles.roleText}>{ROLE_LABELS[roleName] || roleName || 'Sin rol'}</Text>
        </View>
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Información Personal</Text>
        <InfoRow icon="person-outline" label="Nombre completo" value={`${user?.nombre || ''} ${user?.apellido || ''}`} />
        <InfoRow icon="mail-outline" label="Correo electrónico" value={user?.email || '-'} />
        <InfoRow icon="call-outline" label="Teléfono" value={user?.telefono || '-'} />
        <InfoRow icon="card-outline" label="CI/NIT" value={user?.ci_nit || '-'} />
        <InfoRow icon="shield-outline" label="Rol" value={ROLE_LABELS[roleName] || roleName || '-'} />
        <InfoRow icon="calendar-outline" label="Miembro desde" value={formatDate(user?.fecharegistro)} />
        <InfoRow icon="time-outline" label="Último acceso" value={formatDateTime(user?.ultimologin)} />
      </View>

      <TouchableOpacity
        style={styles.aboutButton}
        onPress={() => navigation.navigate('PlataformaPresentacion')}
        activeOpacity={0.8}
      >
        <Ionicons name="play-circle-outline" size={22} color={Colors.primary} />
        <View style={{ flex: 1 }}>
          <Text style={styles.aboutTitle}>Conoce AgroFusion</Text>
          <Text style={styles.aboutSub}>Presentación de trazabilidad agrícola</Text>
        </View>
        <Ionicons name="chevron-forward" size={20} color={Colors.textMuted} />
      </TouchableOpacity>

      <TouchableOpacity style={styles.logoutButton} onPress={handleLogout}>
        <Ionicons name="log-out-outline" size={22} color="#FFF" />
        <Text style={styles.logoutText}>Cerrar Sesión</Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

const InfoRow = ({ icon, label, value }) => (
  <View style={styles.infoRow}>
    <Ionicons name={icon} size={18} color={Colors.primary} />
    <View style={styles.infoContent}>
      <Text style={styles.infoLabel}>{label}</Text>
      <Text style={styles.infoValue}>{value}</Text>
    </View>
  </View>
);

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  header: { backgroundColor: Colors.primary, padding: 32, alignItems: 'center', paddingBottom: 28 },
  avatar: {
    width: 80, height: 80, borderRadius: 40, backgroundColor: 'rgba(255,255,255,0.2)',
    justifyContent: 'center', alignItems: 'center', marginBottom: 16,
  },
  name: { fontSize: 24, fontWeight: 'bold', color: '#FFF' },
  email: { fontSize: 14, color: 'rgba(255,255,255,0.8)', marginTop: 4 },
  roleBadge: {
    backgroundColor: 'rgba(255,255,255,0.2)', paddingHorizontal: 16, paddingVertical: 6,
    borderRadius: 16, marginTop: 12,
  },
  roleText: { color: '#FFF', fontSize: 13, fontWeight: '600' },
  section: { backgroundColor: Colors.surface, margin: 12, borderRadius: 12, padding: 16 },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.text, marginBottom: 12 },
  infoRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: Colors.border },
  infoContent: { marginLeft: 12, flex: 1 },
  infoLabel: { fontSize: 12, color: Colors.textSecondary },
  infoValue: { fontSize: 15, color: Colors.text, fontWeight: '500' },
  aboutButton: {
    flexDirection: 'row', alignItems: 'center', gap: 12,
    backgroundColor: Colors.surface, marginHorizontal: 16, marginBottom: 8,
    padding: 16, borderRadius: 12, borderWidth: 1, borderColor: Colors.border,
  },
  aboutTitle: { fontSize: 15, fontWeight: '600', color: Colors.text },
  aboutSub: { fontSize: 12, color: Colors.textMuted, marginTop: 2 },
  logoutButton: {
    flexDirection: 'row', backgroundColor: Colors.error, margin: 16, padding: 16,
    borderRadius: 12, justifyContent: 'center', alignItems: 'center', gap: 8, marginBottom: 32,
  },
  logoutText: { color: '#FFF', fontWeight: '600', fontSize: 16 },
});
