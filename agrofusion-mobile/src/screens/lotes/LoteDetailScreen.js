import React, { useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert, Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { MapView, Marker, Circle } from '../../components/MapComponent';
import { lotesApi } from '../../api/client';
import LoadingSpinner from '../../components/LoadingSpinner';
import StatusBadge from '../../components/StatusBadge';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function LoteDetailScreen({ route, navigation }) {
  const { id } = route.params;
  const [lote, setLote] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => { loadLote(); }, [id]);

  const loadLote = async () => {
    try {
      const res = await lotesApi.get(id);
      setLote(res.data?.data || res.data);
    } catch (e) {
      Alert.alert('Error', 'No se pudo cargar el lote');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = () => {
    Alert.alert('Eliminar Lote', '¿Estás seguro de eliminar este lote?', [
      { text: 'Cancelar', style: 'cancel' },
      {
        text: 'Eliminar', style: 'destructive', onPress: async () => {
          try {
            await lotesApi.delete(id);
            navigation.goBack();
          } catch (e) {
            Alert.alert('Error', 'No se pudo eliminar');
          }
        }
      },
    ]);
  };

  if (loading) return <LoadingSpinner fullScreen message="Cargando detalle..." />;
  if (!lote) return <View style={styles.container}><Text>No se encontró el lote</Text></View>;

  const hasCoords = lote.latitud && lote.longitud;
  const coords = hasCoords
    ? { latitude: parseFloat(lote.latitud), longitude: parseFloat(lote.longitud), latitudeDelta: 0.01, longitudeDelta: 0.01 }
    : null;

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.eyebrow}>Lote agrícola</Text>
        <Text style={styles.title}>{lote.nombre || `Lote #${lote.loteid}`}</Text>
        <View style={styles.headerMeta}>
          <Text style={styles.metaText}>
            {lote.usuario ? `${lote.usuario.nombre} ${lote.usuario.apellido}` : 'Sin encargado'}
          </Text>
          {lote.ubicacion && <Text style={styles.metaText}> · {lote.ubicacion}</Text>}
        </View>
        <View style={styles.chipsRow}>
          <StatusBadge
            status={lote.estadoTipo?.slug || 'planificado'}
            label={lote.estadoTipo?.nombre || 'Planificado'}
          />
          <View style={styles.cultivoChip}>
            <Ionicons name="leaf" size={12} color={Colors.primary} />
            <Text style={styles.cultivoChipText}>
              {lote.cultivo_etiqueta || lote.cultivo?.nombre || 'Sin cultivo'}
            </Text>
          </View>
        </View>
      </View>

      <View style={styles.statsRow}>
        <StatCard icon="expand-outline" label="Hectáreas" value={lote.superficie ? `${lote.superficie}` : '-'} color={Colors.primary} />
        <StatCard icon="calendar-outline" label="Siembra" value={lote.fechasiembra ? formatDate(lote.fechasiembra) : 'N/A'} color={Colors.info} />
        <StatCard icon="flask-outline" label="Insumos" value={String(lote.loteInsumos?.length || 0)} color={Colors.purple} />
        <StatCard icon="basket-outline" label="Cosechas" value={String(lote.producciones?.length || 0)} color={Colors.warning} />
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Datos del lote</Text>
        <InfoRow icon="hashtag-outline" label="ID" value={`#${lote.loteid}`} />
        <InfoRow icon="expand-outline" label="Superficie" value={lote.superficie ? `${lote.superficie} ha` : '-'} />
        <InfoRow icon="calendar-outline" label="Fecha de siembra" value={lote.fechasiembra ? formatDate(lote.fechasiembra) : 'Pendiente'} />
        <InfoRow
          icon="person-outline"
          label="Propietario"
          value={lote.usuario ? `${lote.usuario.nombre} ${lote.usuario.apellido}` : '-'}
        />
        {lote.codigo_trazabilidad && (
          <InfoRow icon="barcode-outline" label="Código trazabilidad" value={lote.codigo_trazabilidad} />
        )}
        {lote.ubicacion && (
          <InfoRow icon="location-outline" label="Ubicación" value={lote.ubicacion} />
        )}
        {lote.insumoSemilla && (
          <InfoRow icon="leaf-outline" label="Semilla" value={lote.insumoSemilla.nombre} />
        )}
        {lote.cantidad_semilla_planificada != null && (
          <InfoRow
            icon="calculator-outline"
            label="Cantidad semilla"
            value={`${lote.cantidad_semilla_planificada} ${lote.insumoSemilla?.dosis_unidad || 'kg'}`}
          />
        )}
      </View>

      {lote.imagenurl && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Imagen del lote</Text>
          <Image source={{ uri: lote.imagenurl }} style={styles.loteImage} resizeMode="cover" />
        </View>
      )}

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
                  fillColor="rgba(16, 185, 129, 0.25)"
                  strokeColor="#10b981"
                  strokeWidth={2}
                />
              ) : null}
            </MapView>
          </View>
        </View>
      )}

      {lote.producciones && lote.producciones.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Producciones ({lote.producciones.length})</Text>
          {lote.producciones.map((prod, i) => (
            <View key={i} style={styles.subItem}>
              <Text style={styles.subItemTitle}>{prod.cantidad} {prod.unidad || 'unidades'}</Text>
              <Text style={styles.subItemDate}>{formatDate(prod.fechaproduccion || prod.fecharegistro)}</Text>
            </View>
          ))}
        </View>
      )}

      {lote.actividades && lote.actividades.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Actividades ({lote.actividades.length})</Text>
          {lote.actividades.map((act, i) => (
            <View key={i} style={styles.subItem}>
              <Text style={styles.subItemTitle}>{act.descripcion || act.tipo_actividad?.nombre}</Text>
              <Text style={styles.subItemDate}>{formatDate(act.fechaactividad || act.fecharegistro)}</Text>
            </View>
          ))}
        </View>
      )}

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
    </ScrollView>
  );
}

