import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Alert, KeyboardAvoidingView, Platform, ScrollView } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../context/AuthContext';
import FormInput from '../../components/FormInput';
import { Colors } from '../../constants/colors';

const ROLES_OPTIONS = [
  { value: 'agricultor', label: 'Agricultor', icon: 'leaf-outline' },
  { value: 'jefe_planta', label: 'Jefe de Planta', icon: 'business-outline' },
  { value: 'planta', label: 'Operador de Planta', icon: 'cog-outline' },
  { value: 'transportista', label: 'Transportista', icon: 'car-outline' },
  { value: 'minorista', label: 'Minorista', icon: 'storefront-outline' },
];

export default function RegisterScreen({ navigation }) {
  const { register } = useAuth();
  const [form, setForm] = useState({
    nombre: '', apellido: '', email: '', telefono: '',
    ci_nit: '', password: '', password_confirmation: '',
    rol_deseado: '', carta_motivacion: '',
  });
  const [loading, setLoading] = useState(false);

  const updateField = (field, value) => setForm(prev => ({ ...prev, [field]: value }));

  const handleRegister = async () => {
    if (!form.nombre || !form.apellido || !form.email || !form.password) {
      Alert.alert('Error', 'Por favor completa todos los campos obligatorios');
      return;
    }
    if (form.password !== form.password_confirmation) {
      Alert.alert('Error', 'Las contraseñas no coinciden');
      return;
    }
    setLoading(true);
    try {
      await register(form);
      Alert.alert('Registro exitoso', 'Tu solicitud ha sido enviada. Un administrador la revisará.', [
        { text: 'OK', onPress: () => navigation.goBack() },
      ]);
    } catch (error) {
      const msg = error.response?.data?.message || 'Error al registrar';
      Alert.alert('Error', msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView style={styles.container} behavior={Platform.OS === 'ios' ? 'padding' : 'height'}>
      <ScrollView contentContainerStyle={styles.scrollContent} keyboardShouldPersistTaps="handled">
        <View style={styles.header}>
          <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backButton}>
            <Ionicons name="arrow-back" size={24} color={Colors.text} />
          </TouchableOpacity>
          <Text style={styles.title}>Crear Cuenta</Text>
          <Text style={styles.subtitle}>Completa tus datos para registrarte</Text>
        </View>

        <View style={styles.form}>
          <View style={styles.row}>
            <FormInput label="Nombre *" icon="person-outline" value={form.nombre} onChangeText={(v) => updateField('nombre', v)} placeholder="Tu nombre" containerStyle={styles.halfInput} />
            <FormInput label="Apellido *" icon="person-outline" value={form.apellido} onChangeText={(v) => updateField('apellido', v)} placeholder="Tu apellido" containerStyle={styles.halfInput} />
          </View>

          <FormInput label="Correo electrónico *" icon="mail-outline" value={form.email} onChangeText={(v) => updateField('email', v)} placeholder="correo@ejemplo.com" keyboardType="email-address" autoCapitalize="none" />
          <FormInput label="Teléfono" icon="call-outline" value={form.telefono} onChangeText={(v) => updateField('telefono', v)} placeholder="Número de teléfono" keyboardType="phone-pad" />
          <FormInput label="CI/NIT" icon="card-outline" value={form.ci_nit} onChangeText={(v) => updateField('ci_nit', v)} placeholder="Documento de identidad" />

          <Text style={styles.sectionTitle}>Rol deseado</Text>
          <View style={styles.rolesGrid}>
            {ROLES_OPTIONS.map((role) => (
              <TouchableOpacity
                key={role.value}
                style={[styles.roleChip, form.rol_deseado === role.value && styles.roleChipActive]}
                onPress={() => updateField('rol_deseado', role.value)}
              >
                <Ionicons name={role.icon} size={18} color={form.rol_deseado === role.value ? '#FFF' : Colors.textSecondary} />
                <Text style={[styles.roleChipText, form.rol_deseado === role.value && styles.roleChipTextActive]}>{role.label}</Text>
              </TouchableOpacity>
            ))}
          </View>

          <FormInput label="Contraseña *" icon="lock-closed-outline" value={form.password} onChangeText={(v) => updateField('password', v)} placeholder="Mínimo 8 caracteres" secureTextEntry />
          <FormInput label="Confirmar contraseña *" icon="lock-closed-outline" value={form.password_confirmation} onChangeText={(v) => updateField('password_confirmation', v)} placeholder="Repite tu contraseña" secureTextEntry />

          <FormInput label="Carta de motivación" value={form.carta_motivacion} onChangeText={(v) => updateField('carta_motivacion', v)} placeholder="¿Por qué deseas unirte?" multiline numberOfLines={4} style={styles.textArea} />

          <TouchableOpacity style={[styles.registerButton, loading && { opacity: 0.6 }]} onPress={handleRegister} disabled={loading}>
            <Text style={styles.registerButtonText}>{loading ? 'Registrando...' : 'Registrarse'}</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  scrollContent: { padding: 24 },
  header: { marginBottom: 24 },
  backButton: { marginBottom: 16 },
  title: { fontSize: 28, fontWeight: 'bold', color: Colors.text },
  subtitle: { fontSize: 14, color: Colors.textSecondary, marginTop: 4 },
  form: { width: '100%' },
  row: { flexDirection: 'row', gap: 12 },
  halfInput: { flex: 1 },
  sectionTitle: { fontSize: 14, fontWeight: '600', color: Colors.text, marginBottom: 8, marginTop: 8 },
  rolesGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginBottom: 16 },
  roleChip: {
    flexDirection: 'row', alignItems: 'center', paddingHorizontal: 14, paddingVertical: 10,
    borderRadius: 20, backgroundColor: Colors.background, borderWidth: 1, borderColor: Colors.border,
  },
  roleChipActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
  roleChipText: { fontSize: 13, color: Colors.textSecondary, marginLeft: 6 },
  roleChipTextActive: { color: '#FFF' },
  textArea: { height: 100, textAlignVertical: 'top' },
  registerButton: {
    backgroundColor: Colors.primary, borderRadius: 12,
    paddingVertical: 16, alignItems: 'center', marginTop: 8, marginBottom: 32,
  },
  registerButtonText: { color: '#FFF', fontSize: 16, fontWeight: '600' },
});
