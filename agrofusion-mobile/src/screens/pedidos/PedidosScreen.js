import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { pedidosApi } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { isMinorista } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockPedidosDistribucion } from '../../data/mockWorkersData';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function PedidosScreen({ navigation }) {
  const { user } = useAuth();
  const esMinorista = isMinorista(user);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esMinorista) {
        setData(getMockPedidosDistribucion(user?.usuarioid));
      } else {
        const res = await pedidosApi.list();
        setData(res.data?.data || res.data || []);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esMinorista) setData(getMockPedidosDistribucion(user?.usuarioid));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { loadData(); }, []));

  const pendientes = data.filter(p => p.estado === 'pendiente' || p.estado === 'confirmado');
  const entregados = data.filter(p => p.estado === 'entregado');

  const renderItem = ({ item }) => (
    <TouchableOpacity
      style={styles.card}
      onPress={() => navigation.navigate('PedidoDetail', {
        id: item.pedidodistribucionid || item.pedidoid || item.id,
        tipo: item.pedidodistribucionid || esMinorista ? 'distribucion' : undefined,
      })}
      activeOpacity={0.8}
    >
      <View style={styles.cardHeader}>
        <View style={[styles.iconBox, { backgroundColor: Colors.primaryLight }]}>
          <Ionicons name="cart-outline" size={18} color={Colors.primary} />
        </View>
        <View style={{ flex: 1 }}>
          <Text style={styles.cardTitle}>
            {esMinorista ? `Pedido #${item.pedidodistribucionid || item.pedidoid || item.id}` : `Pedido #${item.pedidoid || item.id}`}
          </Text>
          <Text style={styles.cardSubtitle}>{item.punto_venta?.nombre || item.cliente?.nombre || 'Cliente'}</Text>
        </View>
        <StatusBadge status={item.estado || 'pendiente'} />
      </View>
      <View style={styles.cardBody}>
        <Info icon="calendar-outline" text={formatDate(item.fechapedido || item.fecharegistro)} />
        {item.total != null && <Info icon="cash-outline" text={`Total: Bs. ${item.total}`} />}
        {(item.detalles?.length || item.items) != null && (
          <Info icon="layers-outline" text={`${item.detalles?.length || item.items} productos`} />
        )}
      </View>
    </TouchableOpacity>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando pedidos..." />;

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <Text style={styles.pageTitle}>{esMinorista ? 'Mis pedidos' : 'Pedidos'}</Text>
        <Text style={styles.pageSubtitle}>{data.length} registros</Text>
      </View>

      {esMinorista && (
        <View style={styles.summary}>
          <SummaryBox value={String(pendientes.length)} label="Activos" icon="time-outline" color={Colors.warning} />
          <SummaryBox value={String(entregados.length)} label="Entregados" icon="checkmark-circle-outline" color={Colors.success} />
        </View>
      )}

      <FlatList
        data={data}
        keyExtractor={(item) => String(item.pedidoid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="cart-outline" message={esMinorista ? 'No tienes pedidos' : 'No hay pedidos registrados'} />}
      />
    </View>
  );
}

function SummaryBox({ value, label, icon, color }) {
  return (
    <View style={styles.summaryBox}>
      <View style={[styles.summaryIcon, { backgroundColor: color + '15' }]}>
        <Ionicons name={icon} size={20} color={color} />
      </View>
      <Text style={[styles.summaryValue, { color }]}>{value}</Text>
      <Text style={styles.summaryLabel}>{label}</Text>
    </View>
  );
}

function Info({ icon, text }) {
  return (
    <View style={styles.infoRow}>
      <Ionicons name={icon} size={14} color={Colors.textMuted} />
      <Text style={styles.infoText}>{text}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  pageHeader: { padding: 16, paddingBottom: 8 },
  pageTitle: { fontSize: 22, fontWeight: '700', color: Colors.text },
  pageSubtitle: { fontSize: 13, color: Colors.textMuted, marginTop: 2 },
  summary: { flexDirection: 'row', paddingHorizontal: 16, gap: 12, marginBottom: 8 },
  summaryBox: {
    flex: 1, backgroundColor: Colors.surface, borderRadius: 16, padding: 16,
    borderWidth: 1, borderColor: Colors.border, alignItems: 'center',
  },
  summaryIcon: { width: 40, height: 40, borderRadius: 10, justifyContent: 'center', alignItems: 'center', marginBottom: 8 },
  summaryValue: { fontSize: 24, fontWeight: '700' },
  summaryLabel: { fontSize: 12, color: Colors.textMuted, marginTop: 2 },
  list: { padding: 16, paddingBottom: 24 },
  card: {
    backgroundColor: Colors.surface, borderRadius: 16, padding: 16,
    marginBottom: 12, borderWidth: 1, borderColor: Colors.border,
  },
  cardHeader: { flexDirection: 'row', alignItems: 'flex-start', gap: 12, marginBottom: 10 },
  iconBox: { width: 40, height: 40, borderRadius: 10, justifyContent: 'center', alignItems: 'center' },
  cardTitle: { fontSize: 15, fontWeight: '600', color: Colors.text },
  cardSubtitle: { fontSize: 12, color: Colors.textMuted, marginTop: 2 },
  cardBody: { gap: 6, paddingLeft: 52 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
});
