import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Alert } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import apiClient from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { isMinorista, isOperadorPlanta, isMayorista } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockPedidosDistribucion, getMockPedidosMayorista, getMockPuntosVenta } from '../../data/mockWorkersData';
import { createMockPedidoDistribucionMinorista } from '../../data/mockRoleActions';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function PedidosDistribucionScreen({ navigation }) {
  const { user } = useAuth();
  const esMinorista = isMinorista(user);
  const esOperador = isOperadorPlanta(user);
  const esMayorista = isMayorista(user);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esMayorista) {
        setData(getMockPedidosMayorista());
      } else if (USE_MOCK_DATA && (esMinorista || esOperador)) {
        setData(getMockPedidosDistribucion(esMinorista ? user?.usuarioid : undefined));
      } else {
        const res = await apiClient.get('/pedidos-distribucion');
        setData(res.data?.data || res.data || []);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esMayorista) setData(getMockPedidosMayorista());
      else if (USE_MOCK_DATA && (esMinorista || esOperador)) {
        setData(getMockPedidosDistribucion(esMinorista ? user?.usuarioid : undefined));
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { loadData(); }, []));

  const handleNuevoPedido = () => {
    const puntos = getMockPuntosVenta();
    Alert.alert(
      'Nuevo pedido',
      'Seleccione el punto de venta',
      [
        { text: 'Cancelar', style: 'cancel' },
        ...puntos.map((pv) => ({
          text: pv.nombre,
          onPress: () => {
            createMockPedidoDistribucionMinorista({
              usuarioid: user?.usuarioid,
              puntoVenta: pv,
              detalles: [
                { producto: { nombre: 'Tomate cherry' }, cantidad: 20, precio_unitario: 12 },
                { producto: { nombre: 'Lechuga romana' }, cantidad: 15, precio_unitario: 8 },
              ],
            });
            loadData();
          },
        })),
      ],
    );
  };

  const renderItem = ({ item }) => (
    <TouchableOpacity
      style={styles.card}
      onPress={() => navigation.navigate('PedidoDetail', { id: item.pedidodistribucionid || item.id, tipo: 'distribucion' })}
      activeOpacity={0.8}
    >
      <View style={styles.cardHeader}>
        <View style={[styles.iconBox, { backgroundColor: Colors.primaryLight }]}>
          <Ionicons name="send-outline" size={18} color={Colors.primary} />
        </View>
        <View style={{ flex: 1 }}>
          <Text style={styles.cardTitle}>Distribución #{item.pedidodistribucionid || item.id}</Text>
          <Text style={styles.cardSubtitle}>{item.punto_venta?.nombre || 'Sin punto de venta'}</Text>
        </View>
        <StatusBadge status={item.estado || 'pendiente'} />
      </View>
      <View style={styles.cardBody}>
        <Info icon="calendar-outline" text={formatDate(item.fechapedido || item.fecharegistro)} />
        {item.total != null && <Info icon="cash-outline" text={`Total: Bs. ${item.total}`} />}
        {item.detalles && <Info icon="layers-outline" text={`${item.detalles.length} productos`} />}
      </View>
    </TouchableOpacity>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando pedidos de distribución..." />;

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <View style={styles.pageHeaderRow}>
          <View>
            <Text style={styles.pageTitle}>
              {esMayorista ? 'Pedidos de minoristas' : esMinorista ? 'Mis pedidos' : 'Pedidos de distribución'}
            </Text>
            <Text style={styles.pageSubtitle}>{data.length} registros</Text>
          </View>
          {USE_MOCK_DATA && esMinorista && (
            <TouchableOpacity style={styles.addBtn} onPress={handleNuevoPedido}>
              <Ionicons name="add-circle-outline" size={22} color={Colors.primary} />
              <Text style={styles.addBtnText}>Nuevo</Text>
            </TouchableOpacity>
          )}
        </View>
      </View>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.pedidodistribucionid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="send-outline" message="No hay pedidos de distribución" />}
      />
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
  pageHeaderRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
  pageTitle: { fontSize: 22, fontWeight: '700', color: Colors.text },
  pageSubtitle: { fontSize: 13, color: Colors.textMuted, marginTop: 2 },
  addBtn: { flexDirection: 'row', alignItems: 'center', gap: 4, padding: 8 },
  addBtnText: { fontSize: 13, color: Colors.primary, fontWeight: '600' },
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
