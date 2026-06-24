import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl, TouchableOpacity } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { rutasApi } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { isTransportista } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockRutasTransportista } from '../../data/mockWorkersData';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

function conexionBadge(estado) {
  if (estado === 'online') return { label: 'GPS conectado', color: Colors.success };
  if (estado === 'debil') return { label: 'Señal débil', color: Colors.warning };
  if (estado === 'offline') return { label: 'Sin conexión', color: Colors.error };
  return null;
}

export default function RutasScreen({ navigation }) {
  const { user } = useAuth();
  const esTransportista = isTransportista(user);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esTransportista) {
        setData(getMockRutasTransportista(user?.usuarioid));
      } else {
        const res = await rutasApi.list();
        setData(res.data?.data || res.data || []);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esTransportista) setData(getMockRutasTransportista(user?.usuarioid));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { loadData(); }, []));

  const renderItem = ({ item }) => {
    const completadas = item.paradas?.filter(p => p.completada).length || 0;
    const total = item.paradas?.length || 0;
    const conn = conexionBadge(item.conexion_vehiculo);

    return (
      <TouchableOpacity
        activeOpacity={0.8}
        onPress={() => navigation.navigate('RutaDetail', { id: item.rutamultientregaid || item.id })}
      >
        <Card
          title={item.nombre || `Ruta #${item.rutamultientregaid || item.id}`}
          subtitle={item.descripcion || ''}
          icon="git-branch-outline"
          iconColor={Colors.primary}
          rightElement={<StatusBadge status={item.estado || 'pendiente'} />}
        >
          <View style={styles.cardBody}>
            <View style={styles.infoRow}>
              <Ionicons name="calendar-outline" size={16} color={Colors.textSecondary} />
              <Text style={styles.infoText}>{formatDate(item.fecharegistro)}</Text>
            </View>
            {item.vehiculo && (
              <View style={styles.infoRow}>
                <Ionicons name="car-outline" size={16} color={Colors.textSecondary} />
                <Text style={styles.infoText}>{item.vehiculo.placa} · {item.vehiculo.nombre}</Text>
              </View>
            )}
            {conn && (
              <View style={styles.infoRow}>
                <Ionicons name="wifi-outline" size={16} color={conn.color} />
                <Text style={[styles.infoText, { color: conn.color }]}>{conn.label}</Text>
              </View>
            )}
            {total > 0 && (
              <View style={styles.infoRow}>
                <Ionicons name="pin-outline" size={16} color={Colors.textSecondary} />
                <Text style={styles.infoText}>Paradas: {completadas}/{total}</Text>
              </View>
            )}
            {item.estado === 'pendiente' && (
              <Text style={styles.hint}>Toque para verificar vehículo e iniciar ruta</Text>
            )}
          </View>
        </Card>
      </TouchableOpacity>
    );
  };

  if (loading) return <LoadingSpinner fullScreen message="Cargando rutas..." />;

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <Text style={styles.pageTitle}>{esTransportista ? 'Mis rutas del día' : 'Rutas'}</Text>
        <Text style={styles.pageSubtitle}>{data.length} registros</Text>
      </View>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.rutamultientregaid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="git-branch-outline" message="No hay rutas registradas" />}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  pageHeader: { padding: 16, paddingBottom: 8 },
  pageTitle: { fontSize: 22, fontWeight: '700', color: Colors.text },
  pageSubtitle: { fontSize: 13, color: Colors.textMuted, marginTop: 2 },
  list: { padding: 16 },
  cardBody: { marginTop: 12, gap: 6 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
  hint: { fontSize: 12, color: Colors.textMuted, fontStyle: 'italic', marginTop: 4 },
});
