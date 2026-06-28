import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Alert } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { lotesApi } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { canManageLotes, isAgricultor } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockLotes } from '../../data/mockAgricultorData';
import { unwrapApiList } from '../../utils/apiHelpers';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { truncate } from '../../utils/helpers';

export default function LotesScreen({ navigation }) {
  const { user } = useAuth();
  const esAgricultor = isAgricultor(user);
  const puedeGestionar = canManageLotes(user);
  const [lotes, setLotes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA) {
        setLotes(getMockLotes(user?.usuarioid));
      } else {
        const res = await lotesApi.list();
        setLotes(unwrapApiList(res));
      }
    } catch (e) {
      if (USE_MOCK_DATA) {
        setLotes(getMockLotes(user?.usuarioid));
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

  const handleDelete = (item) => {
    Alert.alert('Eliminar Lote', `¿Estás seguro de eliminar "${item.nombre}"?`, [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Eliminar', style: 'destructive', onPress: async () => {
        try {
          await lotesApi.delete(item.loteid);
          loadData();
        } catch (e) {
          Alert.alert('Error', 'No se pudo eliminar');
        }
      }},
    ]);
  };

  const renderItem = ({ item }) => (
    <TouchableOpacity
      style={styles.card}
      onPress={() => navigation.navigate('LoteDetail', { id: item.loteid })}
      activeOpacity={0.8}
    >
      <View style={styles.cardHeader}>
        <View style={styles.cardTitleSection}>
          <View style={[styles.iconBox, { backgroundColor: Colors.primaryLight }]}>
            <Ionicons name="map-outline" size={18} color={Colors.primary} />
          </View>
          <View style={{ flex: 1 }}>
            <Text style={styles.cardTitle}>{item.nombre || `Lote #${item.loteid}`}</Text>
            <Text style={styles.cardSubtitle}>{item.ubicacion ? truncate(item.ubicacion, 45) : 'Sin ubicación'}</Text>
          </View>
        </View>
        <StatusBadge
          status={item.estadoTipo?.slug || item.estadoTipo?.nombre || 'planificado'}
          label={item.estadoTipo?.nombre || 'Planificado'}
        />
      </View>

      <View style={styles.cardBody}>
        {!esAgricultor && (
          <Info icon="person-outline" text={item.usuario ? `${item.usuario.nombre} ${item.usuario.apellido}` : 'Sin asignar'} />
        )}
        <Info icon="leaf-outline" text={item.cultivo_etiqueta || item.cultivo?.nombre || 'Sin cultivo'} />
        <Info icon="expand-outline" text={item.superficie ? `${item.superficie} ha` : 'Sin superficie'} />
        {item.codigo_trazabilidad && (
          <Info icon="barcode-outline" text={item.codigo_trazabilidad} />
        )}
        {esAgricultor && item.actividades && (
          <Info
            icon="calendar-outline"
            text={`${item.actividades.filter(a => !a.fechafin).length} actividades pendientes`}
          />
        )}
      </View>

      {puedeGestionar && (
        <View style={styles.cardFooter}>
          <TouchableOpacity
            style={styles.actionBtn}
            onPress={() => navigation.navigate('LoteForm', { id: item.loteid })}
          >
            <Ionicons name="create-outline" size={14} color={Colors.textSecondary} />
            <Text style={styles.actionText}>Editar</Text>
          </TouchableOpacity>
          <View style={styles.divider} />
          <TouchableOpacity style={styles.actionBtn} onPress={() => handleDelete(item)}>
            <Ionicons name="trash-outline" size={14} color={Colors.error} />
            <Text style={[styles.actionText, { color: Colors.error }]}>Eliminar</Text>
          </TouchableOpacity>
        </View>
      )}
    </TouchableOpacity>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando lotes..." />;

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <Text style={styles.pageTitle}>{esAgricultor ? 'Mis lotes asignados' : 'Gestión de lotes'}</Text>
        <Text style={styles.pageSubtitle}>{lotes.length} registros</Text>
      </View>

      <View style={styles.statsRow}>
        <MiniStat value={String(lotes.length)} label="Total lotes" icon="layers-outline" color="#475569" />
        <MiniStat
          value={String(lotes.filter(l => (l.estadoTipo?.nombre || '').toLowerCase().includes('crecimiento')).length)}
          label="En crecimiento"
          icon="trending-up-outline"
          color={Colors.primary}
        />
        <MiniStat
          value={String(lotes.reduce((sum, l) => sum + (parseFloat(l.superficie) || 0), 0).toFixed(1))}
          label="Hectáreas"
          icon="expand-outline"
          color={Colors.primary}
        />
        <MiniStat
          value={String(lotes.filter(l => l.latitud && l.longitud).length)}
          label="Con GPS"
          icon="location-outline"
          color="#475569"
        />
      </View>

      <FlatList
        data={lotes}
        keyExtractor={(item) => String(item.loteid)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
        ListEmptyComponent={
          <EmptyState
            icon="map-outline"
            message={esAgricultor ? 'No tienes lotes asignados' : 'No hay lotes registrados'}
          />
        }
      />
      {puedeGestionar && (
        <TouchableOpacity style={styles.fab} onPress={() => navigation.navigate('LoteForm')}>
          <Ionicons name="add" size={28} color="#FFF" />
        </TouchableOpacity>
      )}
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

function MiniStat({ value, label, icon, color }) {
  return (
    <View style={styles.miniStat}>
      <View style={[styles.miniStatIcon, { backgroundColor: color + '15' }]}>
        <Ionicons name={icon} size={16} color={color} />
      </View>
      <Text style={[styles.miniStatValue, { color }]}>{value}</Text>
      <Text style={styles.miniStatLabel}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  pageHeader: { padding: 16, paddingBottom: 8 },
  pageTitle: { fontSize: 22, fontWeight: '700', color: Colors.text },
  pageSubtitle: { fontSize: 13, color: Colors.textMuted, marginTop: 2 },
  statsRow: { flexDirection: 'row', paddingHorizontal: 16, gap: 10, marginBottom: 12 },
  miniStat: {
    flex: 1, backgroundColor: Colors.surface, borderRadius: 14, padding: 12,
    borderWidth: 1, borderColor: Colors.border, alignItems: 'center',
  },
  miniStatIcon: { width: 34, height: 34, borderRadius: 10, justifyContent: 'center', alignItems: 'center', marginBottom: 6 },
  miniStatValue: { fontSize: 18, fontWeight: '700' },
  miniStatLabel: { fontSize: 10, color: Colors.textMuted, marginTop: 2, textAlign: 'center' },
  list: { padding: 16, paddingBottom: 80 },
  card: {
    backgroundColor: Colors.surface,
    borderRadius: 16,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: Colors.border,
  },
  cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 14 },
  cardTitleSection: { flexDirection: 'row', gap: 12, flex: 1, paddingRight: 8 },
  iconBox: { width: 40, height: 40, borderRadius: 10, justifyContent: 'center', alignItems: 'center' },
  cardTitle: { fontSize: 15, fontWeight: '700', color: Colors.text },
  cardSubtitle: { fontSize: 12, color: Colors.textMuted, marginTop: 2 },
  cardBody: { gap: 8, marginBottom: 14 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
  cardFooter: { flexDirection: 'row', borderTopWidth: 1, borderTopColor: Colors.border, paddingTop: 12 },
  actionBtn: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6 },
  actionText: { fontSize: 13, color: Colors.textSecondary, fontWeight: '500' },
  divider: { width: 1, backgroundColor: Colors.border },
  fab: {
    position: 'absolute', bottom: 24, right: 24, width: 56, height: 56, borderRadius: 28,
    backgroundColor: Colors.primary, justifyContent: 'center', alignItems: 'center', elevation: 4,
  },
});
