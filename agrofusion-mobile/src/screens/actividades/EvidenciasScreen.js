import React, { useState, useCallback } from 'react';
import {
  View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Image,
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { actividadesApi } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockActividades, getMockEvidenciaUrl } from '../../data/mockAgricultorData';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

function resolveEvidenciaUrl(item) {
  if (USE_MOCK_DATA) return getMockEvidenciaUrl(item);
  return item.evidencia_foto_url || null;
}

export default function EvidenciasScreen({ navigation }) {
  const { user } = useAuth();
  const [evidencias, setEvidencias] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA) {
        const mine = getMockActividades(user?.usuarioid).filter(a => a.fechafin && a.evidencia_foto_path);
        setEvidencias(mine);
      } else {
        const res = await actividadesApi.list();
        const all = res.data?.data || res.data || [];
        setEvidencias(all.filter(a => a.usuarioid === user?.usuarioid && a.fechafin && a.evidencia_foto_path));
      }
    } catch (e) {
      if (USE_MOCK_DATA) {
        setEvidencias(getMockActividades(user?.usuarioid).filter(a => a.fechafin && a.evidencia_foto_path));
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(
    useCallback(() => { loadData(); }, [])
  );

  const onRefresh = () => { setRefreshing(true); loadData(); };

  const renderItem = ({ item }) => {
    const url = resolveEvidenciaUrl(item);
    return (
      <TouchableOpacity
        style={styles.card}
        onPress={() => navigation.navigate('ActividadDetail', { actividadId: item.actividadid, lote: item.lote })}
        activeOpacity={0.8}
      >
        <Image source={{ uri: url }} style={styles.image} resizeMode="cover" />
        <View style={styles.overlay} />
        <View style={styles.cardContent}>
          <View style={styles.badge}>
            <Ionicons name="checkmark-circle" size={12} color={Colors.success} />
            <Text style={styles.badgeText}>Completada</Text>
          </View>
          <Text style={styles.title}>{item.descripcion || item.tipo_actividad?.nombre}</Text>
          <Text style={styles.subtitle}>{item.lote?.nombre || 'Sin lote'}</Text>
          <Text style={styles.date}>{formatDate(item.fechafin)}</Text>
        </View>
      </TouchableOpacity>
    );
  };

  if (loading) return <LoadingSpinner fullScreen message="Cargando evidencias..." />;

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Evidencias fotográficas</Text>
        <Text style={styles.headerSubtitle}>{evidencias.length} registros</Text>
      </View>
      <FlatList
        data={evidencias}
        keyExtractor={(item) => String(item.actividadid)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="images-outline" message="Aún no has enviado evidencias" />}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  header: { padding: 16, paddingBottom: 8 },
  headerTitle: { fontSize: 22, fontWeight: '700', color: Colors.text },
  headerSubtitle: { fontSize: 13, color: Colors.textMuted, marginTop: 2 },
  list: { padding: 16, paddingBottom: 24 },
  card: {
    backgroundColor: Colors.surface,
    borderRadius: 16,
    marginBottom: 16,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: Colors.border,
    height: 220,
  },
  image: { width: '100%', height: '100%' },
  overlay: {
    position: 'absolute', left: 0, right: 0, bottom: 0, height: 140,
    backgroundColor: 'rgba(15, 23, 42, 0.75)',
  },
  cardContent: { position: 'absolute', left: 0, right: 0, bottom: 0, padding: 16 },
  badge: {
    flexDirection: 'row', alignItems: 'center', gap: 4,
    backgroundColor: Colors.successLight, paddingHorizontal: 8, paddingVertical: 3,
    borderRadius: 8, alignSelf: 'flex-start', marginBottom: 8,
  },
  badgeText: { fontSize: 11, color: Colors.success, fontWeight: '600' },
  title: { fontSize: 16, fontWeight: '600', color: '#FFF' },
  subtitle: { fontSize: 13, color: 'rgba(255,255,255,0.8)', marginTop: 2 },
  date: { fontSize: 12, color: 'rgba(255,255,255,0.6)', marginTop: 4 },
});
