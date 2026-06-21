import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert, KeyboardAvoidingView, Platform } from 'react-native';
import { insumosApi, catalogosApi } from '../../api/client';
import FormInput from '../../components/FormInput';
import { Colors } from '../../constants/colors';

export default function InsumoFormScreen({ route, navigation }) {
  const { id } = route.params || {};
  const isEdit = !!id;
  const [form, setForm] = useState({ nombre: '', descripcion: '', stock: '', tipoinsumoid: '', unidadmedidaid: '' });
  const [tipos, setTipos] = useState([]);
  const [unidades, setUnidades] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    loadCatalogos();
    if (isEdit) loadInsumo();
  }, []);

  const loadCatalogos = async () => {
    try {
      const [tiposRes, unidadesRes] = await Promise.allSettled([catalogosApi.tipoInsumos(), catalogosApi.unidadesMedida()]);
      setTipos(tiposRes.status === 'fulfilled' ? (tiposRes.value.data?.data || tiposRes.value.data || []) : []);
      setUnidades(unidadesRes.status === 'fulfilled' ? (unidadesRes.value.data?.data || unidadesRes.value.data || []) : []);
    } catch (e) {}
  };

  const loadInsumo = async () => {
    try {
      const res = await insumosApi.get(id);
      const i = res.data?.data || res.data;
      setForm({ nombre: i.nombre || '', descripcion: i.descripcion || '', stock: String(i.stock || ''), tipoinsumoid: String(i.tipoinsumoid || ''), unidadmedidaid: String(i.unidadmedidaid || '') });
    } catch (e) {}
  };

  const updateField = (field, value) => setForm(prev => ({ ...prev, [field]: value }));

  const handleSave = async () => {
    if (!form.nombre) { Alert.alert('Error', 'El nombre es obligatorio'); return; }
    setLoading(true);
    try {
      const data = { ...form, stock: form.stock ? parseFloat(form.stock) : null, tipoinsumoid: form.tipoinsumoid ? parseInt(form.tipoinsumoid) : null, unidadmedidaid: form.unidadmedidaid ? parseInt(form.unidadmedidaid) : null };
      if (isEdit) { await insumosApi.update(id, data); } else { await insumosApi.create(data); }
      navigation.goBack();
    } catch (e) {
      Alert.alert('Error', e.response?.data?.message || 'No se pudo guardar');
    } finally { setLoading(false); }
  };

  return (
    <KeyboardAvoidingView style={styles.container} behavior={Platform.OS === 'ios' ? 'padding' : 'height'}>
      <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
        <FormInput label="Nombre *" icon="flask-outline" value={form.nombre} onChangeText={(v) => updateField('nombre', v)} placeholder="Nombre del insumo" />
        <FormInput label="Descripción" value={form.descripcion} onChangeText={(v) => updateField('descripcion', v)} placeholder="Descripción" multiline />
        <FormInput label="Stock" icon="cube-outline" value={form.stock} onChangeText={(v) => updateField('stock', v)} placeholder="0.00" keyboardType="numeric" />

        <Text style={styles.label}>Tipo de Insumo</Text>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipRow}>
          {tipos.map(t => (
            <TouchableOpacity key={t.tipoinsumoid} style={[styles.chip, String(t.tipoinsumoid) === form.tipoinsumoid && styles.chipActive]} onPress={() => updateField('tipoinsumoid', String(t.tipoinsumoid))}>
              <Text style={[styles.chipText, String(t.tipoinsumoid) === form.tipoinsumoid && styles.chipTextActive]}>{t.nombre}</Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        <Text style={styles.label}>Unidad de Medida</Text>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipRow}>
          {unidades.map(u => (
            <TouchableOpacity key={u.unidadmedidaid} style={[styles.chip, String(u.unidadmedidaid) === form.unidadmedidaid && styles.chipActive]} onPress={() => updateField('unidadmedidaid', String(u.unidadmedidaid))}>
              <Text style={[styles.chipText, String(u.unidadmedidaid) === form.unidadmedidaid && styles.chipTextActive]}>{u.nombre}</Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        <TouchableOpacity style={[styles.saveButton, loading && { opacity: 0.6 }]} onPress={handleSave} disabled={loading}>
          <Text style={styles.saveButtonText}>{loading ? 'Guardando...' : isEdit ? 'Actualizar' : 'Crear Insumo'}</Text>
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
