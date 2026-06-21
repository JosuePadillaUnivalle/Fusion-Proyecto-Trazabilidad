import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { procesosPlantaApi } from '../../api/client';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function ProcesosPlantaScreen({ navigation }) {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await procesosPlantaApi.list();
      setData(res.data?.data || res.data || []);
    } catch (e) {} finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, []);

  const renderItem = ({ item }) => (
    <Card
      title={item.nombre || `Proceso #${item.procesoplantaid}`}
      subtitle={item.descripcion || ''}
      icon="cog-outline"
      iconColor="#BF360C"
    >
      <View style={styles.cardBody}>
        {item.duracion_estimada && (
          <View style={styles.infoRow}>
            <Ionicons name="time-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>Duración: {item.duracion_estimada}</Text>
          </View>
        )}
        {item.activo !== undefined && (
          <StatusBadge status={item.activo ? 'activo' : 'inactivo'} label={item.activo ? 'Activo' : 'Inactivo'} />
        )}
      </View>
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando procesos..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.procesoplantaid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="cog-outline" message="No hay procesos registrados" />}
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
