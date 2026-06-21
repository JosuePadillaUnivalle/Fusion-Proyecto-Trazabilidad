import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { pedidosApi } from '../../api/client';
import LoadingSpinner from '../../components/LoadingSpinner';
import StatusBadge from '../../components/StatusBadge';
import { Colors } from '../../constants/colors';
import { formatDate, formatDateTime } from '../../utils/helpers';

export default function PedidoDetailScreen({ route }) {
  const { id } = route.params;
  const [pedido, setPedido] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => { loadPedido(); }, [id]);

  const loadPedido = async () => {
    try {
      const res = await pedidosApi.get(id);
      setPedido(res.data?.data || res.data);
    } catch (e) {} finally { setLoading(false); }
  };

  if (loading) return <LoadingSpinner fullScreen message="Cargando pedido..." />;
  if (!pedido) return <View style={styles.container}><Text style={styles.errorText}>No se encontró el pedido</Text></View>;

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>Pedido #{pedido.pedidoid || pedido.id}</Text>
        <StatusBadge status={pedido.estado || 'pendiente'} />
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Información del Pedido</Text>
        <InfoRow icon="calendar-outline" label="Fecha" value={formatDateTime(pedido.fechapedido || pedido.fecharegistro)} />
        <InfoRow icon="person-outline" label="Cliente" value={pedido.cliente?.nombre || pedido.cliente_comercial?.nombre || '-'} />
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
  header: { backgroundColor: '#AD1457', padding: 24, paddingBottom: 20 },
  title: { fontSize: 24, fontWeight: 'bold', color: '#FFF', marginBottom: 8 },
  errorText: { padding: 24, fontSize: 16, color: Colors.textSecondary },
  section: { backgroundColor: Colors.surface, margin: 12, borderRadius: 12, padding: 16 },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.text, marginBottom: 12 },
  infoRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: Colors.border },
  infoContent: { marginLeft: 12, flex: 1 },
  infoLabel: { fontSize: 12, color: Colors.textSecondary },
  infoValue: { fontSize: 15, color: Colors.text, fontWeight: '500' },
  subItem: { paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: Colors.border },
  subItemTitle: { fontSize: 14, fontWeight: '500', color: Colors.text },
  subItemDate: { fontSize: 12, color: Colors.textSecondary, marginTop: 2 },
});
