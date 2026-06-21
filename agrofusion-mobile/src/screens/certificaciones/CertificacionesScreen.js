import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { certificacionesApi } from '../../api/client';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function CertificacionesScreen() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await certificacionesApi.list();
      setData(res.data?.data || res.data || []);
    } catch (e) {} finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, []);

  const renderItem = ({ item }) => (
    <Card
      title={item.nombre || item.tipo_certificacion || `Certificación #${item.certificacionid}`}
      subtitle={item.lote?.nombre || 'Sin lote'}
      icon="shield-checkmark-outline"
      iconColor="#00897B"
      rightElement={<StatusBadge status={item.aprobado ? 'aprobado' : 'pendiente'} label={item.aprobado ? 'Aprobado' : 'Pendiente'} />}
    >
      <View style={styles.cardBody}>
        <View style={styles.infoRow}>
          <Ionicons name="calendar-outline" size={16} color={Colors.textSecondary} />
          <Text style={styles.infoText}>{formatDate(item.fechacertificacion || item.fecharegistro)}</Text>
        </View>
        {item.entidad_certificadora && (
          <View style={styles.infoRow}>
            <Ionicons name="business-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.entidad_certificadora}</Text>
          </View>
        )}
      </View>
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando certificaciones..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.certificacionid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="shield-checkmark-outline" message="No hay certificaciones registradas" />}
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
