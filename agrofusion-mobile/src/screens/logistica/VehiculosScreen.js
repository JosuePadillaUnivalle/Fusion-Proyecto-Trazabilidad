import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import apiClient from '../../api/client';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';

export default function VehiculosScreen() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await apiClient.get('/vehiculos');
      setData(res.data?.data || res.data || []);
    } catch (e) {} finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, []);

  const renderItem = ({ item }) => (
    <Card
      title={item.placa || item.nombre || `Vehículo #${item.vehiculoid || item.id}`}
      subtitle={item.tipo_vehiculo?.nombre || ''}
      icon="car-outline"
      iconColor="#546E7A"
      rightElement={<StatusBadge status={item.activo !== false ? 'activo' : 'inactivo'} label={item.activo !== false ? 'Activo' : 'Mantenimiento'} />}
    >
      <View style={styles.cardBody}>
        {item.marca && (
          <View style={styles.infoRow}>
            <Ionicons name="pricetag-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.marca} {item.modelo}</Text>
          </View>
        )}
        {item.capacidad_carga && (
          <View style={styles.infoRow}>
            <Ionicons name="cube-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>Capacidad: {item.capacidad_carga}</Text>
          </View>
        )}
      </View>
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando vehículos..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.vehiculoid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="car-outline" message="No hay vehículos registrados" />}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  list: { padding: 16 },
  cardBody: { marginTop: 12, gap: 6 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
});
