import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { enviosApi } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { isTransportista, isMayorista } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockEnviosTransportista, MOCK_TRANSPORTISTA_ID } from '../../data/mockWorkersData';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function EnviosScreen({ navigation }) {
  const { user } = useAuth();
  const esTransportista = isTransportista(user);
  const esMayorista = isMayorista(user);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esTransportista) {
        setData(getMockEnviosTransportista(user?.usuarioid));
      } else if (USE_MOCK_DATA && esMayorista) {
        setData(getMockEnviosTransportista(MOCK_TRANSPORTISTA_ID));
      } else {
        const res = await enviosApi.list();
        const all = res.data?.data || res.data || [];
        setData(esTransportista ? all.filter(e => e.usuarioid === user?.usuarioid) : all);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esTransportista) setData(getMockEnviosTransportista(user?.usuarioid));
      else if (USE_MOCK_DATA && esMayorista) setData(getMockEnviosTransportista(MOCK_TRANSPORTISTA_ID));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { loadData(); }, []));

  const renderItem = ({ item }) => (
    <TouchableOpacity
      style={styles.card}
      onPress={() => navigation.navigate('EnvioDetail', { id: item.asignacionid || item.id })}
      activeOpacity={0.8}
    >
      <View style={styles.cardHeader}>
        <View style={[styles.iconBox, { backgroundColor: Colors.infoLight }]}>
          <Ionicons name="cube-outline" size={18} color={Colors.info} />
        </View>
        <View style={{ flex: 1 }}>
          <Text style={styles.cardTitle}>{item.descripcion || `Envío #${item.asignacionid || item.id}`}</Text>
          {item.vehiculo && (
            <Text style={styles.cardSubtitle}>{item.vehiculo.placa} · {item.vehiculo.nombre}</Text>
          )}
        </View>
        <StatusBadge status={item.estado?.nombre || item.estadoenvio || 'pendiente'} />
      </View>
      <View style={styles.cardBody}>
        <Info icon="calendar-outline" text={formatDate(item.fechaenvio || item.fecharegistro)} />
        {item.paradas != null && (
          <Info icon="git-branch-outline" text={`Paradas: ${item.paradasCompletadas || 0}/${item.paradas}`} />
        )}
      </View>
    </TouchableOpacity>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando envíos..." />;

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <Text style={styles.pageTitle}>
          {esTransportista ? 'Mis envíos asignados' : esMayorista ? 'Envíos en seguimiento' : 'Envíos'}
        </Text>
        <Text style={styles.pageSubtitle}>{data.length} registros</Text>
      </View>

      <FlatList
        data={data}
        keyExtractor={(item) => String(item.asignacionid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="cube-outline" message={esTransportista ? 'No tienes envíos asignados' : 'No hay envíos registrados'} />}
      />
    </View>
  );
}

function Info({ icon, text }) {
  return (
    <View style={styles.infoRow}>
      <Ionicons name={icon} size={14} color={Colors.textMuted} />
      <Text style={styles.infoText}>{text}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  pageHeader: { padding: 16, paddingBottom: 8 },
  pageTitle: { fontSize: 22, fontWeight: '700', color: Colors.text },
  pageSubtitle: { fontSize: 13, color: Colors.textMuted, marginTop: 2 },
  list: { padding: 16, paddingBottom: 24 },
  card: {
    backgroundColor: Colors.surface, borderRadius: 16, padding: 16,
    marginBottom: 12, borderWidth: 1, borderColor: Colors.border,
  },
  cardHeader: { flexDirection: 'row', alignItems: 'flex-start', gap: 12, marginBottom: 10 },
  iconBox: { width: 40, height: 40, borderRadius: 10, justifyContent: 'center', alignItems: 'center' },
  cardTitle: { fontSize: 15, fontWeight: '600', color: Colors.text },
  cardSubtitle: { fontSize: 12, color: Colors.textMuted, marginTop: 2 },
  cardBody: { gap: 6, paddingLeft: 52 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
});