const StatCard = ({ icon, label, value, color }) => (
  <View style={styles.statCard}>
    <View style={[styles.statIcon, { backgroundColor: color + '20' }]}>
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
  header: { backgroundColor: Colors.primary, padding: 24, paddingBottom: 20 },
  eyebrow: { fontSize: 12, color: 'rgba(255,255,255,0.7)', textTransform: 'uppercase', letterSpacing: 1 },
  title: { fontSize: 24, fontWeight: 'bold', color: '#FFF', marginTop: 4 },
  headerMeta: { flexDirection: 'row', flexWrap: 'wrap', marginTop: 8 },
  metaText: { fontSize: 13, color: 'rgba(255,255,255,0.85)' },
  chipsRow: { flexDirection: 'row', gap: 8, marginTop: 12, alignItems: 'center' },
  cultivoChip: {
    flexDirection: 'row', alignItems: 'center', gap: 4,
    backgroundColor: 'rgba(255,255,255,0.2)', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12,
  },
  cultivoChipText: { color: '#FFF', fontSize: 12, fontWeight: '600' },
  statsRow: { flexDirection: 'row', padding: 12, gap: 8 },
  statCard: {
    flex: 1, backgroundColor: Colors.surface, borderRadius: 12, padding: 12, alignItems: 'center',
    shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.1, shadowRadius: 3, elevation: 2,
  },
  statIcon: { width: 40, height: 40, borderRadius: 10, justifyContent: 'center', alignItems: 'center', marginBottom: 8 },
  statValue: { fontSize: 18, fontWeight: 'bold', color: Colors.text },
  statLabel: { fontSize: 11, color: Colors.textMuted, marginTop: 2 },
  section: { backgroundColor: Colors.surface, margin: 12, borderRadius: 12, padding: 16 },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.text, marginBottom: 12 },
  infoRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: Colors.border },
  infoContent: { marginLeft: 12, flex: 1 },
  infoLabel: { fontSize: 12, color: Colors.textSecondary },
  infoValue: { fontSize: 15, color: Colors.text, fontWeight: '500' },
  loteImage: { width: '100%', height: 200, borderRadius: 12 },
  mapContainer: { borderRadius: 12, overflow: 'hidden', borderWidth: 1, borderColor: Colors.border },
  map: { width: '100%', height: 250 },
  subItem: { paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: Colors.border },
  subItemTitle: { fontSize: 14, fontWeight: '500', color: Colors.text },
  subItemDate: { fontSize: 12, color: Colors.textSecondary, marginTop: 2 },
  actions: { flexDirection: 'row', gap: 12, padding: 16, paddingBottom: 32 },
  editButton: { flex: 1, flexDirection: 'row', backgroundColor: Colors.info, padding: 14, borderRadius: 10, justifyContent: 'center', alignItems: 'center', gap: 8 },
  deleteButton: { flex: 1, flexDirection: 'row', backgroundColor: Colors.error, padding: 14, borderRadius: 10, justifyContent: 'center', alignItems: 'center', gap: 8 },
  buttonText: { color: '#FFF', fontWeight: '600', fontSize: 15 },
});
