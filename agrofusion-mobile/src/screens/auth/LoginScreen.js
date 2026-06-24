import React, { useState } from 'react';
import {
  View, Text, StyleSheet, TouchableOpacity, Alert, KeyboardAvoidingView,
  Platform, ScrollView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../context/AuthContext';
import FormInput from '../../components/FormInput';
import { Colors } from '../../constants/colors';

const DEMO_PREVIEWS = [
  { key: 'agricultor', label: 'Agricultor de campo', icon: 'leaf-outline' },
  { key: 'planta', label: 'Operador de planta', icon: 'cog-outline' },
  { key: 'transportista', label: 'Transportista', icon: 'car-sport-outline' },
  { key: 'minorista', label: 'Minorista', icon: 'storefront-outline' },
  { key: 'mayorista', label: 'Mayorista', icon: 'warehouse-outline' },
];

export default function LoginScreen({ navigation }) {
  const { login, loginDemo, showDemoLogin } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  const handleLogin = async () => {
    if (!email.trim() || !password.trim()) {
      Alert.alert('Error', 'Por favor ingresa tu correo y contraseña');
      return;
    }
    setLoading(true);
    try {
      await login(email.trim(), password);
    } catch (error) {
      const msg = error.response?.data?.message || 'Credenciales incorrectas';
      Alert.alert('Error de inicio de sesión', msg);
    } finally {
      setLoading(false);
    }
  };

  const handleDemoLogin = async (roleKey) => {
    setLoading(true);
    try {
      await loginDemo(roleKey);
    } finally {
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView style={styles.container} behavior={Platform.OS === 'ios' ? 'padding' : 'height'}>
      <ScrollView contentContainerStyle={styles.scrollContent} keyboardShouldPersistTaps="handled">
        <View style={styles.header}>
          <View style={styles.logoContainer}>
            <Ionicons name="leaf" size={36} color="#FFF" />
          </View>
          <Text style={styles.appName}>AgroFusion</Text>
          <Text style={styles.subtitle}>La nueva generación de trazabilidad agrícola</Text>
          <TouchableOpacity
            style={styles.storyLink}
            onPress={() => navigation.navigate('PlataformaPresentacion')}
            activeOpacity={0.8}
          >
            <Ionicons name="play-circle-outline" size={18} color={Colors.primary} />
            <Text style={styles.storyLinkText}>Conoce la plataforma</Text>
          </TouchableOpacity>
        </View>

        <View style={styles.formContainer}>
          <Text style={styles.welcomeTitle}>Bienvenido</Text>
          <Text style={styles.welcomeSubtitle}>Ingresa tus credenciales para acceder</Text>

          <View style={styles.form}>
            <FormInput
              label="Correo electrónico"
              icon="mail-outline"
              placeholder="tu@correo.com"
              value={email}
              onChangeText={setEmail}
              keyboardType="email-address"
              autoCapitalize="none"
              autoCorrect={false}
            />

            <FormInput
              label="Contraseña"
              icon="lock-closed-outline"
              placeholder="••••••••"
              value={password}
              onChangeText={setPassword}
              secureTextEntry={!showPassword}
            />

            <TouchableOpacity style={styles.togglePassword} onPress={() => setShowPassword(!showPassword)}>
              <Ionicons name={showPassword ? 'eye-off-outline' : 'eye-outline'} size={18} color={Colors.textSecondary} />
              <Text style={styles.toggleText}>{showPassword ? 'Ocultar' : 'Mostrar'} contraseña</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={[styles.loginButton, loading && styles.loginButtonDisabled]}
              onPress={handleLogin}
              disabled={loading}
            >
              <Ionicons name="log-in-outline" size={20} color="#FFF" />
              <Text style={styles.loginButtonText}>{loading ? 'Ingresando...' : 'Iniciar Sesión'}</Text>
            </TouchableOpacity>

            {showDemoLogin && (
              <View style={styles.demoSection}>
                <Text style={styles.demoSectionTitle}>Vistas previa de diseño</Text>
                {DEMO_PREVIEWS.map((item) => (
                  <TouchableOpacity
                    key={item.key}
                    style={styles.demoButton}
                    onPress={() => handleDemoLogin(item.key)}
                    disabled={loading}
                  >
                    <Ionicons name={item.icon} size={18} color={Colors.primary} />
                    <Text style={styles.demoButtonText}>{item.label}</Text>
                  </TouchableOpacity>
                ))}
              </View>
            )}

            <TouchableOpacity style={styles.registerLink} onPress={() => navigation.navigate('Register')}>
              <Text style={styles.registerText}>¿No tienes cuenta? </Text>
              <Text style={styles.registerTextBold}>Regístrate aquí</Text>
            </TouchableOpacity>
          </View>
        </View>

        <Text style={styles.footerText}>© {new Date().getFullYear()} AgroFusion · Tecnología para el campo</Text>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  scrollContent: { flexGrow: 1, justifyContent: 'center', padding: 24 },
  header: { alignItems: 'center', marginBottom: 40 },
  logoContainer: {
    width: 72, height: 72, borderRadius: 16,
    backgroundColor: Colors.primary,
    justifyContent: 'center', alignItems: 'center', marginBottom: 16,
    borderWidth: 1, borderColor: Colors.border,
  },
  appName: { fontSize: 28, fontWeight: 'bold', color: Colors.text },
  subtitle: { fontSize: 13, color: Colors.textSecondary, marginTop: 4, textAlign: 'center' },
  storyLink: {
    flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 14,
    paddingHorizontal: 16, paddingVertical: 10, borderRadius: 10,
    backgroundColor: Colors.primaryLight, borderWidth: 1, borderColor: Colors.border,
  },
  storyLinkText: { fontSize: 14, color: Colors.primary, fontWeight: '600' },
  formContainer: {
    backgroundColor: Colors.surface, borderRadius: 20, padding: 24,
    borderWidth: 1, borderColor: Colors.border,
  },
  welcomeTitle: { fontSize: 22, fontWeight: 'bold', color: Colors.text, marginBottom: 4 },
  welcomeSubtitle: { fontSize: 14, color: Colors.textSecondary, marginBottom: 24 },
  form: { width: '100%' },
  togglePassword: { flexDirection: 'row', alignItems: 'center', marginBottom: 24, alignSelf: 'flex-end' },
  toggleText: { fontSize: 13, color: Colors.textSecondary, marginLeft: 4 },
  loginButton: {
    flexDirection: 'row', backgroundColor: Colors.primary, borderRadius: 12,
    paddingVertical: 16, alignItems: 'center', justifyContent: 'center', marginBottom: 12, gap: 8,
  },
  loginButtonDisabled: { opacity: 0.6 },
  loginButtonText: { color: '#FFF', fontSize: 16, fontWeight: '600' },
  demoSection: { marginBottom: 16, gap: 8 },
  demoSectionTitle: {
    fontSize: 12, fontWeight: '600', color: Colors.textMuted,
    textTransform: 'uppercase', letterSpacing: 0.5, marginBottom: 4,
  },
  demoButton: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8,
    paddingVertical: 12, borderRadius: 12,
    borderWidth: 1, borderColor: Colors.border, backgroundColor: Colors.primaryLight,
  },
  demoButtonText: { color: Colors.primary, fontSize: 14, fontWeight: '600' },
  registerLink: { flexDirection: 'row', justifyContent: 'center', padding: 8 },
  registerText: { fontSize: 14, color: Colors.textSecondary },
  registerTextBold: { fontSize: 14, color: Colors.primary, fontWeight: '600' },
  footerText: { textAlign: 'center', color: Colors.textMuted, fontSize: 12, marginTop: 24 },
});
