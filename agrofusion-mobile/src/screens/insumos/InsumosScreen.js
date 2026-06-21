import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { insumosApi } from '../../api/client';
import Card from '../../components/Card';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import StatusBadge from '../../components/StatusBadge';
import { Colors } from '../../constants/colors';

export default function InsumosScreen({ navigation }) {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await insumosApi.list();
      setData(res.data?.data || res.data || []);
    } catch (e) { console.error(e); }
    finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, []);

  const handleDelete = (item) => {
    Alert.alert('Eliminar Insumo', `¿Estás seguro de eliminar "${item.nombre}"?`, [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Eliminar', style: 'destructive', onPress: async () => {
        try {
          await insumosApi.delete(item.insumoid);
          loadData();
        } catch (e) {
          Alert.alert('Error', 'No se pudo eliminar');
        }
      }},
    ]);
  };

  const getStockStatus = (item) => {
    if (item.stock == null) return null;
    if (item.stock <= (item.stockminimo || 5)) return 'bajo';
    if (item.stock <= (item.stockminimo || 5) * 2) return 'medio';
    return 'alto';
  };

  const getStockColor = (status) => {
    if (status === 'bajo') return Colors.error;
    if (status === 'medio') return Colors.warning;
    return Colors.success;
  };

  const getStockLabel = (status) => ({ bajo: 'Stock bajo', medio: 'Stock medio', alto: 'Stock alto' }[status]);

  const renderItem = ({ item }) => {
    const stockStatus = item.stock != null ? getStockStatus(item) : null;
    return (
      <Card
        title={item.nombre || `Insumo #${item.insumoid}`}
        subtitle={item.tipo?.nombre || 'Sin tipo'}
        icon="flask-outline"
        iconColor={Colors.purple}
        rightElement={stockStatus ? <StatusBadge status={stockStatus} label={getStockLabel(stockStatus)} /> : null}
      >
        <View style={styles.cardBody}>
          <View style={styles.infoRow}>
            <Ionicons name="cube-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>
              Stock: {item.stock ?? '-'} {item.unidad_medida?.nombre || ''}
            </Text>
          </View>
          {item.dosis_por_ha != null && (
            <View style={styles.infoRow}>
              <Ionicons name="calculator-outline" size={16} color={Colors.textSecondary} />
              <Text style={styles.infoText}>
                Dosis: {item.dosis_por_ha} {item.dosis_unidad || 'kg'}/ha
              </Text>
            </View>
          )}
          {item.descripcion && (
            <Text style={styles.description} numberOfLines={2}>{item.descripcion}</Text>
          )}
        </View>
        <View style={styles.actions}>
          <TouchableOpacity
            style={styles.editBtn}
            onPress={() => navigation.navigate('InsumoForm', { id: item.insumoid })}
          >
            <Ionicons name="create-outline" size={18} color="#FFF" />
            <Text style={styles.btnText}>Editar</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={styles.deleteBtn}
            onPress={() => handleDelete(item)}
          >
            <Ionicons name="trash-outline" size={18} color="#FFF" />
            <Text style={styles.btnText}>Eliminar</Text>
          </TouchableOpacity>
        </View>
      </Card>
    );
  };

  if (loading) return <LoadingSpinner fullScreen message="Cargando insumos..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.insumoid)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />
        }
        ListEmptyComponent={<EmptyState icon="flask-outline" message="No hay insumos registrados. Crea el primer insumo tocando el botón +." />}
      />
      <TouchableOpacity style={styles.fab} onPress={() => navigation.navigate('InsumoForm')}>
        <Ionicons name="add" size={28} color="#FFF" />
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  list: { padding: 16, paddingBottom: 80 },
  cardBody: { marginTop: 12, gap: 6 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
  description: { fontSize: 13, color: Colors.textMuted, fontStyle: 'italic', marginTop: 4 },
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
    shadowColor: '#000', shadowOffset: { width: 0, height: 3 }, shadowOpacity: 0.3, shadowRadius: 4,
  },
});
