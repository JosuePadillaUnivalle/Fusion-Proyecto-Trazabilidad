import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Alert } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../context/AuthContext';
import { isMayorista } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockTrasladosPlanta } from '../../data/mockWorkersData';
import { firmarMockTrasladoPlanta } from '../../data/mockRoleActions';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDateTime } from '../../utils/helpers';

export default function TrasladosPlantaScreen() {
  const { user } = useAuth();
  const esMayorista = isMayorista(user);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [filter, setFilter] = useState('todos');

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esMayorista) {
        setData(getMockTrasladosPlanta(filter));
      } else {
        setData([]);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esMayorista) setData(getMockTrasladosPlanta(filter));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { loadData(); }, [filter]));

  const handleFirmar = (item) => {
    Alert.alert('Firmar recepción', `¿Confirmar recepción de ${item.codigo}?`, [
      { text: 'Cancelar', style: 'cancel' },
      {
        text: 'Firmar',
        onPress: () => {
          if (USE_MOCK_DATA) {
            firmarMockTrasladoPlanta(item.id);
            loadData();
          }
        },
      },
    ]);
  };

  const estadoLabel = (estado) => {
    if (estado === 'esperando_firma') return 'Esperando firma';
    if (estado === 'en_camino') return 'En camino';
    if (estado === 'recibido') return 'Recibido';
    return estado;
  };

  const renderItem = ({ item }) => (
    <View style={styles.card}>
      <View style={styles.cardHeader}>
        <View style={styles.iconBox}>
          <Ionicons name="truck-outline" size={20} color={Colors.primary} />
        </View>
        <View style={{ flex: 1 }}>
          <Text style={styles.cardTitle}>{item.codigo}</Text>
          <Text style={styles.cardSubtitle}>{item.producto} · {item.cantidad} {item.unidad || 'kg'}</Text>
        </View>
        <StatusBadge status={item.estado === 'recibido' ? 'completado' : 'pendiente'} label={estadoLabel(item.estado)} />
      </View>
      <View style={styles.cardBody}>
        <Info icon="business-outline" text={`Origen: ${item.origen}`} />
        <Info icon="calendar-outline" text={formatDateTime(item.fecha || item.fecharegistro)} />
      </View>
      {item.estado === 'esperando_firma' && (
        <TouchableOpacity style={styles.actionBtn} onPress={() => handleFirmar(item)}>
          <Ionicons name="create-outline" size={18} color={Colors.success} />
          <Text style={styles.actionText}>Firmar recepción</Text>
        </TouchableOpacity>
      )}
    </View>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando traslados..." />;

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <Text style={styles.pageTitle}>Traslados desde planta</Text>
        <Text style={styles.pageSubtitle}>{data.length} registros</Text>
      </View>
      <View style={styles.filterRow}>
        {[
          { key: 'todos', label: 'Todos' },
          { key: 'esperando_firma', label: 'Por firmar' },
          { key: 'en_camino', label: 'En camino' },
        ].map((f) => (
          <TouchableOpacity
            key={f.key}
            style={[styles.filterChip, filter === f.key && styles.filterChipActive]}
            onPress={() => setFilter(f.key)}
          >
            <Text style={[styles.filterText, filter === f.key && styles.filterTextActive]}>{f.label}</Text>
          </TouchableOpacity>
        ))}
      </View>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="truck-outline" message="No hay traslados registrados" />}
      />
    </View>
  );
}

function Info({ icon, text }) {
  return (
    <View style={styles.infoRow}>
      <Ionicons name={icon} size={14} color={Colors.textMuted} />
      <Text style={styles.infoText}>{text}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  pageHeader: { padding: 16, paddingBottom: 8 },
  pageTitle: { fontSize: 22, fontWeight: '700', color: Colors.text },
  pageSubtitle: { fontSize: 13, color: Colors.textMuted, marginTop: 2 },
  filterRow: { flexDirection: 'row', paddingHorizontal: 16, gap: 8, marginBottom: 8 },
  filterChip: {
    paddingHorizontal: 14, paddingVertical: 8, borderRadius: 10,
    backgroundColor: Colors.surface, borderWidth: 1, borderColor: Colors.border,
  },
  filterChipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
  filterText: { fontSize: 13, color: Colors.textSecondary, fontWeight: '500' },
  filterTextActive: { color: '#FFF' },
  list: { padding: 16, paddingBottom: 24 },
  card: {
    backgroundColor: Colors.surface, borderRadius: 16, padding: 16,
    marginBottom: 12, borderWidth: 1, borderColor: Colors.border,
  },
  cardHeader: { flexDirection: 'row', alignItems: 'flex-start', gap: 12, marginBottom: 10 },
  iconBox: {
    width: 40, height: 40, borderRadius: 10, backgroundColor: Colors.primaryLight,
    justifyContent: 'center', alignItems: 'center',
  },
  cardTitle: { fontSize: 15, fontWeight: '600', color: Colors.text },
  cardSubtitle: { fontSize: 12, color: Colors.textMuted, marginTop: 2 },
  cardBody: { gap: 6, paddingLeft: 52 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
  actionBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8,
    marginTop: 12, padding: 12, borderRadius: 10,
    backgroundColor: Colors.successLight, borderWidth: 1, borderColor: Colors.success + '40',
  },
  actionText: { color: Colors.success, fontWeight: '600', fontSize: 14 },
});
