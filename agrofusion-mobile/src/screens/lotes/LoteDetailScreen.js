import React, { useState, useCallback } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import {
  View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert, Image, RefreshControl,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { MapView, Marker, Circle } from '../../components/MapComponent';
import { lotesApi } from '../../api/client';
import LoadingSpinner from '../../components/LoadingSpinner';
import StatusBadge from '../../components/StatusBadge';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';
import { useAuth } from '../../context/AuthContext';
import { canManageLotes, isAgricultor } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockLote, getMockEvidenciaUrl } from '../../data/mockAgricultorData';

const FASES = [
  { key: 'preparacion', label: 'Preparación', icon: 'construct-outline' },
  { key: 'siembra', label: 'Siembra', icon: 'leaf-outline' },
  { key: 'crecimiento', label: 'En crecimiento', icon: 'trending-up-outline' },
  { key: 'cosecha', label: 'Cosecha', icon: 'basket-outline' },
  { key: 'certificacion', label: 'Certificación', icon: 'checkmark-circle-outline' },
  { key: 'envio', label: 'Envío almacén', icon: 'cube-outline' },
];

const TIPOS_REQUERIDOS_CRECIMIENTO = ['Riego', 'Control de plagas', 'Fertilización'];

export default function LoteDetailScreen({ route, navigation }) {
  const { id } = route.params;
  const { user } = useAuth();
  const esAgricultor = isAgricultor(user);
  const puedeGestionar = canManageLotes(user);
  const [lote, setLote] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  useFocusEffect(
    useCallback(() => { loadLote(); }, [id])
  );

  const loadLote = async () => {
    try {
      if (esAgricultor && USE_MOCK_DATA) {
        setLote(getMockLote(id));
      } else {
        const res = await lotesApi.get(id);
        setLote(res.data?.data || res.data);
      }
    } catch (e) {
      if (esAgricultor && USE_MOCK_DATA) {
        setLote(getMockLote(id));
      } else {
        Alert.alert('Error', 'No se pudo cargar el lote');
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = useCallback(() => { setRefreshing(true); loadLote(); }, []);

  const getFaseActual = () => {
    if (!lote) return 'preparacion';
    const estadoId = lote.estadolotetipoid ?? lote.estadoTipo?.estadolotetipoid;
    const acts = lote.actividades || [];
    const completadas = acts.filter(a => a.fechafin);
    const tiposCompletados = completadas.map(a => (a.tipo_actividad?.nombre || a.tipoNombre || ''));
    const hitosCrecimiento = TIPOS_REQUERIDOS_CRECIMIENTO.filter(t => tiposCompletados.includes(t));
    const siembraPendiente = acts.find(a => (a.tipo_actividad?.nombre || '').toLowerCase().includes('siembra') && !a.fechafin);
    const siembraCompletada = acts.find(a => (a.tipo_actividad?.nombre || '').toLowerCase().includes('siembra') && a.fechafin);

    switch (estadoId) {
      case 5:
      case 7:
      case 6:
        return 'envio';
      case 4:
        return 'cosecha';
      case 3:
        if (hitosCrecimiento.length >= 3) return 'cosecha';
        return 'crecimiento';
      case 2:
        return siembraPendiente ? 'siembra' : 'crecimiento';
      case 1:
      default:
        if (siembraPendiente) return 'siembra';
        if (siembraCompletada || hitosCrecimiento.length > 0) return 'crecimiento';
        return 'preparacion';
    }
  };

  const faseActual = getFaseActual();
  const faseIndex = FASES.findIndex(f => f.key === faseActual);

  const evidenciaUrl = (act) => {
    if (USE_MOCK_DATA) return getMockEvidenciaUrl(act);
    return act.evidencia_foto_url || null;
  };

  const verActividad = (act) => {
    const actId = act.actividadid || act.id;
    if (act.fechafin) {
      navigation.navigate('ActividadDetail', { actividadId: actId, lote });
    } else {
      navigation.navigate('ActividadComplete', { actividadId: actId, lote });
    }
  };

  const handleDelete = () => {
    Alert.alert('Eliminar Lote', '¿Estás seguro de eliminar este lote?', [
      { text: 'Cancelar', style: 'cancel' },
      {
        text: 'Eliminar', style: 'destructive', onPress: async () => {
          try { await lotesApi.delete(id); navigation.goBack(); }
          catch (e) { Alert.alert('Error', 'No se pudo eliminar'); }
        }
      },
    ]);
  };

  if (loading) return <LoadingSpinner fullScreen message="Cargando detalle..." />;
  if (!lote) return <View style={styles.container}><Text style={styles.emptyText}>No se encontró el lote</Text></View>;

  const hasCoords = lote.latitud && lote.longitud;
  const coords = hasCoords
    ? { latitude: parseFloat(lote.latitud), longitude: parseFloat(lote.longitud), latitudeDelta: 0.01, longitudeDelta: 0.01 }
    : null;

  return (
    <ScrollView
      style={styles.container}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
    >
      <View style={styles.header}>
        <Text style={styles.eyebrow}>{esAgricultor ? 'Mi lote asignado' : 'Lote agrícola'}</Text>
        <Text style={styles.title}>{lote.nombre || `Lote #${lote.loteid}`}</Text>
        <View style={styles.headerMeta}>
          {lote.ubicacion && <Text style={styles.metaText}>{lote.ubicacion}</Text>}
        </View>
        <View style={styles.chipsRow}>
          <StatusBadge status={faseActual} label={FASES[faseIndex]?.label || 'Preparación'} />
          <View style={styles.cultivoChip}>
            <Ionicons name="leaf" size={12} color={Colors.primary} />
            <Text style={styles.cultivoChipText}>
              {lote.cultivo_etiqueta || lote.cultivo?.nombre || 'Sin cultivo'}
            </Text>
          </View>
        </View>
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Estado del lote</Text>
        <View style={styles.fasesRow}>
          {FASES.map((f, idx) => {
            const activo = idx <= faseIndex;
            const esActual = idx === faseIndex;
            return (
              <View key={f.key} style={styles.faseItem}>
                <View style={[styles.faseIcon, activo && styles.faseIconActivo, esActual && styles.faseIconActual]}>
                  <Ionicons name={f.icon} size={14} color={activo ? '#FFF' : Colors.textMuted} />
                </View>
                <Text style={[styles.faseLabel, activo && styles.faseLabelActivo, esActual && styles.faseLabelActual]} numberOfLines={2}>
                  {f.label}
                </Text>
                {idx < FASES.length - 1 && (
                  <View style={[styles.faseLine, idx < faseIndex && styles.faseLineActivo]} />
                )}
              </View>
            );
          })}
        </View>
        <View style={styles.estadoActualBox}>
          <Ionicons name="information-circle-outline" size={18} color={Colors.primary} />
          <Text style={styles.estadoActualText}>Fase actual: {FASES[faseIndex]?.label}</Text>
        </View>
      </View>

      <View style={styles.statsRow}>
        <StatCard icon="expand-outline" label="Hectáreas" value={lote.superficie ? `${lote.superficie}` : '-'} color={Colors.primary} />
        <StatCard icon="calendar-outline" label="Siembra" value={lote.fechasiembra ? formatDate(lote.fechasiembra) : 'N/A'} color={Colors.info} />
        <StatCard icon="flask-outline" label="Actividades" value={String(lote.actividades?.length || 0)} color={Colors.purple} />
        <StatCard icon="basket-outline" label="Cosechas" value={String(lote.producciones?.length || 0)} color={Colors.warning} />
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Datos del lote</Text>
        <InfoRow icon="hashtag-outline" label="ID" value={`#${lote.loteid}`} />
        <InfoRow icon="expand-outline" label="Superficie" value={lote.superficie ? `${lote.superficie} ha` : '-'} />
        <InfoRow icon="calendar-outline" label="Fecha de siembra" value={lote.fechasiembra ? formatDate(lote.fechasiembra) : 'Pendiente'} />
        {lote.codigo_trazabilidad && <InfoRow icon="barcode-outline" label="Código trazabilidad" value={lote.codigo_trazabilidad} />}
        {lote.ubicacion && <InfoRow icon="location-outline" label="Ubicación" value={lote.ubicacion} />}
      </View>

      {coords && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Ubicación GPS</Text>
          <View style={styles.mapContainer}>
            <MapView style={styles.map} initialRegion={coords} showsUserLocation={false}>
              <Marker coordinate={coords}>
                <Ionicons name="location" size={32} color={Colors.primary} />
              </Marker>
              {lote.superficie ? (
                <Circle
                  center={coords}
                  radius={Math.sqrt(parseFloat(lote.superficie) * 10000 / Math.PI)}
                  fillColor="rgba(44, 85, 48, 0.2)"
                  strokeColor={Colors.primary}
                  strokeWidth={2}
                />
              ) : null}
            </MapView>
          </View>
        </View>
      )}

      {lote.actividades && lote.actividades.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Historial de actividades ({lote.actividades.length})</Text>
          {lote.actividades.map((act, i) => {
            const evUrl = evidenciaUrl(act);
            return (
              <TouchableOpacity key={i} style={styles.subItem} onPress={() => verActividad(act)}>
                <View style={styles.subItemHeader}>
                  <Text style={styles.subItemTitle}>{act.descripcion || act.tipo_actividad?.nombre}</Text>
                  <Ionicons
                    name={act.fechafin ? 'checkmark-circle' : 'time-outline'}
                    size={18}
                    color={act.fechafin ? Colors.success : Colors.warning}
                  />
                </View>
                <Text style={styles.subItemDate}>
                  {formatDate(act.fechainicio || act.fecharegistro)} · {act.fechafin ? 'Completada' : 'Pendiente'}
                </Text>
                {evUrl && (
                  <Image source={{ uri: evUrl }} style={styles.evidenciaImage} resizeMode="cover" />
                )}
                {!act.fechafin && esAgricultor && (
                  <View style={styles.pendienteHint}>
                    <Ionicons name="camera-outline" size={14} color={Colors.primary} />
                    <Text style={styles.pendienteHintText}>Toca para completar con foto</Text>
                  </View>
                )}
              </TouchableOpacity>
            );
          })}
        </View>
      )}

      {lote.producciones && lote.producciones.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Cosechas ({lote.producciones.length})</Text>
          {lote.producciones.map((prod, i) => (
            <View key={i} style={styles.subItem}>
              <Text style={styles.subItemTitle}>{prod.cantidad} {prod.unidad || 'unidades'}</Text>
              <Text style={styles.subItemDate}>{formatDate(prod.fechaproduccion || prod.fecharegistro)}</Text>
            </View>
          ))}
        </View>
      )}

      {puedeGestionar && (
        <View style={styles.actions}>
          <TouchableOpacity style={styles.editButton} onPress={() => navigation.navigate('LoteForm', { id })}>
            <Ionicons name="create-outline" size={20} color="#FFF" />
            <Text style={styles.buttonText}>Editar</Text>
          </TouchableOpacity>
          <TouchableOpacity style={styles.deleteButton} onPress={handleDelete}>
            <Ionicons name="trash-outline" size={20} color="#FFF" />
            <Text style={styles.buttonText}>Eliminar</Text>
          </TouchableOpacity>
        </View>
      )}
    </ScrollView>
  );
}

