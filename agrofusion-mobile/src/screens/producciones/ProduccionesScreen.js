import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Alert } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { produccionesApi } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { isAgricultor } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockProducciones } from '../../data/mockAgricultorData';
import Card from '../../components/Card';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function ProduccionesScreen({ navigation }) {
  const { user } = useAuth();
  const esAgricultor = isAgricultor(user);
  const soloConsulta = esAgricultor;
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esAgricultor) {
        setData(getMockProducciones(user?.usuarioid));
      } else {
        const res = await produccionesApi.list();
        setData(res.data?.data || res.data || []);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esAgricultor) setData(getMockProducciones(user?.usuarioid));
    } finally { setLoading(false); setRefreshing(false); }
  };

  useFocusEffect(useCallback(() => { loadData(); }, []));

  const handleDelete = (item) => {
    Alert.alert('Eliminar Cosecha', '¿Estás seguro de eliminar esta cosecha?', [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Eliminar', style: 'destructive', onPress: async () => {
        try {
          await produccionesApi.delete(item.produccionid);
          loadData();
        } catch (e) {
          Alert.alert('Error', 'No se pudo eliminar');
        }
      }},
    ]);
  };

  const renderItem = ({ item }) => (
    <Card
      title={`${item.cantidad || 0} ${item.unidad?.abreviatura || item.unidad || 'kg'}`}
      subtitle={item.lote?.nombre || 'Sin lote'}
      icon="basket-outline"
      iconColor={Colors.primary}
      rightElement={
        <Text style={styles.dateText}>{formatDate(item.fechacosecha || item.fechaproduccion || item.fecharegistro)}</Text>
      }
    >
      <View style={styles.cardBody}>
        {item.destino && (
          <View style={styles.infoRow}>
            <Ionicons name="send-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>Destino: {item.destino}</Text>
          </View>
        )}
        {item.observaciones && (
          <Text style={styles.observaciones} numberOfLines={2}>{item.observaciones}</Text>
        )}
      </View>
      {!soloConsulta && (
      <View style={styles.actions}>
        <TouchableOpacity
          style={styles.editBtn}
          onPress={() => navigation.navigate('ProduccionForm', { id: item.produccionid })}
        >
          <Ionicons name="create-outline" size={18} color="#FFF" />
          <Text style={styles.btnText}>Editar</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.deleteBtn} onPress={() => handleDelete(item)}>
          <Ionicons name="trash-outline" size={18} color="#FFF" />
          <Text style={styles.btnText}>Eliminar</Text>
        </TouchableOpacity>
      </View>
      )}
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando cosechas..." />;

  return (
    <View style={styles.container}>
      {soloConsulta && (
        <View style={styles.pageHeader}>
          <Text style={styles.pageTitle}>Mis cosechas</Text>
          <Text style={styles.pageSubtitle}>{data.length} registros</Text>
        </View>
      )}
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.produccionid)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />
        }
        ListEmptyComponent={<EmptyState icon="basket-outline" message="No hay cosechas registradas" />}
      />
      {!soloConsulta && (
      <TouchableOpacity style={styles.fab} onPress={() => navigation.navigate('ProduccionForm')}>
        <Ionicons name="add" size={28} color="#FFF" />
      </TouchableOpacity>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  pageHeader: { padding: 16, paddingBottom: 8 },
  pageTitle: { fontSize: 22, fontWeight: '700', color: Colors.text },
  pageSubtitle: { fontSize: 13, color: Colors.textMuted, marginTop: 2 },
  list: { padding: 16, paddingBottom: 80 },
  dateText: { fontSize: 12, color: Colors.textMuted },
  cardBody: { marginTop: 12, gap: 6 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
  observaciones: { fontSize: 13, color: Colors.textMuted, fontStyle: 'italic', marginTop: 4 },
  actions: { flexDirection: 'row', gap: 8, marginTop: 12, justifyContent: 'flex-end' },
  editBtn: {
    flexDirection: 'row', alignItems: 'center', backgroundColor: Colors.info,
    paddingHorizontal: 14, paddingVertical: 8, borderRadius: 8, gap: 6,
  },
  deleteBtn: {
    flexDirection: 'row', alignItems: 'center', backgroundColor: Colors.error,
    paddingHorizontal: 14, paddingVertical: 8, borderRadius: 8, gap: 6,
  },
  btnText: { color: '#FFF', fontWeight: '600', fontSize: 13 },
  fab: {
    position: 'absolute', bottom: 24, right: 24, width: 56, height: 56, borderRadius: 28,
    backgroundColor: Colors.primary, justifyContent: 'center', alignItems: 'center', elevation: 6,
  },
});
