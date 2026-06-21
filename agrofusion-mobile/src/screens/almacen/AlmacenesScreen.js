import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { almacenesApi } from '../../api/client';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';

export default function AlmacenesScreen({ navigation }) {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await almacenesApi.list();
      setData(res.data?.data || res.data || []);
    } catch (e) {} finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, []);

  const renderItem = ({ item }) => (
    <Card
      title={item.nombre || `Almacén #${item.almacenid}`}
      subtitle={item.tipo?.nombre || item.tipo_almacen?.nombre || 'Sin tipo'}
      icon="business-outline"
      iconColor="#5D4037"
      onPress={() => navigation.navigate('AlmacenDetail', { id: item.almacenid })}
      rightElement={<StatusBadge status={item.activo ? 'activo' : 'inactivo'} label={item.activo !== false ? 'Activo' : 'Inactivo'} />}
    >
      <View style={styles.cardBody}>
        {item.ubicacion && (
          <View style={styles.infoRow}>
            <Ionicons name="location-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.ubicacion}</Text>
          </View>
        )}
        {item.capacidad && (
          <View style={styles.infoRow}>
            <Ionicons name="cube-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>Capacidad: {item.capacidad}</Text>
          </View>
        )}
        {item.ambito && (
          <View style={styles.infoRow}>
            <Ionicons name="layers-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>Ámbito: {item.ambito}</Text>
          </View>
        )}
      </View>
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando almacenes..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.almacenid)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="business-outline" message="No hay almacenes registrados" />}
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
