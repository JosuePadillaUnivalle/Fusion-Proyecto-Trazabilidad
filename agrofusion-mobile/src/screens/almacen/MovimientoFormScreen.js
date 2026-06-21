import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert, KeyboardAvoidingView, Platform } from 'react-native';
import { movimientosApi, almacenesApi } from '../../api/client';
import FormInput from '../../components/FormInput';
import { Colors } from '../../constants/colors';

export default function MovimientoFormScreen({ route, navigation }) {
  const { naturaleza = 'ingreso' } = route.params || {};
  const [form, setForm] = useState({ cantidad: '', almacenid: '', observaciones: '' });
  const [almacenes, setAlmacenes] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => { loadAlmacenes(); }, []);

  const loadAlmacenes = async () => {
    try { const res = await almacenesApi.list(); setAlmacenes(res.data?.data || res.data || []); } catch (e) {}
  };

  const updateField = (field, value) => setForm(prev => ({ ...prev, [field]: value }));

  const handleSave = async () => {
    if (!form.cantidad || !form.almacenid) { Alert.alert('Error', 'Completa todos los campos obligatorios'); return; }
    setLoading(true);
    try {
      await movimientosApi.create({ ...form, naturaleza, cantidad: parseFloat(form.cantidad), almacenid: parseInt(form.almacenid) });
      navigation.goBack();
    } catch (e) {
      Alert.alert('Error', e.response?.data?.message || 'No se pudo registrar');
    } finally { setLoading(false); }
  };

  return (
    <KeyboardAvoidingView style={styles.container} behavior={Platform.OS === 'ios' ? 'padding' : 'height'}>
      <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
        <View style={[styles.naturalezaBadge, { backgroundColor: naturaleza === 'egreso' ? Colors.error + '20' : Colors.success + '20' }]}>
          <Text style={[styles.naturalezaText, { color: naturaleza === 'egreso' ? Colors.error : Colors.success }]}>
            {naturaleza === 'egreso' ? 'Egreso' : 'Ingreso'}
          </Text>
        </View>

        <FormInput label="Cantidad *" icon="calculator-outline" value={form.cantidad} onChangeText={(v) => updateField('cantidad', v)} placeholder="0.00" keyboardType="numeric" />

        <Text style={styles.label}>Almacén *</Text>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipRow}>
          {almacenes.map(a => (
            <TouchableOpacity key={a.almacenid} style={[styles.chip, String(a.almacenid) === form.almacenid && styles.chipActive]} onPress={() => updateField('almacenid', String(a.almacenid))}>
              <Text style={[styles.chipText, String(a.almacenid) === form.almacenid && styles.chipTextActive]}>{a.nombre}</Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        <FormInput label="Observaciones" value={form.observaciones} onChangeText={(v) => updateField('observaciones', v)} placeholder="Notas adicionales" multiline />

        <TouchableOpacity style={[styles.saveButton, loading && { opacity: 0.6 }]} onPress={handleSave} disabled={loading}>
          <Text style={styles.saveButtonText}>{loading ? 'Guardando...' : `Registrar ${naturaleza}`}</Text>
        </TouchableOpacity>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  scroll: { padding: 16, paddingBottom: 32 },
  naturalezaBadge: { alignSelf: 'flex-start', paddingHorizontal: 16, paddingVertical: 8, borderRadius: 20, marginBottom: 16 },
  naturalezaText: { fontSize: 14, fontWeight: '600' },
  label: { fontSize: 14, fontWeight: '600', color: Colors.text, marginBottom: 8 },
  chipRow: { marginBottom: 16, maxHeight: 44 },
  chip: { paddingHorizontal: 16, paddingVertical: 10, borderRadius: 20, backgroundColor: Colors.background, borderWidth: 1, borderColor: Colors.border, marginRight: 8 },
  chipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
  chipText: { fontSize: 13, color: Colors.textSecondary },
  chipTextActive: { color: '#FFF' },
  saveButton: { backgroundColor: Colors.primary, borderRadius: 12, paddingVertical: 16, alignItems: 'center', marginTop: 16 },
  saveButtonText: { color: '#FFF', fontSize: 16, fontWeight: '600' },
});
