import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert, KeyboardAvoidingView, Platform } from 'react-native';
import { produccionesApi, lotesApi } from '../../api/client';
import FormInput from '../../components/FormInput';
import { Colors } from '../../constants/colors';

export default function ProduccionFormScreen({ route, navigation }) {
  const { id } = route.params || {};
  const isEdit = !!id;
  const [form, setForm] = useState({ cantidad: '', loteid: '', fechaproduccion: '', observaciones: '' });
  const [lotes, setLotes] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    loadLotes();
    if (isEdit) loadProduccion();
  }, []);

  const loadLotes = async () => {
    try { const res = await lotesApi.list(); setLotes(res.data?.data || res.data || []); } catch (e) {}
  };

  const loadProduccion = async () => {
    try {
      const res = await produccionesApi.get(id);
      const p = res.data?.data || res.data;
      setForm({ cantidad: String(p.cantidad || ''), loteid: String(p.loteid || ''), fechaproduccion: p.fechaproduccion || '', observaciones: p.observaciones || '' });
    } catch (e) {}
  };

  const updateField = (field, value) => setForm(prev => ({ ...prev, [field]: value }));

  const handleSave = async () => {
    if (!form.cantidad) { Alert.alert('Error', 'La cantidad es obligatoria'); return; }
    setLoading(true);
    try {
      const data = { ...form, cantidad: parseFloat(form.cantidad), loteid: form.loteid ? parseInt(form.loteid) : null };
      if (isEdit) { await produccionesApi.update(id, data); } else { await produccionesApi.create(data); }
      navigation.goBack();
    } catch (e) {
      Alert.alert('Error', e.response?.data?.message || 'No se pudo guardar');
    } finally { setLoading(false); }
  };

  return (
    <KeyboardAvoidingView style={styles.container} behavior={Platform.OS === 'ios' ? 'padding' : 'height'}>
      <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
        <FormInput label="Cantidad *" icon="calculator-outline" value={form.cantidad} onChangeText={(v) => updateField('cantidad', v)} placeholder="0.00" keyboardType="numeric" />
        <FormInput label="Fecha de producción" icon="calendar-outline" value={form.fechaproduccion} onChangeText={(v) => updateField('fechaproduccion', v)} placeholder="YYYY-MM-DD" />

        <Text style={styles.label}>Lote</Text>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipRow}>
          {lotes.map(l => (
            <TouchableOpacity key={l.loteid} style={[styles.chip, String(l.loteid) === form.loteid && styles.chipActive]} onPress={() => updateField('loteid', String(l.loteid))}>
              <Text style={[styles.chipText, String(l.loteid) === form.loteid && styles.chipTextActive]}>{l.nombre}</Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        <FormInput label="Observaciones" value={form.observaciones} onChangeText={(v) => updateField('observaciones', v)} placeholder="Notas adicionales" multiline />

        <TouchableOpacity style={[styles.saveButton, loading && { opacity: 0.6 }]} onPress={handleSave} disabled={loading}>
          <Text style={styles.saveButtonText}>{loading ? 'Guardando...' : isEdit ? 'Actualizar' : 'Registrar Cosecha'}</Text>
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
