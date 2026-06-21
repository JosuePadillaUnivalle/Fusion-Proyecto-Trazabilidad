import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import apiClient from '../../api/client';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';

export default function TransportistasScreen() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await apiClient.get('/usuarios');
      const users = res.data?.data || res.data || [];
      setData(users.filter(u => u.roles?.some(r => r.name === 'transportista')));
    } catch (e) {} finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, []);

  const renderItem = ({ item }) => (
    <Card
      title={`${item.nombre} ${item.apellido}`}
      subtitle={item.email}
      icon="car-sport-outline"
      iconColor="#37474F"
      rightElement={<StatusBadge status={item.activo !== false ? 'activo' : 'inactivo'} label={item.activo !== false ? 'Activo' : 'Inactivo'} />}
    >
      <View style={styles.cardBody}>
        {item.telefono && (
          <View style={styles.infoRow}>
            <Ionicons name="call-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.telefono}</Text>
          </View>
        )}
        {item.perfil_transportista && (
          <View style={styles.infoRow}>
            <Ionicons name="card-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>Licencia: {item.perfil_transportista.tipo_licencia}</Text>
          </View>
        )}
      </View>
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando transportistas..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.usuarioid)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="car-sport-outline" message="No hay transportistas registrados" />}
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
