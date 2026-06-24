import React, { useState, useEffect } from 'react';
import {
  View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert, Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { actividadesApi } from '../../api/client';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockActividad, getMockEvidenciaUrl } from '../../data/mockAgricultorData';
import LoadingSpinner from '../../components/LoadingSpinner';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

function resolveEvidenciaUrl(actividad) {
  if (USE_MOCK_DATA) return getMockEvidenciaUrl(actividad);
  if (!actividad?.evidencia_foto_path) return null;
  return actividad.evidencia_foto_url || null;
}

export default function ActividadDetailScreen({ route, navigation }) {
  const { actividadId, lote } = route.params || {};
  const [actividad, setActividad] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => { loadActividad(); }, []);

  const loadActividad = async () => {
    try {
      if (USE_MOCK_DATA) {
        setActividad(getMockActividad(actividadId));
      } else {
        const res = await actividadesApi.get(actividadId);
        setActividad(res.data?.data || res.data);
      }
    } catch (e) {
      if (USE_MOCK_DATA) {
        setActividad(getMockActividad(actividadId));
      } else {
        Alert.alert('Error', 'No se pudo cargar la actividad');
      }
    } finally { setLoading(false); }
  };

  if (!actividadId) {
    return (
      <View style={styles.container}>
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Error</Text>
          <Text style={styles.cardDesc}>No se recibió el ID de la actividad.</Text>
        </View>
      </View>
    );
  }

  if (loading) return <LoadingSpinner fullScreen message="Cargando actividad..." />;

  const tipo = actividad?.tipo_actividad?.nombre || 'Actividad';
  const prioridad = actividad?.prioridad?.nombre || 'Normal';
  const responsable = actividad?.usuario ? `${actividad.usuario.nombre} ${actividad.usuario.apellido}` : 'Sin responsable';
  const loteNombre = lote?.nombre || actividad?.lote?.nombre || 'Sin lote';
  const evidenciaUrl = resolveEvidenciaUrl(actividad);

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.scroll}>
      <View style={styles.card}>
        <View style={styles.cardHeader}>
          <View style={styles.typeBadge}>
            <Ionicons name="clipboard-outline" size={14} color="#FFF" />
            <Text style={styles.typeBadgeText}>{tipo}</Text>
          </View>
          <View style={[styles.priorityBadge, { backgroundColor: getPriorityColor(prioridad) }]}>
            <Text style={styles.priorityBadgeText}>{prioridad}</Text>
          </View>
        </View>

        <Text style={styles.cardTitle}>{actividad?.descripcion || tipo}</Text>

        <View style={styles.statusBox}>
          <Ionicons
            name={actividad?.fechafin ? 'checkmark-circle' : 'time-outline'}
            size={20}
            color={actividad?.fechafin ? Colors.success : Colors.warning}
          />
          <Text style={[styles.statusText, { color: actividad?.fechafin ? Colors.success : Colors.warning }]}>
            {actividad?.fechafin ? 'Completada' : 'Pendiente'}
          </Text>
        </View>

        <View style={styles.metaGrid}>
          <Info icon="person-outline" label="Responsable" value={responsable} />
          <Info icon="location-outline" label="Lote" value={loteNombre} />
          <Info icon="calendar-outline" label="Fecha inicio" value={formatDate(actividad?.fechainicio)} />
          <Info icon="checkmark-done-outline" label="Fecha fin" value={formatDate(actividad?.fechafin)} />
          <Info icon="chatbubble-outline" label="Observaciones" value={actividad?.observaciones || 'Ninguna'} />
        </View>
      </View>

      {evidenciaUrl ? (
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Evidencia fotográfica</Text>
          <Image source={{ uri: evidenciaUrl }} style={styles.evidenciaImage} resizeMode="cover" />
        </View>
      ) : (
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Evidencia fotográfica</Text>
          <View style={styles.noEvidenciaBox}>
            <Ionicons name="image-outline" size={32} color={Colors.textMuted} />
            <Text style={styles.noEvidenciaText}>No hay foto de evidencia registrada</Text>
          </View>
        </View>
      )}

      {!actividad?.fechafin && (
        <TouchableOpacity
          style={styles.completeButton}
          onPress={() => navigation.replace('ActividadComplete', { actividadId, lote })}
        >
          <Ionicons name="camera-outline" size={20} color="#FFF" />
          <Text style={styles.completeButtonText}>Completar actividad con foto</Text>
        </TouchableOpacity>
      )}
    </ScrollView>
  );
}

function getPriorityColor(prioridad) {
  const p = (prioridad || '').toLowerCase();
  if (p.includes('crítica') || p.includes('critica') || p.includes('urgente')) return Colors.error;
  if (p.includes('alta')) return Colors.warning;
  if (p.includes('media')) return Colors.info;
  return Colors.success;
}

function Info({ icon, label, value }) {
  return (
    <View style={styles.infoItem}>
      <Ionicons name={icon} size={16} color={Colors.primary} />
      <Text style={styles.infoLabel}>{label}</Text>
      <Text style={styles.infoValue} numberOfLines={3}>{value}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  scroll: { padding: 16, paddingBottom: 32 },
  card: { backgroundColor: Colors.surface, borderRadius: 12, padding: 16, marginBottom: 16, borderWidth: 1, borderColor: Colors.border },
  cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
  typeBadge: { flexDirection: 'row', alignItems: 'center', backgroundColor: Colors.primary, paddingHorizontal: 12, paddingVertical: 6, borderRadius: 8, gap: 6 },
  typeBadgeText: { color: '#FFF', fontSize: 12, fontWeight: '600' },
  priorityBadge: { paddingHorizontal: 12, paddingVertical: 6, borderRadius: 8 },
  priorityBadgeText: { color: '#FFF', fontSize: 12, fontWeight: '600' },
  cardTitle: { fontSize: 18, fontWeight: '700', color: Colors.text, marginBottom: 12 },
  cardDesc: { fontSize: 14, color: Colors.textSecondary },
  statusBox: {
    flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 16,
    backgroundColor: Colors.background, borderRadius: 10, padding: 12, borderWidth: 1, borderColor: Colors.border,
  },
  statusText: { fontSize: 14, fontWeight: '600' },
  metaGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  infoItem: { width: '47%', backgroundColor: Colors.background, borderRadius: 10, padding: 12, gap: 4, borderWidth: 1, borderColor: Colors.border },
  infoLabel: { fontSize: 11, color: Colors.textMuted, textTransform: 'uppercase', letterSpacing: 0.5 },
  infoValue: { fontSize: 13, color: Colors.text, fontWeight: '500' },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.text, marginBottom: 12 },
  evidenciaImage: { width: '100%', height: 260, borderRadius: 12 },
  noEvidenciaBox: { backgroundColor: Colors.background, borderRadius: 12, padding: 32, alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: Colors.border },
  noEvidenciaText: { fontSize: 13, color: Colors.textMuted, marginTop: 8 },
  completeButton: {
    flexDirection: 'row', backgroundColor: Colors.success, borderRadius: 12, paddingVertical: 16,
    alignItems: 'center', justifyContent: 'center', gap: 8,
  },
  completeButtonText: { color: '#FFF', fontSize: 16, fontWeight: '600' },
});
