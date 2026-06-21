import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import apiClient from '../../api/client';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';

export default function PuntosVentaScreen() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await apiClient.get('/puntos-venta');
      setData(res.data?.data || res.data || []);
    } catch (e) {} finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, []);

  const renderItem = ({ item }) => (
    <Card
      title={item.nombre || `Punto de Venta #${item.puntoventaid || item.id}`}
      subtitle={item.direccion || ''}
      icon="storefront-outline"
      iconColor="#6A1B9A"
      rightElement={<StatusBadge status={item.activo !== false ? 'activo' : 'inactivo'} label={item.activo !== false ? 'Activo' : 'Inactivo'} />}
    >
      <View style={styles.cardBody}>
        {item.telefono && (
          <View style={styles.infoRow}>
            <Ionicons name="call-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.telefono}</Text>
          </View>
        )}
        {item.minorista && (
          <View style={styles.infoRow}>
            <Ionicons name="person-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.minorista.nombre} {item.minorista.apellido}</Text>
          </View>
        )}
      </View>
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando puntos de venta..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.puntoventaid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="storefront-outline" message="No hay puntos de venta registrados" />}
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
