import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import apiClient from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { isOperadorPlanta } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockProcesamientos } from '../../data/mockWorkersData';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function ProcesamientoScreen() {
  const { user } = useAuth();
  const esOperador = isOperadorPlanta(user);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esOperador) {
        setData(getMockProcesamientos());
      } else {
        const res = await apiClient.get('/procesamiento');
        setData(res.data?.data || res.data || []);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esOperador) setData(getMockProcesamientos());
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { loadData(); }, []));

  const renderItem = ({ item }) => (
    <Card
      title={item.nombre || `Procesamiento #${item.id}`}
      subtitle={item.lote?.nombre || ''}
      icon="business-outline"
      iconColor={Colors.primary}
      rightElement={<StatusBadge status={item.estado || 'pendiente'} />}
    >
      <View style={styles.cardBody}>
        <View style={styles.infoRow}>
          <Ionicons name="calendar-outline" size={16} color={Colors.textSecondary} />
          <Text style={styles.infoText}>{formatDate(item.fechainicio || item.fecharegistro)}</Text>
        </View>
        {item.proceso_planta && (
          <View style={styles.infoRow}>
            <Ionicons name="cog-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.proceso_planta.nombre}</Text>
          </View>
        )}
      </View>
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando procesamiento..." />;

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <Text style={styles.pageTitle}>Procesamiento</Text>
        <Text style={styles.pageSubtitle}>{data.length} líneas</Text>
      </View>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="business-outline" message="No hay procesamientos registrados" />}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  pageHeader: { padding: 16, paddingBottom: 8 },
  pageTitle: { fontSize: 22, fontWeight: '700', color: Colors.text },
  pageSubtitle: { fontSize: 13, color: Colors.textMuted, marginTop: 2 },
  list: { padding: 16 },
  cardBody: { marginTop: 12, gap: 6 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
});
