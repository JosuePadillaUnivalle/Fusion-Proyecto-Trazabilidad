import React, { useState, useCallback } from 'react';
import {
  View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Alert,
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../context/AuthContext';
import { isOperadorPlanta } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockRecepcionesPlanta } from '../../data/mockWorkersData';
import { confirmMockRecepcionPlanta } from '../../data/mockRoleActions';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDateTime } from '../../utils/helpers';

export default function RecepcionPlantaScreen() {
  const { user } = useAuth();
  const esOperador = isOperadorPlanta(user);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esOperador) {
        setData(getMockRecepcionesPlanta());
      } else {
        setData([]);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esOperador) setData(getMockRecepcionesPlanta());
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { loadData(); }, []));

  const pendientes = data.filter(r => r.estado === 'pendiente');
  const confirmadas = data.filter(r => r.estado === 'confirmada');

  const handleConfirm = (item) => {
    Alert.alert(
      'Confirmar recepción',
      `¿Confirmar ingreso de ${item.cantidad} ${item.unidad} de ${item.producto}?`,
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Confirmar',
          onPress: () => {
            if (USE_MOCK_DATA) {
              confirmMockRecepcionPlanta(item.recepcionid);
              loadData();
            }
          },
        },
      ],
    );
  };

  const renderItem = ({ item }) => (
    <View style={styles.card}>
      <View style={styles.cardHeader}>
        <View style={styles.iconBox}>
          <Ionicons name="download-outline" size={20} color={Colors.primary} />
        </View>
        <View style={{ flex: 1 }}>
          <Text style={styles.cardTitle}>{item.codigo}</Text>
          <Text style={styles.cardSubtitle}>{item.producto} · {item.lote?.nombre}</Text>
        </View>
        <StatusBadge
          status={item.estado === 'confirmada' ? 'completado' : 'pendiente'}
          label={item.estado === 'confirmada' ? 'Confirmada' : 'Pendiente'}
        />
      </View>
      <View style={styles.cardBody}>
        <Info icon="cube-outline" text={`${item.cantidad} ${item.unidad}`} />
        <Info icon="location-outline" text={item.origen} />
        <Info icon="person-outline" text={item.transportista} />
        <Info icon="calendar-outline" text={formatDateTime(item.fecharecepcion)} />
      </View>
      {item.estado === 'pendiente' && (
        <TouchableOpacity style={styles.confirmBtn} onPress={() => handleConfirm(item)}>
          <Ionicons name="checkmark-circle-outline" size={18} color={Colors.success} />
          <Text style={styles.confirmText}>Confirmar recepción</Text>
        </TouchableOpacity>
      )}
    </View>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando recepciones..." />;

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <Text style={styles.pageTitle}>Recepción en planta</Text>
        <Text style={styles.pageSubtitle}>{data.length} registros</Text>
      </View>

      <View style={styles.summary}>
        <SummaryBox value={String(pendientes.length)} label="Pendientes" icon="time-outline" color={Colors.warning} />
        <SummaryBox value={String(confirmadas.length)} label="Confirmadas" icon="checkmark-circle-outline" color={Colors.success} />
      </View>

      <FlatList
        data={data}
        keyExtractor={(item) => String(item.recepcionid)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />
        }
        ListEmptyComponent={<EmptyState icon="download-outline" message="No hay recepciones pendientes" />}
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
  iconBox: {
    width: 40, height: 40, borderRadius: 10, backgroundColor: Colors.primaryLight,
    justifyContent: 'center', alignItems: 'center',
  },
  cardTitle: { fontSize: 15, fontWeight: '600', color: Colors.text },
  cardSubtitle: { fontSize: 12, color: Colors.textMuted, marginTop: 2 },
  cardBody: { gap: 6, paddingLeft: 52 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
  confirmBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8,
    marginTop: 12, padding: 12, borderRadius: 10,
    backgroundColor: Colors.successLight, borderWidth: 1, borderColor: Colors.success + '40',
  },
  confirmText: { color: Colors.success, fontWeight: '600', fontSize: 14 },
});
