import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { incidentesApi } from '../../api/client';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDateTime } from '../../utils/helpers';

export default function IncidentesScreen() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await incidentesApi.list();
      setData(res.data?.data || res.data || []);
    } catch (e) {} finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, []);

  const handleResolve = (id) => {
    Alert.alert('Resolver Incidente', '¿Marcar este incidente como resuelto?', [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Resolver', onPress: async () => {
        try { await incidentesApi.resolve(id); loadData(); } catch (e) { Alert.alert('Error', 'No se pudo resolver'); }
      }},
    ]);
  };

  const renderItem = ({ item }) => (
    <Card
      title={item.tipo || item.descripcion || `Incidente #${item.incidenteid || item.id}`}
      subtitle={item.envio?.descripcion || ''}
      icon="warning-outline"
      iconColor="#C62828"
      rightElement={<StatusBadge status={item.resuelto ? 'completado' : 'pendiente'} label={item.resuelto ? 'Resuelto' : 'Pendiente'} />}
    >
      <View style={styles.cardBody}>
        <View style={styles.infoRow}>
          <Ionicons name="calendar-outline" size={16} color={Colors.textSecondary} />
          <Text style={styles.infoText}>{formatDateTime(item.fechaincidente || item.fecharegistro)}</Text>
        </View>
        {item.descripcion && (
          <Text style={styles.description} numberOfLines={3}>{item.descripcion}</Text>
        )}
        {!item.resuelto && (
          <TouchableOpacity style={styles.resolveButton} onPress={() => handleResolve(item.incidenteid || item.id)}>
            <Text style={styles.resolveText}>Marcar como resuelto</Text>
          </TouchableOpacity>
        )}
      </View>
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando incidentes..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.incidenteid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="warning-outline" message="No hay incidentes registrados" />}
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
  description: { fontSize: 13, color: Colors.text, fontStyle: 'italic' },
  resolveButton: { backgroundColor: Colors.success + '20', padding: 10, borderRadius: 8, alignItems: 'center', marginTop: 8 },
  resolveText: { color: Colors.success, fontWeight: '600', fontSize: 13 },
});
