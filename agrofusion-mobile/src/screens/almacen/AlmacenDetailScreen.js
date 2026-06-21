import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { almacenesApi } from '../../api/client';
import LoadingSpinner from '../../components/LoadingSpinner';
import StatusBadge from '../../components/StatusBadge';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function AlmacenDetailScreen({ route, navigation }) {
  const { id } = route.params;
  const [almacen, setAlmacen] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => { loadAlmacen(); }, [id]);

  const loadAlmacen = async () => {
    try {
      const res = await almacenesApi.get(id);
      setAlmacen(res.data?.data || res.data);
    } catch (e) {
      Alert.alert('Error', 'No se pudo cargar el almacén');
    } finally { setLoading(false); }
  };

  if (loading) return <LoadingSpinner fullScreen message="Cargando almacén..." />;
  if (!almacen) return <View style={styles.container}><Text>No se encontró el almacén</Text></View>;

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>{almacen.nombre || `Almacén #${almacen.almacenid}`}</Text>
        <StatusBadge status={almacen.activo !== false ? 'activo' : 'inactivo'} label={almacen.activo !== false ? 'Activo' : 'Inactivo'} />
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Información General</Text>
        <InfoRow icon="layers-outline" label="Tipo" value={almacen.tipo?.nombre || almacen.tipo_almacen?.nombre || '-'} />
        <InfoRow icon="location-outline" label="Ubicación" value={almacen.ubicacion || '-'} />
        <InfoRow icon="cube-outline" label="Capacidad" value={almacen.capacidad || '-'} />
        <InfoRow icon="calendar-outline" label="Fecha registro" value={formatDate(almacen.fecharegistro)} />
        {almacen.descripcion && <InfoRow icon="document-text-outline" label="Descripción" value={almacen.descripcion} />}
      </View>

      <View style={styles.actions}>
        <TouchableOpacity style={styles.movimientosButton} onPress={() => navigation.navigate('Movimientos')}>
          <Ionicons name="swap-horizontal-outline" size={20} color="#FFF" />
          <Text style={styles.buttonText}>Ver Movimientos</Text>
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
  header: { backgroundColor: '#5D4037', padding: 24, paddingBottom: 20 },
  title: { fontSize: 24, fontWeight: 'bold', color: '#FFF', marginBottom: 8 },
  section: { backgroundColor: Colors.surface, margin: 12, borderRadius: 12, padding: 16 },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.text, marginBottom: 12 },
  infoRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: Colors.border },
  infoContent: { marginLeft: 12, flex: 1 },
  infoLabel: { fontSize: 12, color: Colors.textSecondary },
  infoValue: { fontSize: 15, color: Colors.text, fontWeight: '500' },
  actions: { padding: 16, paddingBottom: 32 },
  movimientosButton: { flexDirection: 'row', backgroundColor: Colors.primary, padding: 14, borderRadius: 10, justifyContent: 'center', alignItems: 'center', gap: 8 },
  buttonText: { color: '#FFF', fontWeight: '600', fontSize: 15 },
});
