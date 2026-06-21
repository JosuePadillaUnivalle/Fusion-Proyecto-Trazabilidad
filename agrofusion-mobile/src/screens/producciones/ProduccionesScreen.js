import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { produccionesApi } from '../../api/client';
import Card from '../../components/Card';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function ProduccionesScreen({ navigation }) {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await produccionesApi.list();
      setData(res.data?.data || res.data || []);
    } catch (e) { console.error(e); }
    finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, []);

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
      title={`${item.cantidad || 0} ${item.unidad || 'unidades'}`}
      subtitle={item.lote?.nombre || 'Sin lote'}
      icon="basket-outline"
      iconColor={Colors.warning}
      rightElement={
        <Text style={styles.dateText}>{formatDate(item.fechaproduccion || item.fecharegistro)}</Text>
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
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando cosechas..." />;

  return (
    <View style={styles.container}>
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
      <TouchableOpacity style={styles.fab} onPress={() => navigation.navigate('ProduccionForm')}>
        <Ionicons name="add" size={28} color="#FFF" />
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
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
