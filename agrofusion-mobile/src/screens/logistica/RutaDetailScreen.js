import React, { useState, useCallback } from 'react';
import {
  View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert,
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../context/AuthContext';
import { isTransportista } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockRutaById } from '../../data/mockWorkersData';
import {
  verificarMockConexionVehiculo,
  verificarMockCondicionesVehiculo,
  iniciarMockRuta,
  completeMockParada,
  puedeIniciarMockRuta,
} from '../../data/mockRoleActions';
import LoadingSpinner from '../../components/LoadingSpinner';
import StatusBadge from '../../components/StatusBadge';
import { Colors } from '../../constants/colors';
import { formatDateTime } from '../../utils/helpers';

function conexionLabel(estado) {
  if (estado === 'online') return 'Conectado';
  if (estado === 'debil') return 'Señal débil';
  return 'Sin conexión';
}

function conexionColor(estado) {
  if (estado === 'online') return Colors.success;
  if (estado === 'debil') return Colors.warning;
  return Colors.error;
}

export default function RutaDetailScreen({ route }) {
  const { id } = route.params;
  const { user } = useAuth();
  const esTransportista = isTransportista(user);
  const [ruta, setRuta] = useState(null);
  const [loading, setLoading] = useState(true);

  const loadRuta = useCallback(() => {
    setLoading(true);
    if (USE_MOCK_DATA) {
      const found = getMockRutaById(id);
      setRuta(found ? { ...found } : null);
    }
    setLoading(false);
  }, [id]);

  useFocusEffect(useCallback(() => { loadRuta(); }, [loadRuta]));

  const handleVerificarConexion = () => {
    if (!USE_MOCK_DATA) return;
    verificarMockConexionVehiculo(id);
    loadRuta();
    const updated = getMockRutaById(id);
    const msg = updated?.conexion_vehiculo === 'offline'
      ? 'El vehículo no tiene señal GPS activa. Los avances se guardarán en el dispositivo.'
      : updated?.conexion_vehiculo === 'debil'
        ? 'Señal GPS débil detectada. Puede iniciar con precaución.'
        : 'Vehículo conectado y listo para trazabilidad en ruta.';
    Alert.alert('Conexión del vehículo', msg);
  };

  const handleVerificarCondiciones = () => {
    Alert.alert(
      'Condiciones del vehículo',
      'Marque el estado general antes de salir (como en cierre operativo web)',
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: 'Revisado con observaciones',
          onPress: () => {
            if (USE_MOCK_DATA) {
              verificarMockCondicionesVehiculo(id, 'revisado');
              loadRuta();
            }
          },
        },
        {
          text: 'Perfecto',
          onPress: () => {
            if (USE_MOCK_DATA) {
              verificarMockCondicionesVehiculo(id, 'perfecto');
              loadRuta();
            }
          },
        },
      ],
    );
  };

  const handleIniciarRuta = () => {
    const check = puedeIniciarMockRuta(ruta);
    if (!check.ok) {
      Alert.alert('No se puede iniciar', check.motivo);
      return;
    }
    const iniciar = () => {
      if (USE_MOCK_DATA) {
        const result = iniciarMockRuta(id);
        if (result?.error) {
          Alert.alert('No se puede iniciar', result.error);
          return;
        }
        loadRuta();
        Alert.alert('Ruta iniciada', 'Multiruta en curso. Puede registrar paradas.');
      }
    };
    if (check.conexion === 'offline' || check.conexion === 'debil') {
      Alert.alert(
        'Conexión limitada',
        'El vehículo tiene señal GPS limitada. ¿Desea iniciar la ruta de todos modos?',
        [
          { text: 'Cancelar', style: 'cancel' },
          { text: 'Iniciar igual', onPress: iniciar },
        ],
      );
    } else {
      Alert.alert('Iniciar multiruta', '¿Comenzar el recorrido con trazabilidad activa?', [
        { text: 'Cancelar', style: 'cancel' },
        { text: 'Iniciar', onPress: iniciar },
      ]);
    }
  };

  const handleCompleteParada = (parada) => {
    Alert.alert('Completar parada', `¿Marcar "${parada.nombre}" como visitada?`, [
      { text: 'Cancelar', style: 'cancel' },
      {
        text: 'Completar',
        onPress: () => {
          if (USE_MOCK_DATA) {
            completeMockParada(id, parada.paradaid);
            loadRuta();
          }
        },
      },
    ]);
  };

  if (loading) return <LoadingSpinner fullScreen message="Cargando ruta..." />;
  if (!ruta) {
    return (
      <View style={styles.container}>
        <Text style={styles.errorText}>No se encontró la ruta</Text>
      </View>
    );
  }

  const completadas = ruta.paradas?.filter(p => p.completada).length || 0;
  const total = ruta.paradas?.length || 0;
  const pendiente = ruta.estado === 'pendiente';
  const enRuta = ruta.estado === 'en_ruta';

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>{ruta.nombre}</Text>
        <StatusBadge status={ruta.estado || 'pendiente'} />
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Vehículo asignado</Text>
        <InfoRow icon="car-outline" label="Unidad" value={ruta.vehiculo ? `${ruta.vehiculo.placa} · ${ruta.vehiculo.nombre}` : '—'} />
        <InfoRow
          icon="wifi-outline"
          label="Conexión GPS"
          value={conexionLabel(ruta.conexion_vehiculo)}
          valueColor={conexionColor(ruta.conexion_vehiculo)}
        />
        <InfoRow icon="time-outline" label="Última señal" value={formatDateTime(ruta.ultima_senal) || '—'} />
        <InfoRow
          icon="clipboard-outline"
          label="Condiciones"
          value={ruta.condiciones_verificadas ? (ruta.condiciones_estado === 'perfecto' ? 'Perfecto' : 'Revisado') : 'Pendiente de verificar'}
        />
        {esTransportista && USE_MOCK_DATA && pendiente && (
          <View style={styles.checkRow}>
            <TouchableOpacity style={styles.checkBtn} onPress={handleVerificarConexion}>
              <Ionicons name="refresh-outline" size={18} color={Colors.primary} />
              <Text style={styles.checkBtnText}>Verificar conexión</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.checkBtn} onPress={handleVerificarCondiciones}>
              <Ionicons name="clipboard-outline" size={18} color={Colors.primary} />
              <Text style={styles.checkBtnText}>Condiciones vehículo</Text>
            </TouchableOpacity>
          </View>
        )}
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Paradas ({completadas}/{total})</Text>
        {ruta.paradas?.map((p) => (
          <TouchableOpacity
            key={p.paradaid}
            style={styles.paradaItem}
            disabled={p.completada || !enRuta || !USE_MOCK_DATA || !esTransportista}
            onPress={() => handleCompleteParada(p)}
            activeOpacity={0.7}
          >
            <Ionicons
              name={p.completada ? 'checkmark-circle' : 'ellipse-outline'}
              size={18}
              color={p.completada ? Colors.success : Colors.textMuted}
            />
            <View style={{ flex: 1 }}>
              <Text style={[styles.paradaNombre, p.completada && styles.paradaDone]}>{p.nombre}</Text>
              <Text style={styles.paradaDir}>{p.direccion}</Text>
            </View>
          </TouchableOpacity>
        ))}
        {pendiente && (
          <Text style={styles.hint}>Verifique conexión y condiciones del vehículo antes de iniciar.</Text>
        )}
      </View>

      {esTransportista && USE_MOCK_DATA && pendiente && (
        <View style={styles.actions}>
          <TouchableOpacity style={styles.actionBtn} onPress={handleIniciarRuta}>
            <Ionicons name="play-outline" size={20} color="#FFF" />
            <Text style={styles.actionBtnText}>Iniciar multiruta</Text>
          </TouchableOpacity>
        </View>
      )}
    </ScrollView>
  );
}

