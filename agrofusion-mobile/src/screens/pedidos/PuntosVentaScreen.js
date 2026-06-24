import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import apiClient from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { isMinorista } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockPuntosVenta } from '../../data/mockWorkersData';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';

export default function PuntosVentaScreen() {
  const { user } = useAuth();
  const esMinorista = isMinorista(user);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esMinorista) {
        setData(getMockPuntosVenta());
      } else {
        const res = await apiClient.get('/puntos-venta');
        setData(res.data?.data || res.data || []);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esMinorista) setData(getMockPuntosVenta());
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { loadData(); }, []));

  const renderItem = ({ item }) => (
    <View style={styles.card}>
      <View style={styles.cardHeader}>
        <View style={[styles.iconBox, { backgroundColor: Colors.primaryLight }]}>
          <Ionicons name="storefront-outline" size={18} color={Colors.primary} />
        </View>
        <View style={{ flex: 1 }}>
          <Text style={styles.cardTitle}>{item.nombre || `Punto #${item.puntoventaid || item.id}`}</Text>
          <Text style={styles.cardSubtitle}>{item.direccion || 'Sin dirección'}</Text>
        </View>
        <StatusBadge status={item.activo !== false ? 'activo' : 'inactivo'} label={item.activo !== false ? 'Activo' : 'Inactivo'} />
      </View>
      {item.telefono && (
        <View style={styles.infoRow}>
          <Ionicons name="call-outline" size={14} color={Colors.textMuted} />
          <Text style={styles.infoText}>{item.telefono}</Text>
        </View>
      )}
    </View>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando puntos de venta..." />;

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <Text style={styles.pageTitle}>{esMinorista ? 'Mis puntos de venta' : 'Puntos de venta'}</Text>
        <Text style={styles.pageSubtitle}>{data.length} locales</Text>
      </View>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.puntoventaid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="storefront-outline" message="No hay puntos de venta" />}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  pageHeader: { padding: 16, paddingBottom: 8 },
  pageTitle: { fontSize: 22, fontWeight: '700', color: Colors.text },
  pageSubtitle: { fontSize: 13, color: Colors.textMuted, marginTop: 2 },
  list: { padding: 16, paddingBottom: 24 },
  card: {
    backgroundColor: Colors.surface, borderRadius: 16, padding: 16,
    marginBottom: 12, borderWidth: 1, borderColor: Colors.border,
  },
  cardHeader: { flexDirection: 'row', alignItems: 'flex-start', gap: 12, marginBottom: 8 },
  iconBox: { width: 40, height: 40, borderRadius: 10, justifyContent: 'center', alignItems: 'center' },
  cardTitle: { fontSize: 15, fontWeight: '600', color: Colors.text },
  cardSubtitle: { fontSize: 12, color: Colors.textMuted, marginTop: 2 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8, paddingLeft: 52 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
});
