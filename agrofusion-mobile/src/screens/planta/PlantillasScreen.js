import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { plantillasApi } from '../../api/client';
import Card from '../../components/Card';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';

export default function PlantillasScreen() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await plantillasApi.list();
      setData(res.data?.data || res.data || []);
    } catch (e) {} finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, []);

  const renderItem = ({ item }) => (
    <Card
      title={item.nombre || `Plantilla #${item.plantillatransformacionid || item.id}`}
      subtitle={item.descripcion || ''}
      icon="document-text-outline"
      iconColor="#1A237E"
    >
      <View style={styles.cardBody}>
        {item.pasos && item.pasos.length > 0 && (
          <View style={styles.infoRow}>
            <Ionicons name="list-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.pasos.length} pasos</Text>
          </View>
        )}
        {item.proceso_planta && (
          <View style={styles.infoRow}>
            <Ionicons name="cog-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.proceso_planta.nombre}</Text>
          </View>
        )}
      </View>
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando plantillas..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.plantillatransformacionid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="document-text-outline" message="No hay plantillas registradas" />}
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
