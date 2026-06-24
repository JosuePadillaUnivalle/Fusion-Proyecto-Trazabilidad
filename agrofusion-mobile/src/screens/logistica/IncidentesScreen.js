import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Alert } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { incidentesApi } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { isTransportista } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockIncidentesTransportista } from '../../data/mockWorkersData';
import { resolveMockIncidente, addMockIncidente } from '../../data/mockRoleActions';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDateTime } from '../../utils/helpers';

export default function IncidentesScreen() {
  const { user } = useAuth();
  const esTransportista = isTransportista(user);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esTransportista) {
        setData(getMockIncidentesTransportista());
      } else {
        const res = await incidentesApi.list();
        setData(res.data?.data || res.data || []);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esTransportista) setData(getMockIncidentesTransportista());
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { loadData(); }, []));

  const handleResolve = (id) => {
    Alert.alert('Resolver incidente', '¿Marcar este incidente como resuelto?', [
      { text: 'Cancelar', style: 'cancel' },
      {
        text: 'Resolver',
        onPress: () => {
          if (USE_MOCK_DATA) {
            resolveMockIncidente(id);
            loadData();
          } else {
            incidentesApi.resolve(id).then(loadData).catch(() => Alert.alert('Error', 'No se pudo resolver'));
          }
        },
      },
    ]);
  };

  const handleReport = () => {
    Alert.alert('Reportar incidente', 'Seleccione el tipo de incidente', [
      { text: 'Cancelar', style: 'cancel' },
      {
        text: 'Retraso en ruta',
        onPress: () => {
          if (USE_MOCK_DATA) {
            addMockIncidente({ descripcion: 'Retraso reportado desde app móvil — tráfico en ruta' });
            loadData();
          }
        },
      },
      {
        text: 'Problema con carga',
        onPress: () => {
          if (USE_MOCK_DATA) {
            addMockIncidente({ descripcion: 'Problema con carga — temperatura fuera de rango' });
            loadData();
          }
        },
      },
    ]);
  };

  const renderItem = ({ item }) => (
    <Card
      title={item.tipo || item.titulo || item.descripcion || `Incidente #${item.incidenteid || item.id}`}
      subtitle={item.envio?.descripcion || ''}
      icon="warning-outline"
      iconColor={Colors.error}
      rightElement={<StatusBadge status={item.resuelto ? 'completado' : 'pendiente'} label={item.resuelto ? 'Resuelto' : 'Pendiente'} />}
    >
      <View style={styles.cardBody}>
        <View style={styles.infoRow}>
          <Ionicons name="calendar-outline" size={16} color={Colors.textSecondary} />
          <Text style={styles.infoText}>{formatDateTime(item.fechaincidente || item.fecharegistro)}</Text>
        </View>
        {item.descripcion && (
          <Text style={styles.description} numberOfLines={3}>{item.descripcion}</Text>
        )}
        {!item.resuelto && (
          <TouchableOpacity style={styles.resolveButton} onPress={() => handleResolve(item.incidenteid || item.id)}>
            <Text style={styles.resolveText}>Marcar como resuelto</Text>
          </TouchableOpacity>
        )}
      </View>
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando incidentes..." />;

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <View style={styles.pageHeaderRow}>
          <View>
            <Text style={styles.pageTitle}>{esTransportista ? 'Mis incidentes' : 'Incidentes'}</Text>
            <Text style={styles.pageSubtitle}>{data.length} registros</Text>
          </View>
          {USE_MOCK_DATA && esTransportista && (
            <TouchableOpacity style={styles.reportBtn} onPress={handleReport}>
              <Ionicons name="add-circle-outline" size={22} color={Colors.primary} />
              <Text style={styles.reportBtnText}>Reportar</Text>
            </TouchableOpacity>
          )}
        </View>
      </View>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.incidenteid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="warning-outline" message="No hay incidentes registrados" />}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  pageHeader: { padding: 16, paddingBottom: 8 },
  pageHeaderRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
  pageTitle: { fontSize: 22, fontWeight: '700', color: Colors.text },
  pageSubtitle: { fontSize: 13, color: Colors.textMuted, marginTop: 2 },
  reportBtn: { flexDirection: 'row', alignItems: 'center', gap: 4, padding: 8 },
  reportBtnText: { fontSize: 13, color: Colors.primary, fontWeight: '600' },
  list: { padding: 16 },
  cardBody: { marginTop: 12, gap: 6 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
  description: { fontSize: 13, color: Colors.text, fontStyle: 'italic' },
  resolveButton: { backgroundColor: Colors.successLight, padding: 10, borderRadius: 8, alignItems: 'center', marginTop: 8, borderWidth: 1, borderColor: Colors.success + '40' },
  resolveText: { color: Colors.success, fontWeight: '600', fontSize: 13 },
});
