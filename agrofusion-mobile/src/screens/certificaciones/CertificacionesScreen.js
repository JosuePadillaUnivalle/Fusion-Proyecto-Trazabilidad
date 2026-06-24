import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { certificacionesApi } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { isAgricultor } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockCertificaciones } from '../../data/mockAgricultorData';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function CertificacionesScreen() {
  const { user } = useAuth();
  const esAgricultor = isAgricultor(user);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esAgricultor) {
        setData(getMockCertificaciones(user?.usuarioid));
      } else {
        const res = await certificacionesApi.list();
        setData(res.data?.data || res.data || []);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esAgricultor) setData(getMockCertificaciones(user?.usuarioid));
    } finally { setLoading(false); setRefreshing(false); }
  };

  useFocusEffect(useCallback(() => { loadData(); }, []));

  const renderItem = ({ item }) => (
    <Card
      title={item.nombre || item.tipo_certificacion || `Certificación #${item.certificacionid}`}
      subtitle={item.lote?.nombre || 'Sin lote'}
      icon="shield-checkmark-outline"
      iconColor={Colors.primary}
      rightElement={<StatusBadge status={item.aprobado ? 'aprobado' : 'pendiente'} label={item.aprobado ? 'Aprobado' : 'Pendiente'} />}
    >
      <View style={styles.cardBody}>
        <View style={styles.infoRow}>
          <Ionicons name="calendar-outline" size={16} color={Colors.textSecondary} />
          <Text style={styles.infoText}>{formatDate(item.fechacertificacion || item.fecharegistro)}</Text>
        </View>
        {item.entidad_certificadora && (
          <View style={styles.infoRow}>
            <Ionicons name="business-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.entidad_certificadora}</Text>
          </View>
        )}
      </View>
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando certificaciones..." />;

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <Text style={styles.pageTitle}>{esAgricultor ? 'Mis certificaciones' : 'Certificaciones'}</Text>
        <Text style={styles.pageSubtitle}>{data.length} registros</Text>
      </View>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.certificacionid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="shield-checkmark-outline" message="No hay certificaciones registradas" />}
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
});
