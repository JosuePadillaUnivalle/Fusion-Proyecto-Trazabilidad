import React, { useState, useEffect } from 'react';
import {
  View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert, Image,
  KeyboardAvoidingView, Platform,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import { actividadesApi } from '../../api/client';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockActividad } from '../../data/mockAgricultorData';
import { completeMockActividad, actividadRequiereComprobante } from '../../data/mockRoleActions';
import FormInput from '../../components/FormInput';
import LoadingSpinner from '../../components/LoadingSpinner';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function ActividadCompleteScreen({ route, navigation }) {
  const { actividadId, lote } = route.params || {};
  const [actividad, setActividad] = useState(null);
  const [observaciones, setObservaciones] = useState('');
  const [image, setImage] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => { loadActividad(); }, []);

  const loadActividad = async () => {
    try {
      if (USE_MOCK_DATA) {
        const data = getMockActividad(actividadId);
        setActividad(data);
        if (data?.observaciones) setObservaciones(data.observaciones);
      } else {
        const res = await actividadesApi.get(actividadId);
        const data = res.data?.data || res.data;
        setActividad(data);
        if (data?.observaciones) setObservaciones(data.observaciones);
      }
    } catch (e) {
      if (USE_MOCK_DATA) {
        const data = getMockActividad(actividadId);
        setActividad(data);
      } else {
        Alert.alert('Error', 'No se pudo cargar la actividad');
      }
    } finally { setLoading(false); }
  };

  const pickImage = async () => {
    const permission = await ImagePicker.requestCameraPermissionsAsync();
    if (!permission.granted) {
      Alert.alert('Permiso requerido', 'Se necesita acceso a la cámara');
      return;
    }
    const result = await ImagePicker.launchCameraAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: true,
      aspect: [4, 3],
      quality: 0.7,
    });
    if (!result.canceled) setImage(result.assets[0]);
  };

  const handleComplete = async () => {
    if (!image) {
      const esSiembra = (actividad?.tipo_actividad?.nombre || '').toLowerCase().includes('siembra');
      Alert.alert(
        'Comprobante requerido',
        esSiembra
          ? 'Debe adjuntar una foto como comprobante de la siembra realizada.'
          : 'Debes tomar una foto como evidencia de que realizaste la actividad.',
      );
      return;
    }
    setSaving(true);
    try {
      if (USE_MOCK_DATA) {
        await new Promise(r => setTimeout(r, 600));
        completeMockActividad(actividadId, observaciones || 'Actividad completada con evidencia fotográfica.', image?.uri);
        Alert.alert('Éxito', 'Actividad marcada como realizada');
        navigation.goBack();
        return;
      }
      const data = new FormData();
      const uriParts = image.uri.split('.');
      const ext = uriParts[uriParts.length - 1];
      data.append('evidencia_foto', { uri: image.uri, name: `evidencia.${ext}`, type: `image/${ext}` });
      data.append('observaciones', observaciones || 'Actividad completada con evidencia fotográfica.');
      await actividadesApi.marcarRealizada(actividadId, data, { headers: { 'Content-Type': 'multipart/form-data' } });
      Alert.alert('Éxito', 'Actividad marcada como realizada con evidencia');
      navigation.goBack();
    } catch (e) {
      Alert.alert('Error', e.response?.data?.message || 'No se pudo completar la actividad');
    } finally { setSaving(false); }
  };

  if (loading) return <LoadingSpinner fullScreen message="Cargando actividad..." />;

  const tipo = actividad?.tipo_actividad?.nombre || 'Actividad';
  const esSiembra = tipo.toLowerCase().includes('siembra');
  const requiereComprobante = actividadRequiereComprobante(actividad);
  const prioridad = actividad?.prioridad?.nombre || 'Normal';
  const responsable = actividad?.usuario ? `${actividad.usuario.nombre} ${actividad.usuario.apellido}` : 'Sin responsable';
  const loteNombre = lote?.nombre || actividad?.lote?.nombre || 'Sin lote';

  return (
    <KeyboardAvoidingView style={styles.container} behavior={Platform.OS === 'ios' ? 'padding' : 'height'}>
      <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
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

          {requiereComprobante && !actividad?.fechafin && (
            <View style={styles.comprobanteAviso}>
              <Ionicons name="camera-outline" size={16} color={Colors.primary} />
              <Text style={styles.comprobanteAvisoText}>
                {esSiembra
                  ? 'Al completar debe enviar comprobante fotográfico de la siembra (surcos, semilla o área sembrada).'
                  : 'Esta actividad requiere comprobante fotográfico al cerrarla.'}
              </Text>
            </View>
          )}

          <View style={styles.metaGrid}>
            <Info icon="person-outline" label="Responsable" value={responsable} />
            <Info icon="location-outline" label="Lote" value={loteNombre} />
            <Info icon="calendar-outline" label="Fecha inicio" value={formatDate(actividad?.fechainicio)} />
            <Info icon="chatbubble-outline" label="Observaciones actuales" value={actividad?.observaciones || 'Ninguna'} />
          </View>
        </View>

        <FormInput
          label="Observaciones de cierre"
          value={observaciones}
          onChangeText={setObservaciones}
          placeholder="¿Cómo se completó la actividad?"
          multiline
        />

        <Text style={styles.label}>
          {esSiembra ? 'Comprobante de siembra *' : 'Foto evidencia *'}
        </Text>
        {!image ? (
          <TouchableOpacity style={styles.imagePlaceholder} onPress={pickImage}>
            <Ionicons name="camera-outline" size={32} color={Colors.textMuted} />
            <Text style={styles.imagePlaceholderText}>
              {esSiembra ? 'Tomar foto del comprobante de siembra' : 'Tomar foto de evidencia'}
            </Text>
          </TouchableOpacity>
        ) : (
          <View style={styles.imagePreviewBox}>
            <Image source={{ uri: image.uri }} style={styles.previewImage} />
            <TouchableOpacity style={styles.removeImage} onPress={() => setImage(null)}>
              <Ionicons name="close-circle" size={28} color={Colors.error} />
            </TouchableOpacity>
          </View>
        )}

        <TouchableOpacity style={[styles.saveButton, saving && { opacity: 0.6 }]} onPress={handleComplete} disabled={saving}>
          <Ionicons name="checkmark-circle-outline" size={20} color="#FFF" />
          <Text style={styles.saveButtonText}>{saving ? 'Guardando...' : 'Marcar como realizada'}</Text>
        </TouchableOpacity>
      </ScrollView>
    </KeyboardAvoidingView>
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
      <Text style={styles.infoValue} numberOfLines={2}>{value}</Text>
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
  comprobanteAviso: {
    flexDirection: 'row', alignItems: 'flex-start', gap: 8, marginBottom: 12,
    padding: 12, borderRadius: 10, backgroundColor: Colors.primaryLight,
    borderWidth: 1, borderColor: Colors.border,
  },
  comprobanteAvisoText: { flex: 1, fontSize: 13, color: Colors.text, lineHeight: 18 },
  metaGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  infoItem: { width: '47%', backgroundColor: Colors.background, borderRadius: 10, padding: 12, gap: 4, borderWidth: 1, borderColor: Colors.border },
  infoLabel: { fontSize: 11, color: Colors.textMuted, textTransform: 'uppercase', letterSpacing: 0.5 },
  infoValue: { fontSize: 13, color: Colors.text, fontWeight: '500' },
  label: { fontSize: 14, fontWeight: '600', color: Colors.text, marginBottom: 8 },
  imagePlaceholder: {
    backgroundColor: Colors.surface, borderWidth: 2, borderColor: Colors.border, borderStyle: 'dashed',
    borderRadius: 12, padding: 24, alignItems: 'center', justifyContent: 'center', height: 120, marginBottom: 16,
  },
  imagePlaceholderText: { fontSize: 13, color: Colors.textMuted, marginTop: 8 },
  imagePreviewBox: { position: 'relative', marginBottom: 16 },
  previewImage: { width: '100%', height: 200, borderRadius: 12 },
  removeImage: { position: 'absolute', top: 8, right: 8 },
  saveButton: {
    flexDirection: 'row', backgroundColor: Colors.success, borderRadius: 12, paddingVertical: 16,
    alignItems: 'center', justifyContent: 'center', marginTop: 16, gap: 8,
  },
  saveButtonText: { color: '#FFF', fontSize: 16, fontWeight: '600' },
});
