import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { movimientosApi } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { isOperadorPlanta, isMayorista } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockMovimientosForRole } from '../../data/mockWorkersData';
import Card from '../../components/Card';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDateTime } from '../../utils/helpers';

export default function MovimientosScreen({ navigation }) {
  const { user } = useAuth();
  const esOperador = isOperadorPlanta(user);
  const esMayorista = isMayorista(user);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [filter, setFilter] = useState('ingreso');

  const roleKey = esMayorista ? 'mayorista' : 'planta';

  const loadData = async () => {
    setLoading(true);
    try {
      if (USE_MOCK_DATA && (esOperador || esMayorista)) {
        setData(getMockMovimientosForRole(roleKey, filter));
      } else {
        const res = await movimientosApi.list(filter);
        setData(res.data?.data || res.data || []);
      }
    } catch (e) {
      if (USE_MOCK_DATA && (esOperador || esMayorista)) setData(getMockMovimientosForRole(roleKey, filter));
    } finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, [filter]);

  const renderItem = ({ item }) => (
    <Card
      title={item.tipo_movimiento?.nombre || item.tipo || 'Movimiento'}
      subtitle={item.almacen?.nombre || ''}
      icon={item.naturaleza === 'egreso' ? 'arrow-down-circle-outline' : 'arrow-up-circle-outline'}
      iconColor={item.naturaleza === 'egreso' ? Colors.error : Colors.success}
    >
      <View style={styles.cardBody}>
        <View style={styles.infoRow}>
          <Ionicons name="cube-outline" size={16} color={Colors.textSecondary} />
          <Text style={styles.infoText}>Cantidad: {item.cantidad}</Text>
        </View>
        <View style={styles.infoRow}>
          <Ionicons name="calendar-outline" size={16} color={Colors.textSecondary} />
          <Text style={styles.infoText}>{formatDateTime(item.fechamovimiento || item.fecharegistro)}</Text>
        </View>
        {item.produccion && (
          <View style={styles.infoRow}>
            <Ionicons name="basket-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>Producción #{item.produccion.produccionid}</Text>
          </View>
        )}
      </View>
    </Card>
  );

  if (loading && data.length === 0) return <LoadingSpinner fullScreen message="Cargando movimientos..." />;

  return (
    <View style={styles.container}>
      <View style={styles.filterRow}>
        <TouchableOpacity style={[styles.filterChip, filter === 'ingreso' && styles.filterChipActive]} onPress={() => setFilter('ingreso')}>
          <Text style={[styles.filterText, filter === 'ingreso' && styles.filterTextActive]}>Ingresos</Text>
        </TouchableOpacity>
        <TouchableOpacity style={[styles.filterChip, filter === 'egreso' && styles.filterChipActive]} onPress={() => setFilter('egreso')}>
          <Text style={[styles.filterText, filter === 'egreso' && styles.filterTextActive]}>Egresos</Text>
        </TouchableOpacity>
      </View>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.movimientoid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="swap-horizontal-outline" message={`No hay ${filter}s registrados`} />}
      />
      <TouchableOpacity style={styles.fab} onPress={() => navigation.navigate('MovimientoForm', { naturaleza: filter })}>
        <Ionicons name="add" size={28} color="#FFF" />
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  filterRow: { flexDirection: 'row', padding: 12, gap: 8 },
  filterChip: { paddingHorizontal: 20, paddingVertical: 10, borderRadius: 20, backgroundColor: Colors.surface, borderWidth: 1, borderColor: Colors.border },
  filterChipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
  filterText: { fontSize: 14, color: Colors.textSecondary, fontWeight: '500' },
  filterTextActive: { color: '#FFF' },
  list: { padding: 16 },
  cardBody: { marginTop: 12, gap: 6 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
  fab: {
    position: 'absolute', bottom: 24, right: 24, width: 56, height: 56, borderRadius: 28,
    backgroundColor: Colors.primary, justifyContent: 'center', alignItems: 'center', elevation: 6,
  },
});
