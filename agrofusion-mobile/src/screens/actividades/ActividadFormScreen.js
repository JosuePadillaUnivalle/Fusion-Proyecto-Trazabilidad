import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert, KeyboardAvoidingView, Platform } from 'react-native';
import { actividadesApi, catalogosApi, lotesApi } from '../../api/client';
import FormInput from '../../components/FormInput';
import { Colors } from '../../constants/colors';

export default function ActividadFormScreen({ route, navigation }) {
  const { id } = route.params || {};
  const isEdit = !!id;
  const [form, setForm] = useState({ descripcion: '', loteid: '', tipoactividadid: '', fechaactividad: '' });
  const [lotes, setLotes] = useState([]);
  const [tipos, setTipos] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    loadCatalogos();
    if (isEdit) loadActividad();
  }, []);

  const loadCatalogos = async () => {
    try {
      const [lotesRes, tiposRes] = await Promise.allSettled([lotesApi.list(), catalogosApi.tipoActividades()]);
      setLotes(lotesRes.status === 'fulfilled' ? (lotesRes.value.data?.data || lotesRes.value.data || []) : []);
      setTipos(tiposRes.status === 'fulfilled' ? (tiposRes.value.data?.data || tiposRes.value.data || []) : []);
    } catch (e) {}
  };

  const loadActividad = async () => {
    try {
      const res = await actividadesApi.get(id);
      const a = res.data?.data || res.data;
      setForm({ descripcion: a.descripcion || '', loteid: String(a.loteid || ''), tipoactividadid: String(a.tipoactividadid || ''), fechaactividad: a.fechaactividad || '' });
    } catch (e) {}
  };

  const updateField = (field, value) => setForm(prev => ({ ...prev, [field]: value }));

  const handleSave = async () => {
    if (!form.descripcion) { Alert.alert('Error', 'La descripción es obligatoria'); return; }
    setLoading(true);
    try {
      const data = { ...form, loteid: form.loteid ? parseInt(form.loteid) : null, tipoactividadid: form.tipoactividadid ? parseInt(form.tipoactividadid) : null };
      if (isEdit) { await actividadesApi.update(id, data); } else { await actividadesApi.create(data); }
      navigation.goBack();
    } catch (e) {
      Alert.alert('Error', e.response?.data?.message || 'No se pudo guardar');
    } finally { setLoading(false); }
  };

  return (
    <KeyboardAvoidingView style={styles.container} behavior={Platform.OS === 'ios' ? 'padding' : 'height'}>
      <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
        <FormInput label="Descripción *" value={form.descripcion} onChangeText={(v) => updateField('descripcion', v)} placeholder="Describe la actividad" multiline />
        <FormInput label="Fecha" icon="calendar-outline" value={form.fechaactividad} onChangeText={(v) => updateField('fechaactividad', v)} placeholder="YYYY-MM-DD" />

        <Text style={styles.label}>Lote</Text>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipRow}>
          {lotes.map(l => (
            <TouchableOpacity key={l.loteid} style={[styles.chip, String(l.loteid) === form.loteid && styles.chipActive]} onPress={() => updateField('loteid', String(l.loteid))}>
              <Text style={[styles.chipText, String(l.loteid) === form.loteid && styles.chipTextActive]}>{l.nombre}</Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        <Text style={styles.label}>Tipo de Actividad</Text>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipRow}>
          {tipos.map(t => (
            <TouchableOpacity key={t.tipoactividadid} style={[styles.chip, String(t.tipoactividadid) === form.tipoactividadid && styles.chipActive]} onPress={() => updateField('tipoactividadid', String(t.tipoactividadid))}>
              <Text style={[styles.chipText, String(t.tipoactividadid) === form.tipoactividadid && styles.chipTextActive]}>{t.nombre}</Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        <TouchableOpacity style={[styles.saveButton, loading && { opacity: 0.6 }]} onPress={handleSave} disabled={loading}>
          <Text style={styles.saveButtonText}>{loading ? 'Guardando...' : isEdit ? 'Actualizar' : 'Crear Actividad'}</Text>
        </TouchableOpacity>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  scroll: { padding: 16, paddingBottom: 32 },
  label: { fontSize: 14, fontWeight: '600', color: Colors.text, marginBottom: 8 },
  chipRow: { marginBottom: 16, maxHeight: 44 },
  chip: { paddingHorizontal: 16, paddingVertical: 10, borderRadius: 20, backgroundColor: Colors.background, borderWidth: 1, borderColor: Colors.border, marginRight: 8 },
  chipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
  chipText: { fontSize: 13, color: Colors.textSecondary },
  chipTextActive: { color: '#FFF' },
  saveButton: { backgroundColor: Colors.primary, borderRadius: 12, paddingVertical: 16, alignItems: 'center', marginTop: 16 },
  saveButtonText: { color: '#FFF', fontSize: 16, fontWeight: '600' },
});