const InfoRow = ({ icon, label, value, valueColor }) => (
  <View style={styles.infoRow}>
    <Ionicons name={icon} size={18} color={Colors.primary} />
    <View style={styles.infoContent}>
      <Text style={styles.infoLabel}>{label}</Text>
      <Text style={[styles.infoValue, valueColor && { color: valueColor }]}>{value}</Text>
    </View>
  </View>
);

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  header: {
    backgroundColor: Colors.primary, padding: 24, paddingBottom: 20,
    borderBottomWidth: 1, borderBottomColor: Colors.primaryDark,
  },
  title: { fontSize: 22, fontWeight: '700', color: '#FFF', marginBottom: 8 },
  errorText: { padding: 24, fontSize: 16, color: Colors.textSecondary },
  section: {
    backgroundColor: Colors.surface, margin: 12, borderRadius: 16, padding: 16,
    borderWidth: 1, borderColor: Colors.border,
  },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.text, marginBottom: 12 },
  infoRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: Colors.border },
  infoContent: { marginLeft: 12, flex: 1 },
  infoLabel: { fontSize: 12, color: Colors.textSecondary },
  infoValue: { fontSize: 15, color: Colors.text, fontWeight: '500' },
  checkRow: { flexDirection: 'row', gap: 8, marginTop: 12 },
  checkBtn: {
    flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6,
    padding: 12, borderRadius: 10, backgroundColor: Colors.primaryLight,
    borderWidth: 1, borderColor: Colors.border,
  },
  checkBtnText: { fontSize: 12, fontWeight: '600', color: Colors.primary },
  paradaItem: {
    flexDirection: 'row', alignItems: 'flex-start', gap: 10,
    paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: Colors.border,
  },
  paradaNombre: { fontSize: 14, fontWeight: '600', color: Colors.text },
  paradaDir: { fontSize: 12, color: Colors.textMuted, marginTop: 2 },
  paradaDone: { textDecorationLine: 'line-through', color: Colors.textMuted },
  hint: { fontSize: 12, color: Colors.textMuted, marginTop: 10, fontStyle: 'italic' },
  actions: { padding: 16, paddingBottom: 32 },
  actionBtn: {
    flexDirection: 'row', backgroundColor: Colors.primary, padding: 14, borderRadius: 10,
    justifyContent: 'center', alignItems: 'center', gap: 8,
  },
  actionBtnText: { color: '#FFF', fontWeight: '600', fontSize: 15 },
});
