import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Alert } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import apiClient from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { isOperadorPlanta } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockTareasPlanta } from '../../data/mockWorkersData';
import { completeMockTareaPlanta } from '../../data/mockRoleActions';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function TareasPlantaScreen() {
  const { user } = useAuth();
  const esOperador = isOperadorPlanta(user);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esOperador) {
        setData(getMockTareasPlanta(user?.usuarioid));
      } else {
        const res = await apiClient.get('/mis-tareas-planta');
        setData(res.data?.data || res.data || []);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esOperador) setData(getMockTareasPlanta(user?.usuarioid));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { loadData(); }, []));

  const handleComplete = (item) => {
    Alert.alert('Completar tarea', `¿Marcar "${item.descripcion}" como completada?`, [
      { text: 'Cancelar', style: 'cancel' },
      {
        text: 'Completar',
        onPress: () => {
          if (USE_MOCK_DATA) {
            completeMockTareaPlanta(item.id);
            loadData();
          }
        },
      },
    ]);
  };

  const pendientes = data.filter(t => !t.completada);
  const completadas = data.filter(t => t.completada);

  const renderItem = ({ item }) => (
    <TouchableOpacity
      style={styles.card}
      activeOpacity={0.8}
      onPress={() => !item.completada && USE_MOCK_DATA && esOperador && handleComplete(item)}
    >
      <View style={styles.cardHeader}>
        <View style={styles.iconBox}>
          <Ionicons
            name={item.completada ? 'checkmark-circle-outline' : 'time-outline'}
            size={20}
            color={item.completada ? Colors.success : Colors.warning}
          />
        </View>
        <View style={{ flex: 1 }}>
          <Text style={styles.cardTitle}>{item.descripcion || item.etapa?.nombre}</Text>
          <Text style={styles.cardSubtitle}>{item.procesamiento?.nombre || 'Sin línea'}</Text>
        </View>
        <StatusBadge status={item.completada ? 'completado' : 'pendiente'} label={item.completada ? 'Completada' : 'Pendiente'} />
      </View>
      <View style={styles.cardBody}>
        <Info icon="construct-outline" text={item.etapa?.nombre || 'Etapa'} />
        <Info icon="calendar-outline" text={formatDate(item.fechaasignacion || item.fecharegistro)} />
        {item.prioridad && <Info icon="flag-outline" text={`Prioridad: ${item.prioridad.nombre}`} />}
      </View>
    </TouchableOpacity>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando tareas..." />;

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <Text style={styles.pageTitle}>{esOperador ? 'Mis tareas de planta' : 'Tareas de planta'}</Text>
        <Text style={styles.pageSubtitle}>{data.length} registros</Text>
      </View>

      <View style={styles.summary}>
        <SummaryBox value={String(pendientes.length)} label="Pendientes" icon="time-outline" color={Colors.warning} />
        <SummaryBox value={String(completadas.length)} label="Completadas" icon="checkmark-circle-outline" color={Colors.success} />
      </View>

      <FlatList
        data={data}
        keyExtractor={(item) => String(item.id || item.asignacionid)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="list-circle-outline" message="No tienes tareas asignadas" />}
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
  cardHeader: { flexDirection: 'row', alignItems: 'center', gap: 12, marginBottom: 10 },
  iconBox: { width: 40, height: 40, borderRadius: 10, backgroundColor: Colors.divider, justifyContent: 'center', alignItems: 'center' },
  cardTitle: { fontSize: 15, fontWeight: '600', color: Colors.text },
  cardSubtitle: { fontSize: 12, color: Colors.textMuted, marginTop: 2 },
  cardBody: { gap: 6, paddingLeft: 52 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
});
