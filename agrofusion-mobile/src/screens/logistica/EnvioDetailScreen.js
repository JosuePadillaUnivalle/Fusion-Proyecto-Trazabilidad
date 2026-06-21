import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { enviosApi } from '../../api/client';
import LoadingSpinner from '../../components/LoadingSpinner';
import StatusBadge from '../../components/StatusBadge';
import { Colors } from '../../constants/colors';
import { formatDate, formatDateTime } from '../../utils/helpers';

export default function EnvioDetailScreen({ route }) {
  const { id } = route.params;
  const [envio, setEnvio] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadEnvio();
  }, [id]);

  const loadEnvio = async () => {
    try {
      const res = await enviosApi.list();
      const all = res.data?.data || res.data || [];
      const found = all.find(e => (e.asignacionid || e.id) === id);
      setEnvio(found);
    } catch (e) {} finally { setLoading(false); }
  };

  if (loading) return <LoadingSpinner fullScreen message="Cargando envío..." />;
  if (!envio) return <View style={styles.container}><Text style={styles.errorText}>No se encontró el envío</Text></View>;

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>{envio.descripcion || `Envío #${envio.asignacionid || envio.id}`}</Text>
        <StatusBadge status={envio.estado?.nombre || envio.estadoenvio || 'pendiente'} />
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Información del Envío</Text>
        <InfoRow icon="calendar-outline" label="Fecha de envío" value={formatDateTime(envio.fechaenvio || envio.fecharegistro)} />
        <InfoRow icon="person-outline" label="Transportista" value={envio.transportista ? `${envio.transportista.nombre} ${envio.transportista.apellido}` : '-'} />
        <InfoRow icon="car-outline" label="Vehículo" value={envio.vehiculo ? (envio.vehiculo.placa || envio.vehiculo.nombre) : '-'} />
        <InfoRow icon="location-outline" label="Origen" value={envio.origen || '-'} />
        <InfoRow icon="flag-outline" label="Destino" value={envio.destino || '-'} />
      </View>

      {envio.cargas && envio.cargas.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Carga ({envio.cargas.length})</Text>
          {envio.cargas.map((carga, i) => (
            <View key={i} style={styles.subItem}>
              <Text style={styles.subItemTitle}>{carga.descripcion || `Carga #${i + 1}`}</Text>
              <Text style={styles.subItemDate}>{carga.cantidad} {carga.unidad || 'unidades'}</Text>
            </View>
          ))}
        </View>
      )}
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
  header: { backgroundColor: '#1565C0', padding: 24, paddingBottom: 20 },
  title: { fontSize: 24, fontWeight: 'bold', color: '#FFF', marginBottom: 8 },
  errorText: { padding: 24, fontSize: 16, color: Colors.textSecondary },
  section: { backgroundColor: Colors.surface, margin: 12, borderRadius: 12, padding: 16 },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.text, marginBottom: 12 },
  infoRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: Colors.border },
  infoContent: { marginLeft: 12, flex: 1 },
  infoLabel: { fontSize: 12, color: Colors.textSecondary },
  infoValue: { fontSize: 15, color: Colors.text, fontWeight: '500' },
  subItem: { paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: Colors.border },
  subItemTitle: { fontSize: 14, fontWeight: '500', color: Colors.text },
  subItemDate: { fontSize: 12, color: Colors.textSecondary, marginTop: 2 },
});
