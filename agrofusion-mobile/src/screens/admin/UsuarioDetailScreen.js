import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { usuariosApi } from '../../api/client';
import LoadingSpinner from '../../components/LoadingSpinner';
import StatusBadge from '../../components/StatusBadge';
import { Colors } from '../../constants/colors';
import { formatDate, formatDateTime } from '../../utils/helpers';
import { ROLE_LABELS } from '../../constants/roles';

export default function UsuarioDetailScreen({ route, navigation }) {
  const { id } = route.params;
  const [usuario, setUsuario] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => { loadUsuario(); }, [id]);

  const loadUsuario = async () => {
    try {
      const res = await usuariosApi.get(id);
      setUsuario(res.data?.data || res.data);
    } catch (e) {
      Alert.alert('Error', 'No se pudo cargar el usuario');
    } finally { setLoading(false); }
  };

  const handleDelete = () => {
    Alert.alert('Eliminar Usuario', '¿Estás seguro de eliminar este usuario?', [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Eliminar', style: 'destructive', onPress: async () => {
        try { await usuariosApi.delete(id); navigation.goBack(); } catch (e) { Alert.alert('Error', 'No se pudo eliminar'); }
      }},
    ]);
  };

  if (loading) return <LoadingSpinner fullScreen message="Cargando usuario..." />;
  if (!usuario) return <View style={styles.container}><Text>No se encontró el usuario</Text></View>;

  const roleName = usuario.roles?.[0]?.name || '';

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <View style={styles.avatar}>
          <Ionicons name="person" size={40} color="#FFF" />
        </View>
        <Text style={styles.name}>{usuario.nombre} {usuario.apellido}</Text>
        <Text style={styles.email}>{usuario.email}</Text>
        <View style={styles.roleBadge}>
          <Text style={styles.roleText}>{ROLE_LABELS[roleName] || roleName || 'Sin rol'}</Text>
        </View>
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Información Personal</Text>
        <InfoRow icon="call-outline" label="Teléfono" value={usuario.telefono || '-'} />
        <InfoRow icon="card-outline" label="CI/NIT" value={usuario.ci_nit || '-'} />
        <InfoRow icon="calendar-outline" label="Fecha de registro" value={formatDate(usuario.fecharegistro)} />
        <InfoRow icon="time-outline" label="Último login" value={formatDateTime(usuario.ultimologin)} />
        <InfoRow icon="shield-checkmark-outline" label="Estado de cuenta" value={usuario.estado_cuenta || 'APROBADO'} />
      </View>

      <View style={styles.actions}>
        <TouchableOpacity style={styles.deleteButton} onPress={handleDelete}>
          <Ionicons name="trash-outline" size={20} color="#FFF" />
          <Text style={styles.buttonText}>Eliminar Usuario</Text>
        </TouchableOpacity>
      </View>
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
  header: { backgroundColor: Colors.primary, padding: 24, alignItems: 'center', paddingBottom: 24 },
  avatar: { width: 80, height: 80, borderRadius: 40, backgroundColor: 'rgba(255,255,255,0.2)', justifyContent: 'center', alignItems: 'center', marginBottom: 12 },
  name: { fontSize: 22, fontWeight: 'bold', color: '#FFF' },
  email: { fontSize: 14, color: 'rgba(255,255,255,0.8)', marginTop: 4 },
  roleBadge: { backgroundColor: 'rgba(255,255,255,0.2)', paddingHorizontal: 16, paddingVertical: 6, borderRadius: 16, marginTop: 8 },
  roleText: { color: '#FFF', fontSize: 13, fontWeight: '600' },
  section: { backgroundColor: Colors.surface, margin: 12, borderRadius: 12, padding: 16 },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.text, marginBottom: 12 },
  infoRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: Colors.border },
  infoContent: { marginLeft: 12, flex: 1 },
  infoLabel: { fontSize: 12, color: Colors.textSecondary },
  infoValue: { fontSize: 15, color: Colors.text, fontWeight: '500' },
  actions: { padding: 16, paddingBottom: 32 },
  deleteButton: { flexDirection: 'row', backgroundColor: Colors.error, padding: 14, borderRadius: 10, justifyContent: 'center', alignItems: 'center', gap: 8 },
  buttonText: { color: '#FFF', fontWeight: '600', fontSize: 15 },
});
