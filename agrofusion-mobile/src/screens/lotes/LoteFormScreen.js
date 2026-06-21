import React, { useState, useEffect } from 'react';
import {
  View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert,
  KeyboardAvoidingView, Platform, Image, ActivityIndicator,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { MapView, Marker, Circle } from '../../components/MapComponent';
import * as ImagePicker from 'expo-image-picker';
import { lotesApi, usuariosApi, insumosApi } from '../../api/client';
import FormInput from '../../components/FormInput';
import { Colors } from '../../constants/colors';

const SANTA_CRUZ = { latitude: -17.7833, longitude: -63.1821, latitudeDelta: 0.05, longitudeDelta: 0.05 };

export default function LoteFormScreen({ route, navigation }) {
  const { id } = route.params || {};
  const isEdit = !!id;

  const [form, setForm] = useState({
    usuarioid: '',
    nombre: '',
    superficie: '',
    insumosemallaid: '',
    cantidad_semilla_planificada: '',
    ubicacion: '',
    latitud: String(SANTA_CRUZ.latitude),
    longitud: String(SANTA_CRUZ.longitude),
  });
  const [empleados, setEmpleados] = useState([]);
  const [semillas, setSemillas] = useState([]);
  const [loading, setLoading] = useState(false);
  const [loadingData, setLoadingData] = useState(true);
  const [image, setImage] = useState(null);
  const [selectedSemilla, setSelectedSemilla] = useState(null);
  const [mapRegion, setMapRegion] = useState(SANTA_CRUZ);
  const [markerPos, setMarkerPos] = useState(null);

  useEffect(() => {
    loadCatalogos();
    if (isEdit) loadLote();
    else setLoadingData(false);
  }, []);

  const loadCatalogos = async () => {
    try {
      const [empleadosRes, semillasRes] = await Promise.allSettled([
        usuariosApi.list(),
        insumosApi.list(),
      ]);
      if (empleadosRes.status === 'fulfilled') {
        const users = empleadosRes.value.data?.data || empleadosRes.value.data || [];
        setEmpleados(users.filter(u => u.roles?.some(r => r.name === 'agricultor')));
      }
      if (semillasRes.status === 'fulfilled') {
        const insumos = semillasRes.value.data?.data || semillasRes.value.data || [];
        const seedSlugs = ['material_siembra', 'semilla', 'material de siembra'];
        setSemillas(insumos.filter(i => {
          const tipoNombre = (i.tipo?.nombre || '').toLowerCase();
          const tipoSlug = (i.tipo?.slug || '').toLowerCase();
          return seedSlugs.some(s =>
            tipoNombre.includes(s) || tipoSlug.includes(s) || i.tiposlug === s
          );
        }));
      }
    } catch (e) {
      console.error('Error loading catalogos:', e);
    }
  };

  const loadLote = async () => {
    try {
      const res = await lotesApi.get(id);
      const l = res.data?.data || res.data;
      setForm({
        usuarioid: String(l.usuarioid || ''),
        nombre: l.nombre || '',
        superficie: String(l.superficie || ''),
        insumosemallaid: String(l.insumosemallaid || ''),
        cantidad_semilla_planificada: String(l.cantidad_semilla_planificada || ''),
        ubicacion: l.ubicacion || '',
        latitud: String(l.latitud || SANTA_CRUZ.latitude),
        longitud: String(l.longitud || SANTA_CRUZ.longitude),
      });
      if (l.latitud && l.longitud) {
        const pos = { latitude: parseFloat(l.latitud), longitude: parseFloat(l.longitud) };
        setMarkerPos(pos);
        setMapRegion({ ...pos, latitudeDelta: 0.01, longitudeDelta: 0.01 });
      }
      if (l.imagenurl) setImage({ uri: l.imagenurl });
    } catch (e) {
      Alert.alert('Error', 'No se pudo cargar el lote');
    } finally {
      setLoadingData(false);
    }
  };

  const updateField = (field, value) => setForm(prev => ({ ...prev, [field]: value }));

  const onSelectSemilla = (semilla) => {
    setSelectedSemilla(semilla);
    updateField('insumosemallaid', String(semilla.insumoid));
    if (semilla.dosis_por_ha && form.superficie) {
      const cantidad = (parseFloat(semilla.dosis_por_ha) * parseFloat(form.superficie)).toFixed(3);
      updateField('cantidad_semilla_planificada', cantidad);
    }
  };

  const onSuperficieChange = (value) => {
    updateField('superficie', value);
    if (selectedSemilla?.dosis_por_ha && value) {
      const cantidad = (parseFloat(selectedSemilla.dosis_por_ha) * parseFloat(value)).toFixed(3);
      updateField('cantidad_semilla_planificada', cantidad);
    }
  };

  const onMapPress = (e) => {
    const { latitude, longitude } = e.nativeEvent.coordinate;
    setMarkerPos({ latitude, longitude });
    updateField('latitud', latitude.toFixed(7));
    updateField('longitud', longitude.toFixed(7));
    reverseGeocode(latitude, longitude);
  };

  const reverseGeocode = async (lat, lng) => {
    try {
      const url = `https://nominatim.openstreetmap.org/reverse?format=json&zoom=17&addressdetails=1&lat=${lat}&lon=${lng}&accept-language=es`;
      const response = await fetch(url, {
        headers: {
          'Accept': 'application/json',
          'Accept-Language': 'es',
          'User-Agent': 'AgroFusionApp/1.0',
        },
      });
      if (!response.ok) return;
      const text = await response.text();
      if (!text || text.startsWith('<')) return;
      const data = JSON.parse(text);
      const addr = data.address || {};
      const parts = [
        addr.road, addr.suburb, addr.neighbourhood,
        addr.city || addr.town || addr.village,
      ].filter(Boolean);
      if (parts.length > 0) {
        updateField('ubicacion', parts.join(', '));
      }
    } catch (e) {
      // Reverse geocoding is non-critical - silently fail
    }
  };

  const pickImage = async () => {
    const permission = await ImagePicker.requestCameraPermissionsAsync();
    if (!permission.granted) {
      Alert.alert('Permiso requerido', 'Necesitas otorgar permisos de cámara');
      return;
    }
    Alert.alert('Imagen del lote', 'Elige una opción', [
      { text: 'Cámara', onPress: takePhoto },
      { text: 'Galería', onPress: pickFromGallery },
      { text: 'Cancelar', style: 'cancel' },
    ]);
  };

  const takePhoto = async () => {
    try {
      const result = await ImagePicker.launchCameraAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: true,
        aspect: [4, 3],
        quality: 0.8,
      });
      if (!result.canceled) {
        setImage(result.assets[0]);
      }
    } catch (e) {
      Alert.alert('Error', 'No se pudo tomar la foto');
    }
  };

  const pickFromGallery = async () => {
    try {
      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: true,
        aspect: [4, 3],
        quality: 0.8,
      });
      if (!result.canceled) {
        setImage(result.assets[0]);
      }
    } catch (e) {
      Alert.alert('Error', 'No se pudo seleccionar la imagen');
    }
  };

  const handleSave = async () => {
    if (!form.nombre) {
      Alert.alert('Error', 'El nombre del lote es obligatorio');
      return;
    }
    if (!form.superficie) {
      Alert.alert('Error', 'La superficie es obligatoria');
      return;
    }
    if (!form.usuarioid) {
      Alert.alert('Error', 'Debes asignar un empleado');
      return;
    }
    setLoading(true);
    try {
      const data = {
        usuarioid: parseInt(form.usuarioid),
        nombre: form.nombre,
        superficie: parseFloat(form.superficie),
        insumosemallaid: form.insumosemallaid ? parseInt(form.insumosemallaid) : null,
        cantidad_semilla_planificada: form.cantidad_semilla_planificada ? parseFloat(form.cantidad_semilla_planificada) : null,
        ubicacion: form.ubicacion || null,
        latitud: form.latitud ? parseFloat(form.latitud) : null,
        longitud: form.longitud ? parseFloat(form.longitud) : null,
      };

      if (isEdit) {
        await lotesApi.update(id, data);
      } else {
        await lotesApi.create(data);
      }
      navigation.goBack();
    } catch (e) {
      const msg = e.response?.data?.message || 'No se pudo guardar el lote';
      if (e.response?.data?.errors) {
        const errors = Object.values(e.response.data.errors).flat().join('\n');
        Alert.alert('Error de validación', errors);
      } else {
        Alert.alert('Error', msg);
      }
    } finally {
      setLoading(false);
    }
  };

  if (loadingData) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={Colors.primary} />
        <Text style={styles.loadingText}>Cargando...</Text>
      </View>
    );
  }

  return (
    <KeyboardAvoidingView style={styles.container} behavior={Platform.OS === 'ios' ? 'padding' : 'height'}>
      <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">

        <View style={styles.infoBanner}>
          <Ionicons name="information-circle-outline" size={20} color={Colors.primary} />
          <Text style={styles.infoText}>
            Se completa automáticamente: código de trazabilidad, estado "Planificado" y unidad en hectáreas.
          </Text>
        </View>

        <Text style={styles.sectionTitle}>Datos del lote</Text>

        <Text style={styles.label}>Empleado asignado *</Text>
        <View style={styles.empleadoContainer}>
          {empleados.map(emp => (
            <TouchableOpacity
              key={emp.usuarioid}
              style={[
                styles.empleadoChip,
                String(emp.usuarioid) === form.usuarioid && styles.empleadoChipActive,
              ]}
              onPress={() => updateField('usuarioid', String(emp.usuarioid))}
            >
              <Ionicons name="person-outline" size={14} color={String(emp.usuarioid) === form.usuarioid ? '#FFF' : Colors.textSecondary} />
              <Text style={[
                styles.empleadoChipText,
                String(emp.usuarioid) === form.usuarioid && styles.empleadoChipTextActive,
              ]}>
                {emp.nombre} {emp.apellido}
              </Text>
            </TouchableOpacity>
          ))}
          {empleados.length === 0 && (
            <Text style={styles.emptyText}>No hay empleados con rol agricultor</Text>
          )}
        </View>

        <FormInput
          label="Nombre del lote *"
          icon="map-outline"
          value={form.nombre}
          onChangeText={updateField.bind(null, 'nombre')}
          placeholder="Ej: Lote Norte A1"
        />

        <FormInput
          label="Superficie (hectáreas) *"
          icon="expand-outline"
          value={form.superficie}
          onChangeText={onSuperficieChange}
          placeholder="Ej: 12.5"
          keyboardType="numeric"
        />

        <Text style={styles.label}>Semilla / cultivo a cosechar</Text>
        <Text style={styles.helpText}>Selecciona una semilla del inventario (opcional)</Text>
        <View style={styles.semillaContainer}>
          {semillas.map(sem => (
            <TouchableOpacity
              key={sem.insumoid}
              style={[
                styles.semillaChip,
                String(sem.insumoid) === form.insumosemallaid && styles.semillaChipActive,
              ]}
              onPress={() => onSelectSemilla(sem)}
            >
              <Ionicons name="leaf-outline" size={14} color={String(sem.insumoid) === form.insumosemallaid ? '#FFF' : Colors.textSecondary} />
              <Text style={[
                styles.semillaChipText,
                String(sem.insumoid) === form.insumosemallaid && styles.semillaChipTextActive,
              ]}>
                {sem.nombre}
              </Text>
              {sem.stock != null && (
                <Text style={[
                  styles.semillaStock,
                  String(sem.insumoid) === form.insumosemallaid && styles.semillaStockActive,
                ]}>
                  ({sem.stock} {sem.unidad_medida?.nombre || 'kg'})
                </Text>
              )}
            </TouchableOpacity>
          ))}
          {semillas.length === 0 && (
            <Text style={styles.emptyText}>No hay semillas en el inventario</Text>
          )}
        </View>

        {selectedSemilla && (
          <FormInput
            label="Cantidad de semilla estimada"
            icon="calculator-outline"
            value={form.cantidad_semilla_planificada}
            onChangeText={updateField.bind(null, 'cantidad_semilla_planificada')}
            placeholder="0.000"
            keyboardType="numeric"
          />
        )}

        {selectedSemilla?.dosis_por_ha && form.superficie && (
          <View style={styles.previewAlert}>
            <Ionicons name="leaf" size={16} color={Colors.success} />
            <Text style={styles.previewText}>
              Referencia: {selectedSemilla.dosis_por_ha} {selectedSemilla.dosis_unidad || 'kg'}/ha × {form.superficie} ha ={' '}
              {form.cantidad_semilla_planificada} {selectedSemilla.dosis_unidad || 'kg'} de semilla
            </Text>
          </View>
        )}

        <FormInput
          label="Calle o referencia"
          icon="location-outline"
          value={form.ubicacion}
          onChangeText={updateField.bind(null, 'ubicacion')}
          placeholder="Se completa al marcar el mapa"
        />

        <Text style={styles.label}>Imagen del lote (opcional)</Text>
        <TouchableOpacity style={styles.imagePicker} onPress={pickImage}>
          {image ? (
            <View style={styles.imagePreview}>
              <Image source={{ uri: image.uri }} style={styles.previewImage} />
              <TouchableOpacity style={styles.removeImage} onPress={() => setImage(null)}>
                <Ionicons name="close-circle" size={24} color={Colors.error} />
              </TouchableOpacity>
            </View>
          ) : (
            <View style={styles.imagePlaceholder}>
              <Ionicons name="camera-outline" size={32} color={Colors.textMuted} />
              <Text style={styles.imagePlaceholderText}>Tomar foto o elegir de galería</Text>
            </View>
          )}
        </TouchableOpacity>

        <Text style={styles.label}>Marca la parcela en el mapa *</Text>
        <Text style={styles.helpText}>Haz clic donde está el lote (Santa Cruz por defecto)</Text>
        <View style={styles.mapContainer}>
          <MapView
            style={styles.map}
            initialRegion={mapRegion}
            region={mapRegion}
            onPress={onMapPress}
            showsUserLocation={false}
          >
            {markerPos && (
              <>
                <Marker coordinate={markerPos}>
                  <View style={styles.markerContainer}>
                    <Ionicons name="location" size={32} color={Colors.primary} />
                  </View>
                </Marker>
                {form.superficie ? (
                  <Circle
                    center={markerPos}
                    radius={Math.sqrt(parseFloat(form.superficie) * 10000 / Math.PI)}
                    fillColor="rgba(16, 185, 129, 0.25)"
                    strokeColor="#10b981"
                    strokeWidth={2}
                  />
                ) : null}
              </>
            )}
          </MapView>
        </View>

        {markerPos && (
          <View style={styles.coordsContainer}>
            <Ionicons name="location-outline" size={16} color={Colors.primary} />
            <Text style={styles.coordsText}>
              Lat: {form.latitud} | Lng: {form.longitud}
            </Text>
          </View>
        )}

        <TouchableOpacity
          style={[styles.saveButton, loading && { opacity: 0.6 }]}
          onPress={handleSave}
          disabled={loading}
        >
          <Ionicons name="save-outline" size={20} color="#FFF" />
          <Text style={styles.saveButtonText}>
            {loading ? 'Guardando...' : isEdit ? 'Actualizar Lote' : 'Guardar Lote'}
          </Text>
        </TouchableOpacity>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  scroll: { padding: 16, paddingBottom: 32 },
  loadingContainer: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: Colors.background },
  loadingText: { marginTop: 12, color: Colors.textSecondary },
  infoBanner: {
    flexDirection: 'row', alignItems: 'flex-start', backgroundColor: Colors.primaryLight,
    borderRadius: 10, padding: 12, marginBottom: 16, gap: 8,
  },
  infoText: { fontSize: 13, color: Colors.primaryDark, flex: 1, lineHeight: 18 },
  sectionTitle: { fontSize: 18, fontWeight: '700', color: Colors.text, marginBottom: 16 },
  label: { fontSize: 14, fontWeight: '600', color: Colors.text, marginBottom: 6 },
  helpText: { fontSize: 12, color: Colors.textMuted, marginBottom: 8 },
  empleadoContainer: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginBottom: 16 },
  empleadoChip: {
    flexDirection: 'row', alignItems: 'center', paddingHorizontal: 14, paddingVertical: 10,
    borderRadius: 20, backgroundColor: Colors.surface, borderWidth: 1, borderColor: Colors.border, gap: 6,
  },
  empleadoChipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
  empleadoChipText: { fontSize: 13, color: Colors.textSecondary },
  empleadoChipTextActive: { color: '#FFF', fontWeight: '600' },
  semillaContainer: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginBottom: 16 },
  semillaChip: {
    flexDirection: 'row', alignItems: 'center', paddingHorizontal: 14, paddingVertical: 10,
    borderRadius: 20, backgroundColor: Colors.surface, borderWidth: 1, borderColor: Colors.border, gap: 6,
  },
  semillaChipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
  semillaChipText: { fontSize: 13, color: Colors.textSecondary },
  semillaChipTextActive: { color: '#FFF', fontWeight: '600' },
  semillaStock: { fontSize: 11, color: Colors.textMuted },
  semillaStockActive: { color: 'rgba(255,255,255,0.8)' },
  emptyText: { fontSize: 13, color: Colors.textMuted, fontStyle: 'italic' },
  previewAlert: {
    flexDirection: 'row', alignItems: 'center', backgroundColor: Colors.primaryLight,
    borderRadius: 8, padding: 10, marginBottom: 16, gap: 8,
  },
  previewText: { fontSize: 12, color: Colors.primaryDark, flex: 1 },
  imagePicker: { marginBottom: 16 },
  imagePlaceholder: {
    backgroundColor: Colors.surface, borderWidth: 2, borderColor: Colors.border, borderStyle: 'dashed',
    borderRadius: 12, padding: 24, alignItems: 'center', justifyContent: 'center', height: 120,
  },
  imagePlaceholderText: { fontSize: 13, color: Colors.textMuted, marginTop: 8 },
  imagePreview: { position: 'relative' },
  previewImage: { width: '100%', height: 150, borderRadius: 12 },
  removeImage: { position: 'absolute', top: 8, right: 8 },
  mapContainer: {
    borderRadius: 12, overflow: 'hidden', borderWidth: 1, borderColor: Colors.border, marginBottom: 8,
  },
  map: { width: '100%', height: 320 },
  markerContainer: { alignItems: 'center', justifyContent: 'center' },
  coordsContainer: {
    flexDirection: 'row', alignItems: 'center', backgroundColor: Colors.primaryLight,
    borderRadius: 8, padding: 10, marginBottom: 16, gap: 8,
  },
  coordsText: { fontSize: 12, color: Colors.primaryDark },
  saveButton: {
    flexDirection: 'row', backgroundColor: Colors.primary, borderRadius: 12, paddingVertical: 16,
    alignItems: 'center', justifyContent: 'center', marginTop: 8, gap: 8,
  },
  saveButtonText: { color: '#FFF', fontSize: 16, fontWeight: '600' },
});
