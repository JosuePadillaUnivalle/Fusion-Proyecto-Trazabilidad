import React, { useState } from 'react';
import {
  View, Text, StyleSheet, TouchableOpacity, Alert, KeyboardAvoidingView,
  Platform, ScrollView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../context/AuthContext';
import FormInput from '../../components/FormInput';
import { Colors } from '../../constants/colors';

export default function LoginScreen({ navigation }) {
  const { login } = useAuth();
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

  return (
    <KeyboardAvoidingView style={styles.container} behavior={Platform.OS === 'ios' ? 'padding' : 'height'}>
      <ScrollView contentContainerStyle={styles.scrollContent} keyboardShouldPersistTaps="handled">
        <View style={styles.header}>
          <View style={styles.logoContainer}>
            <Ionicons name="leaf" size={40} color="#FFF" />
          </View>
          <Text style={styles.appName}>AgroFusion</Text>
          <Text style={styles.subtitle}>Sistema integral de gestión agrícola</Text>
        </View>

        <View style={styles.formContainer}>
          <Text style={styles.welcomeTitle}>¡Bienvenido!</Text>
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
  container: { flex: 1, backgroundColor: '#080d0f' },
  scrollContent: { flexGrow: 1, justifyContent: 'center', padding: 24 },
  header: { alignItems: 'center', marginBottom: 40 },
  logoContainer: {
    width: 72, height: 72, borderRadius: 16,
    backgroundColor: Colors.primary,
    justifyContent: 'center', alignItems: 'center', marginBottom: 16,
    shadowColor: Colors.primary, shadowOffset: { width: 0, height: 0 }, shadowOpacity: 0.5, shadowRadius: 12, elevation: 8,
  },
  appName: { fontSize: 28, fontWeight: 'bold', color: '#FFF' },
  subtitle: { fontSize: 13, color: 'rgba(255,255,255,0.6)', marginTop: 4 },
  formContainer: {
    backgroundColor: 'rgba(255,255,255,0.05)',
    borderRadius: 20, padding: 24,
    borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)',
  },
  welcomeTitle: { fontSize: 22, fontWeight: 'bold', color: '#FFF', marginBottom: 4 },
  welcomeSubtitle: { fontSize: 14, color: 'rgba(255,255,255,0.6)', marginBottom: 24 },
  form: { width: '100%' },
  togglePassword: { flexDirection: 'row', alignItems: 'center', marginBottom: 24, alignSelf: 'flex-end' },
  toggleText: { fontSize: 13, color: 'rgba(255,255,255,0.6)', marginLeft: 4 },
  loginButton: {
    flexDirection: 'row', backgroundColor: Colors.primary, borderRadius: 12,
    paddingVertical: 16, alignItems: 'center', justifyContent: 'center', marginBottom: 16, gap: 8,
  },
  loginButtonDisabled: { opacity: 0.6 },
  loginButtonText: { color: '#FFF', fontSize: 16, fontWeight: '600' },
  registerLink: { flexDirection: 'row', justifyContent: 'center', padding: 8 },
  registerText: { fontSize: 14, color: 'rgba(255,255,255,0.6)' },
  registerTextBold: { fontSize: 14, color: Colors.primary, fontWeight: '600' },
  footerText: { textAlign: 'center', color: 'rgba(255,255,255,0.4)', fontSize: 12, marginTop: 24 },
});
