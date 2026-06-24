import React, { useState, useCallback } from 'react';
import {
  View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl,
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { actividadesApi } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockActividades, getMockLotes } from '../../data/mockAgricultorData';
import { actividadRequiereComprobante } from '../../data/mockRoleActions';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import StatusBadge from '../../components/StatusBadge';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function MisActividadesScreen({ navigation }) {
  const { user } = useAuth();
  const [actividades, setActividades] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [filtro, setFiltro] = useState('todas');

  const lotesResponsables = USE_MOCK_DATA ? getMockLotes(user?.usuarioid) : [];

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA) {
        setActividades(getMockActividades(user?.usuarioid));
      } else {
        const res = await actividadesApi.list();
        const all = res.data?.data || res.data || [];
        setActividades(all.filter(a => a.usuarioid === user?.usuarioid));
      }
    } catch (e) {
      if (USE_MOCK_DATA) {
        setActividades(getMockActividades(user?.usuarioid));
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(
    useCallback(() => { loadData(); }, [])
  );

  const onRefresh = () => { setRefreshing(true); loadData(); };

  const pendientes = actividades.filter(a => !a.fechafin);
  const completadas = actividades.filter(a => a.fechafin);
  const conComprobante = pendientes.filter(a => actividadRequiereComprobante(a));

  const dataFiltrada = filtro === 'pendientes'
    ? pendientes
    : filtro === 'completadas'
      ? completadas
      : actividades;

  const renderItem = ({ item }) => {
    const requiereComprobante = actividadRequiereComprobante(item) && !item.fechafin;
    const esSiembra = (item.tipo_actividad?.nombre || '').toLowerCase().includes('siembra');

    return (
      <TouchableOpacity
        style={styles.card}
        onPress={() => {
          if (item.fechafin) {
            navigation.navigate('ActividadDetail', { actividadId: item.actividadid, lote: item.lote });
          } else {
            navigation.navigate('ActividadComplete', { actividadId: item.actividadid, lote: item.lote });
          }
        }}
        activeOpacity={0.8}
      >
        <View style={styles.cardHeader}>
          <View style={styles.iconBox}>
            <Ionicons
              name={item.fechafin ? 'checkmark-circle-outline' : esSiembra ? 'leaf-outline' : 'time-outline'}
              size={20}
              color={item.fechafin ? Colors.success : Colors.warning}
            />
          </View>
          <View style={{ flex: 1 }}>
            <Text style={styles.cardTitle}>{item.descripcion || item.tipo_actividad?.nombre}</Text>
            <TouchableOpacity
              onPress={(e) => {
                e.stopPropagation?.();
                if (item.lote?.loteid) navigation.navigate('LoteDetail', { id: item.lote.loteid });
              }}
            >
              <Text style={styles.loteLink}>{item.lote?.nombre || 'Sin lote'} · Ver lote</Text>
            </TouchableOpacity>
          </View>
          <StatusBadge
            status={item.fechafin ? 'completado' : 'pendiente'}
            label={item.fechafin ? 'Completada' : 'Pendiente'}
          />
        </View>
        <View style={styles.cardBody}>
          <Info icon="pricetag-outline" text={item.tipo_actividad?.nombre || 'Actividad'} />
          <Info icon="calendar-outline" text={`Inicio: ${formatDate(item.fechainicio)}`} />
          {item.fechafin && <Info icon="checkmark-done-outline" text={`Fin: ${formatDate(item.fechafin)}`} />}
          {requiereComprobante && (
            <View style={styles.comprobanteRow}>
              <Ionicons name="camera-outline" size={14} color={Colors.primary} />
              <Text style={styles.comprobanteText}>
                {esSiembra ? 'Comprobante de siembra requerido' : 'Comprobante fotográfico requerido'}
              </Text>
            </View>
          )}
        </View>
      </TouchableOpacity>
    );
  };

  if (loading) return <LoadingSpinner fullScreen message="Cargando actividades..." />;

  return (
    <View style={styles.container}>
      <View style={styles.lotesBar}>
        <View style={styles.lotesInfo}>
          <Ionicons name="map-outline" size={18} color={Colors.primary} />
          <Text style={styles.lotesText}>{lotesResponsables.length} lotes bajo su responsabilidad</Text>
        </View>
        <TouchableOpacity style={styles.lotesBtn} onPress={() => navigation.navigate('Lotes')}>
          <Text style={styles.lotesBtnText}>Ver lotes</Text>
          <Ionicons name="chevron-forward" size={16} color={Colors.primary} />
        </TouchableOpacity>
      </View>

      <View style={styles.summary}>
        <SummaryBox value={String(pendientes.length)} label="Pendientes" icon="time-outline" color={Colors.warning} />
        <SummaryBox value={String(completadas.length)} label="Completadas" icon="checkmark-circle-outline" color={Colors.success} />
        <SummaryBox value={String(conComprobante.length)} label="Con comprobante" icon="camera-outline" color={Colors.primary} />
      </View>

      <View style={styles.filterRow}>
        {[
          { key: 'todas', label: 'Todas' },
          { key: 'pendientes', label: 'Pendientes' },
          { key: 'completadas', label: 'Completadas' },
        ].map((f) => (
          <TouchableOpacity
            key={f.key}
            style={[styles.filterChip, filtro === f.key && styles.filterChipActive]}
            onPress={() => setFiltro(f.key)}
          >
            <Text style={[styles.filterText, filtro === f.key && styles.filterTextActive]}>{f.label}</Text>
          </TouchableOpacity>
        ))}
      </View>

      <FlatList
        data={dataFiltrada}
        keyExtractor={(item) => String(item.actividadid)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="calendar-outline" message="No tienes actividades asignadas" />}
      />
    </View>
  );
}

function SummaryBox({ value, label, icon, color }) {
  return (
    <View style={styles.summaryBox}>
      <View style={[styles.summaryIcon, { backgroundColor: color + '15' }]}>
        <Ionicons name={icon} size={18} color={color} />
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
  lotesBar: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    marginHorizontal: 16, marginTop: 12, padding: 12, borderRadius: 12,
    backgroundColor: Colors.surface, borderWidth: 1, borderColor: Colors.border,
  },
  lotesInfo: { flexDirection: 'row', alignItems: 'center', gap: 8, flex: 1 },
  lotesText: { fontSize: 13, color: Colors.text, fontWeight: '500' },
  lotesBtn: { flexDirection: 'row', alignItems: 'center', gap: 2 },
  lotesBtnText: { fontSize: 13, color: Colors.primary, fontWeight: '600' },
  summary: { flexDirection: 'row', padding: 16, gap: 8 },
  summaryBox: {
    flex: 1, backgroundColor: Colors.surface, borderRadius: 12, padding: 12,
    borderWidth: 1, borderColor: Colors.border, alignItems: 'center',
  },
  summaryIcon: { width: 36, height: 36, borderRadius: 8, justifyContent: 'center', alignItems: 'center', marginBottom: 6 },
  summaryValue: { fontSize: 20, fontWeight: '700' },
  summaryLabel: { fontSize: 11, color: Colors.textMuted, marginTop: 2, textAlign: 'center' },
  filterRow: { flexDirection: 'row', paddingHorizontal: 16, gap: 8, marginBottom: 4 },
  filterChip: {
    paddingHorizontal: 14, paddingVertical: 8, borderRadius: 10,
    backgroundColor: Colors.surface, borderWidth: 1, borderColor: Colors.border,
  },
  filterChipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
  filterText: { fontSize: 13, color: Colors.textSecondary, fontWeight: '500' },
  filterTextActive: { color: '#FFF' },
  list: { padding: 16, paddingBottom: 24 },
  card: {
    backgroundColor: Colors.surface, borderRadius: 16, padding: 16,
    marginBottom: 12, borderWidth: 1, borderColor: Colors.border,
  },
  cardHeader: { flexDirection: 'row', alignItems: 'center', gap: 12, marginBottom: 10 },
  iconBox: { width: 40, height: 40, borderRadius: 10, backgroundColor: Colors.divider, justifyContent: 'center', alignItems: 'center' },
  cardTitle: { fontSize: 15, fontWeight: '600', color: Colors.text },
  loteLink: { fontSize: 12, color: Colors.primary, marginTop: 2, fontWeight: '500' },
  cardBody: { gap: 6, paddingLeft: 52 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary, flex: 1 },
  comprobanteRow: {
    flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 4,
    padding: 8, borderRadius: 8, backgroundColor: Colors.primaryLight,
    borderWidth: 1, borderColor: Colors.border,
  },
  comprobanteText: { fontSize: 12, color: Colors.primary, fontWeight: '600', flex: 1 },
});
