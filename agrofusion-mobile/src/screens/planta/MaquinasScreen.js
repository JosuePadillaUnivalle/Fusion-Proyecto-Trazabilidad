import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { maquinasApi } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { isOperadorPlanta } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockMaquinas } from '../../data/mockWorkersData';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';

export default function MaquinasScreen() {
  const { user } = useAuth();
  const esOperador = isOperadorPlanta(user);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esOperador) {
        setData(getMockMaquinas());
      } else {
        const res = await maquinasApi.list();
        setData(res.data?.data || res.data || []);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esOperador) setData(getMockMaquinas());
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { loadData(); }, []));

  const renderItem = ({ item }) => (
    <Card
      title={item.nombre || `Máquina #${item.maquinaplantaid}`}
      subtitle={item.descripcion || ''}
      icon="hardware-chip-outline"
      iconColor={Colors.primary}
      rightElement={<StatusBadge status={item.activo !== false ? 'activo' : 'inactivo'} label={item.activo !== false ? 'Activo' : 'Inactivo'} />}
    >
      <View style={styles.cardBody}>
        {item.modelo && (
          <View style={styles.infoRow}>
            <Ionicons name="pricetag-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>Modelo: {item.modelo}</Text>
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

  if (loading) return <LoadingSpinner fullScreen message="Cargando máquinas..." />;

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <Text style={styles.pageTitle}>Máquinas de planta</Text>
        <Text style={styles.pageSubtitle}>{data.length} equipos</Text>
      </View>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.maquinaplantaid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="hardware-chip-outline" message="No hay máquinas registradas" />}
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