const StatCard = ({ icon, label, value, color }) => (
  <View style={styles.statCard}>
    <View style={[styles.statIcon, { backgroundColor: color + '15' }]}>
      <Ionicons name={icon} size={20} color={color} />
    </View>
    <Text style={styles.statValue}>{value}</Text>
    <Text style={styles.statLabel}>{label}</Text>
  </View>
);

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
  emptyText: { padding: 24, color: Colors.textSecondary },
  header: {
    backgroundColor: Colors.surface, padding: 24, paddingBottom: 20,
    borderBottomWidth: 1, borderBottomColor: Colors.border,
  },
  eyebrow: { fontSize: 12, color: Colors.textMuted, textTransform: 'uppercase', letterSpacing: 1 },
  title: { fontSize: 24, fontWeight: '700', color: Colors.text, marginTop: 4 },
  headerMeta: { flexDirection: 'row', flexWrap: 'wrap', marginTop: 8 },
  metaText: { fontSize: 13, color: Colors.textSecondary },
  chipsRow: { flexDirection: 'row', gap: 8, marginTop: 12, alignItems: 'center', flexWrap: 'wrap' },
  cultivoChip: {
    flexDirection: 'row', alignItems: 'center', gap: 4,
    backgroundColor: Colors.primaryLight, paddingHorizontal: 10, paddingVertical: 4,
    borderRadius: 8, borderWidth: 1, borderColor: Colors.border,
  },
  cultivoChipText: { color: Colors.primary, fontSize: 12, fontWeight: '600' },
  statsRow: { flexDirection: 'row', padding: 12, gap: 8 },
  statCard: {
    flex: 1, backgroundColor: Colors.surface, borderRadius: 12, padding: 12,
    alignItems: 'center', borderWidth: 1, borderColor: Colors.border,
  },
  statIcon: { width: 40, height: 40, borderRadius: 10, justifyContent: 'center', alignItems: 'center', marginBottom: 8 },
  statValue: { fontSize: 18, fontWeight: '700', color: Colors.text },
  statLabel: { fontSize: 11, color: Colors.textMuted, marginTop: 2 },
  section: {
    backgroundColor: Colors.surface, margin: 12, borderRadius: 16, padding: 16,
    borderWidth: 1, borderColor: Colors.border,
  },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.text, marginBottom: 12 },
  infoRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: Colors.border },
  infoContent: { marginLeft: 12, flex: 1 },
  infoLabel: { fontSize: 12, color: Colors.textSecondary },
  infoValue: { fontSize: 15, color: Colors.text, fontWeight: '500' },
  mapContainer: { borderRadius: 12, overflow: 'hidden', borderWidth: 1, borderColor: Colors.border },
  map: { width: '100%', height: 250 },
  subItem: { paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: Colors.border },
  subItemHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  subItemTitle: { fontSize: 14, fontWeight: '600', color: Colors.text, flex: 1, paddingRight: 8 },
  subItemDate: { fontSize: 12, color: Colors.textSecondary, marginTop: 2 },
  evidenciaImage: { width: '100%', height: 140, borderRadius: 10, marginTop: 8 },
  pendienteHint: { flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 8 },
  pendienteHintText: { fontSize: 12, color: Colors.primary, fontWeight: '500' },
  actions: { flexDirection: 'row', gap: 12, padding: 16, paddingBottom: 32 },
  editButton: {
    flex: 1, flexDirection: 'row', backgroundColor: Colors.info, padding: 14,
    borderRadius: 10, justifyContent: 'center', alignItems: 'center', gap: 8,
  },
  deleteButton: {
    flex: 1, flexDirection: 'row', backgroundColor: Colors.error, padding: 14,
    borderRadius: 10, justifyContent: 'center', alignItems: 'center', gap: 8,
  },
  buttonText: { color: '#FFF', fontWeight: '600', fontSize: 15 },
  fasesRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 16 },
  faseItem: { flex: 1, alignItems: 'center', position: 'relative' },
  faseIcon: {
    width: 34, height: 34, borderRadius: 17, backgroundColor: Colors.border,
    justifyContent: 'center', alignItems: 'center', marginBottom: 4,
  },
  faseIconActivo: { backgroundColor: Colors.success },
  faseIconActual: { backgroundColor: Colors.primary },
  faseLabel: { fontSize: 9, color: Colors.textMuted, textAlign: 'center' },
  faseLabelActivo: { color: Colors.success, fontWeight: '600' },
  faseLabelActual: { color: Colors.primary, fontWeight: '700' },
  faseLine: { position: 'absolute', top: 16, right: -30, width: 50, height: 2, backgroundColor: Colors.border },
  faseLineActivo: { backgroundColor: Colors.success },
  estadoActualBox: {
    flexDirection: 'row', alignItems: 'center', gap: 8,
    backgroundColor: Colors.background, borderRadius: 10, padding: 12,
    borderWidth: 1, borderColor: Colors.border,
  },
  estadoActualText: { fontSize: 13, color: Colors.textSecondary, fontWeight: '500' },
});
