import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { almacenesApi } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { isAgricultor, isMayorista } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockAlmacenesAgricultor } from '../../data/mockAgricultorData';
import { getMockAlmacenesMayorista } from '../../data/mockWorkersData';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';

export default function AlmacenesScreen({ navigation }) {
  const { user } = useAuth();
  const esAgricultor = isAgricultor(user);
  const esMayorista = isMayorista(user);
  const soloConsulta = esAgricultor;
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esAgricultor) {
        setData(getMockAlmacenesAgricultor());
      } else if (USE_MOCK_DATA && esMayorista) {
        setData(getMockAlmacenesMayorista());
      } else {
        const res = await almacenesApi.list();
        setData(res.data?.data || res.data || []);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esAgricultor) setData(getMockAlmacenesAgricultor());
      else if (USE_MOCK_DATA && esMayorista) setData(getMockAlmacenesMayorista());
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { loadData(); }, []));

  const renderItem = ({ item }) => (
    <Card
      title={item.nombre || `Almacén #${item.almacenid}`}
      subtitle={item.tipo?.nombre || item.tipo_almacen?.nombre || 'Sin tipo'}
      icon="business-outline"
      iconColor={Colors.primary}
      onPress={() => navigation.navigate('AlmacenDetail', { id: item.almacenid })}
      rightElement={<StatusBadge status={item.activo !== false ? 'activo' : 'inactivo'} label={item.activo !== false ? 'Activo' : 'Inactivo'} />}
    >
      <View style={styles.cardBody}>
        {item.ubicacion && (
          <View style={styles.infoRow}>
            <Ionicons name="location-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.ubicacion}</Text>
          </View>
        )}
        {item.productos_stock != null && (
          <View style={styles.infoRow}>
            <Ionicons name="cube-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.productos_stock} productos en stock</Text>
          </View>
        )}
        {item.capacidad && (
          <View style={styles.infoRow}>
            <Ionicons name="layers-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>Capacidad: {item.capacidad}</Text>
          </View>
        )}
      </View>
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando almacenes..." />;

  const titulo = esMayorista ? 'Mis almacenes' : esAgricultor ? 'Almacenes de cosecha' : 'Almacenes';

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <Text style={styles.pageTitle}>{titulo}</Text>
        <Text style={styles.pageSubtitle}>{data.length} registros{soloConsulta ? ' · solo consulta' : ''}</Text>
      </View>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.almacenid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="business-outline" message="No hay almacenes registrados" />}
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
