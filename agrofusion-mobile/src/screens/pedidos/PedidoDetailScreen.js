import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { pedidosApi } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getWorkerRoleKey } from '../../constants/roleFeatures';
import { getMockPedidoDistribucion, getMockPedidosMinorista } from '../../data/mockWorkersData';
import {
  getMockPedidoActions,
  aprobarMockPedidoDistribucion,
  rechazarMockPedidoDistribucion,
  updateMockPedidoPlantaEstado,
} from '../../data/mockRoleActions';
import LoadingSpinner from '../../components/LoadingSpinner';
import StatusBadge from '../../components/StatusBadge';
import { Colors } from '../../constants/colors';
import { formatDateTime } from '../../utils/helpers';

export default function PedidoDetailScreen({ route }) {
  const { id, tipo } = route.params;
  const { user } = useAuth();
  const roleKey = getWorkerRoleKey(user);
  const [pedido, setPedido] = useState(null);
  const [loading, setLoading] = useState(true);
  const esDistribucion = tipo === 'distribucion';

  const loadPedido = useCallback(async () => {
    setLoading(true);
    try {
      if (USE_MOCK_DATA) {
        if (esDistribucion) {
          const found = getMockPedidoDistribucion(id);
          if (found) { setPedido({ ...found }); return; }
        } else {
          const all = getMockPedidosMinorista();
          const found = all.find(p => (p.pedidoid || p.id) === Number(id));
          if (found) { setPedido({ ...found }); return; }
          const dist = getMockPedidoDistribucion(id);
          if (dist) { setPedido({ ...dist }); return; }
        }
      }
      const res = await pedidosApi.get(id);
      setPedido(res.data?.data || res.data);
    } catch (e) {
      if (USE_MOCK_DATA) {
        const found = getMockPedidoDistribucion(id) || getMockPedidosMinorista().find(p => p.pedidoid === Number(id));
        if (found) setPedido({ ...found });
      }
    } finally {
      setLoading(false);
    }
  }, [id, esDistribucion]);

  useFocusEffect(useCallback(() => { loadPedido(); }, [loadPedido]));

  const handleAction = (key) => {
    const labels = {
      aprobar: ['Aprobar pedido', '¿Aprobar y enviar a tránsito?', aprobarMockPedidoDistribucion],
      rechazar: ['Rechazar pedido', '¿Rechazar esta solicitud?', rechazarMockPedidoDistribucion],
      preparar: ['Preparar pedido', '¿Marcar en preparación?', (pid) => updateMockPedidoPlantaEstado(pid, 'en_preparacion')],
    };
    const [title, msg, fn] = labels[key] || [];
    if (!fn) return;
    Alert.alert(title, msg, [
      { text: 'Cancelar', style: 'cancel' },
      {
        text: 'Confirmar',
        style: key === 'rechazar' ? 'destructive' : 'default',
        onPress: () => {
          if (USE_MOCK_DATA) {
            fn(id);
            loadPedido();
          }
        },
      },
    ]);
  };

  const actions = USE_MOCK_DATA ? getMockPedidoActions(pedido, roleKey) : [];

  if (loading) return <LoadingSpinner fullScreen message="Cargando pedido..." />;
  if (!pedido) return <View style={styles.container}><Text style={styles.errorText}>No se encontró el pedido</Text></View>;

  const pedidoId = pedido.pedidodistribucionid || pedido.pedidoid || pedido.id;
  const titulo = esDistribucion || pedido.pedidodistribucionid
    ? `Distribución #${pedidoId}`
    : `Pedido #${pedidoId}`;
  const cliente = pedido.punto_venta?.nombre || pedido.cliente?.nombre || pedido.cliente_comercial?.nombre || '-';

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>{titulo}</Text>
        <StatusBadge status={pedido.estado || 'pendiente'} />
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Información</Text>
        <InfoRow icon="calendar-outline" label="Fecha" value={formatDateTime(pedido.fechapedido || pedido.fecharegistro)} />
        <InfoRow icon="storefront-outline" label={esDistribucion || pedido.punto_venta ? 'Punto de venta' : 'Cliente'} value={cliente} />
        {pedido.total != null && <InfoRow icon="cash-outline" label="Total" value={`Bs. ${pedido.total}`} />}
        {pedido.observaciones && <InfoRow icon="document-text-outline" label="Observaciones" value={pedido.observaciones} />}
      </View>

      {pedido.detalles && pedido.detalles.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Detalle ({pedido.detalles.length} items)</Text>
          {pedido.detalles.map((d, i) => (
            <View key={i} style={styles.subItem}>
              <Text style={styles.subItemTitle}>{d.producto?.nombre || d.descripcion || `Item #${i + 1}`}</Text>
              <Text style={styles.subItemDate}>{d.cantidad} x {d.precio_unitario ? `Bs. ${d.precio_unitario}` : ''}</Text>
            </View>
          ))}
        </View>
      )}

      {actions.length > 0 && (
        <View style={styles.actions}>
          {actions.map((a) => (
            <TouchableOpacity
              key={a.key}
              style={[styles.actionBtn, a.key === 'rechazar' && styles.actionBtnDanger]}
              onPress={() => handleAction(a.key)}
            >
              <Text style={styles.actionBtnText}>{a.label}</Text>
            </TouchableOpacity>
          ))}
        </View>
      )}
    </ScrollView>
  );
}

const InfoRow = ({ icon, label, value }) => (
  <View style={styles.infoRow}>
    <Ionicons name={icon} size={18} color={Colors.primary} />
    <View style={styles.infoContent}>
      <Text style={styles.infoLabel}>{label}</Text>
      <Text style={styles.infoValue}>{value}</Text>
    </View>
  </View>
);

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  header: {
    backgroundColor: Colors.primary, padding: 24, paddingBottom: 20,
    borderBottomWidth: 1, borderBottomColor: Colors.primaryDark,
  },
  title: { fontSize: 22, fontWeight: '700', color: '#FFF', marginBottom: 8 },
  errorText: { padding: 24, fontSize: 16, color: Colors.textSecondary },
  section: {
    backgroundColor: Colors.surface, margin: 12, borderRadius: 16, padding: 16,
    borderWidth: 1, borderColor: Colors.border,
  },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.text, marginBottom: 12 },
  infoRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: Colors.border },
  infoContent: { marginLeft: 12, flex: 1 },
  infoLabel: { fontSize: 12, color: Colors.textSecondary },
  infoValue: { fontSize: 15, color: Colors.text, fontWeight: '500' },
  subItem: { paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: Colors.border },
  subItemTitle: { fontSize: 14, fontWeight: '500', color: Colors.text },
  subItemDate: { fontSize: 12, color: Colors.textSecondary, marginTop: 2 },
  actions: { padding: 16, paddingBottom: 32, gap: 10 },
  actionBtn: {
    backgroundColor: Colors.primary, padding: 14, borderRadius: 10, alignItems: 'center',
  },
  actionBtnDanger: { backgroundColor: Colors.error },
  actionBtnText: { color: '#FFF', fontWeight: '600', fontSize: 15 },
});
