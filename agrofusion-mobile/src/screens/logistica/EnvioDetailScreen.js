import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { enviosApi } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { isTransportista } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockEnvioById, getMockRutaById } from '../../data/mockWorkersData';
import { iniciarMockEnvio, entregarMockEnvio, puedeIniciarMockRuta } from '../../data/mockRoleActions';
import LoadingSpinner from '../../components/LoadingSpinner';
import StatusBadge from '../../components/StatusBadge';
import { Colors } from '../../constants/colors';
import { formatDate, formatDateTime } from '../../utils/helpers';

export default function EnvioDetailScreen({ route, navigation }) {
  const { id } = route.params;
  const { user } = useAuth();
  const esTransportista = isTransportista(user);
  const [envio, setEnvio] = useState(null);
  const [loading, setLoading] = useState(true);

  const loadEnvio = useCallback(async () => {
    setLoading(true);
    try {
      if (USE_MOCK_DATA) {
        const found = getMockEnvioById(id);
        if (found) {
          setEnvio({ ...found });
          return;
        }
      }
      const res = await enviosApi.list();
      const all = res.data?.data || res.data || [];
      const found = all.find(e => (e.asignacionid || e.id) === id);
      setEnvio(found);
    } catch (e) {
      if (USE_MOCK_DATA) setEnvio(getMockEnvioById(id));
    } finally {
      setLoading(false);
    }
  }, [id]);

  useFocusEffect(useCallback(() => { loadEnvio(); }, [loadEnvio]));

  const handleIniciar = () => {
    if (USE_MOCK_DATA && envio?.rutamultientregaid) {
      const ruta = getMockRutaById(envio.rutamultientregaid);
      const check = puedeIniciarMockRuta(ruta);
      if (!check.ok) {
        Alert.alert(
          'Multiruta pendiente',
          `${check.motivo}. Abra la ruta para verificar conexión y condiciones del vehículo.`,
          [
            { text: 'Cancelar', style: 'cancel' },
            { text: 'Ir a la ruta', onPress: () => navigation.navigate('RutaDetail', { id: envio.rutamultientregaid }) },
          ],
        );
        return;
      }
    }
    Alert.alert('Iniciar envío', '¿Marcar este envío como en ruta?', [
      { text: 'Cancelar', style: 'cancel' },
      {
        text: 'Iniciar',
        onPress: () => {
          if (USE_MOCK_DATA) {
            iniciarMockEnvio(id);
            loadEnvio();
          }
        },
      },
    ]);
  };

  const handleEntregar = () => {
    Alert.alert('Confirmar entrega', '¿Marcar este envío como entregado?', [
      { text: 'Cancelar', style: 'cancel' },
      {
        text: 'Entregar',
        onPress: () => {
          if (USE_MOCK_DATA) {
            entregarMockEnvio(id);
            loadEnvio();
          }
        },
      },
    ]);
  };

  const estado = envio?.estadoenvio || envio?.estado?.nombre?.toLowerCase()?.replace(' ', '_');
  const puedeIniciar = USE_MOCK_DATA && esTransportista && ['pendiente', 'asignado'].includes(estado);
  const puedeEntregar = USE_MOCK_DATA && esTransportista && ['en_ruta', 'en ruta'].includes(estado);

  if (loading) return <LoadingSpinner fullScreen message="Cargando envío..." />;
  if (!envio) return <View style={styles.container}><Text style={styles.errorText}>No se encontró el envío</Text></View>;

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>{envio.descripcion || `Envío #${envio.asignacionid || envio.id}`}</Text>
        <StatusBadge status={envio.estado?.nombre || envio.estadoenvio || 'pendiente'} />
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Información del envío</Text>
        <InfoRow icon="calendar-outline" label="Fecha de envío" value={formatDateTime(envio.fechaenvio || envio.fecharegistro)} />
        <InfoRow icon="person-outline" label="Transportista" value={envio.transportista ? `${envio.transportista.nombre} ${envio.transportista.apellido}` : '-'} />
        <InfoRow icon="car-outline" label="Vehículo" value={envio.vehiculo ? `${envio.vehiculo.placa} · ${envio.vehiculo.nombre}` : '-'} />
        <InfoRow icon="location-outline" label="Origen" value={envio.origen || '-'} />
        <InfoRow icon="flag-outline" label="Destino" value={envio.destino || '-'} />
        {envio.paradas != null && (
          <InfoRow icon="git-branch-outline" label="Paradas" value={`${envio.paradasCompletadas || 0}/${envio.paradas}`} />
        )}
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

      {(puedeIniciar || puedeEntregar) && (
        <View style={styles.actions}>
          {puedeIniciar && (
            <TouchableOpacity style={styles.actionBtn} onPress={handleIniciar}>
              <Ionicons name="navigate-outline" size={20} color="#FFF" />
              <Text style={styles.actionBtnText}>Iniciar envío</Text>
            </TouchableOpacity>
          )}
          {puedeEntregar && (
            <TouchableOpacity style={[styles.actionBtn, styles.actionBtnSuccess]} onPress={handleEntregar}>
              <Ionicons name="checkmark-circle-outline" size={20} color="#FFF" />
              <Text style={styles.actionBtnText}>Confirmar entrega</Text>
            </TouchableOpacity>
          )}
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
  header: {
    backgroundColor: Colors.primary, padding: 24, paddingBottom: 20,
    borderBottomWidth: 1, borderBottomColor: Colors.primaryDark,
  },
  title: { fontSize: 22, fontWeight: '700', color: '#FFF', marginBottom: 8 },
  errorText: { padding: 24, fontSize: 16, color: Colors.textSecondary },
  section: {
    backgroundColor: Colors.surface, margin: 12, borderRadius: 16, padding: 16,
    borderWidth: 1, borderColor: Colors.border,
  },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.text, marginBottom: 12 },
  infoRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: Colors.border },
  infoContent: { marginLeft: 12, flex: 1 },
  infoLabel: { fontSize: 12, color: Colors.textSecondary },
  infoValue: { fontSize: 15, color: Colors.text, fontWeight: '500' },
  subItem: { paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: Colors.border },
  subItemTitle: { fontSize: 14, fontWeight: '500', color: Colors.text },
  subItemDate: { fontSize: 12, color: Colors.textSecondary, marginTop: 2 },
  actions: { padding: 16, paddingBottom: 32, gap: 10 },
  actionBtn: {
    flexDirection: 'row', backgroundColor: Colors.primary, padding: 14, borderRadius: 10,
    justifyContent: 'center', alignItems: 'center', gap: 8,
  },
  actionBtnSuccess: { backgroundColor: Colors.success },
  actionBtnText: { color: '#FFF', fontWeight: '600', fontSize: 15 },
});
