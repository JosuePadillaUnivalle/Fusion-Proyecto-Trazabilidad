import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { actividadesApi } from '../../api/client';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function ActividadesScreen({ navigation }) {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await actividadesApi.list();
      setData(res.data?.data || res.data || []);
    } catch (e) { console.error(e); }
    finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, []);

  const handleDelete = (item) => {
    Alert.alert('Eliminar Actividad', '¿Estás seguro de eliminar esta actividad?', [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Eliminar', style: 'destructive', onPress: async () => {
        try {
          await actividadesApi.delete(item.actividadid);
          loadData();
        } catch (e) {
          Alert.alert('Error', 'No se pudo eliminar');
        }
      }},
    ]);
  };

  const renderItem = ({ item }) => (
    <Card
      title={item.descripcion || item.tipo_actividad?.nombre || 'Actividad'}
      subtitle={item.lote?.nombre || 'Sin lote'}
      icon="calendar-outline"
      iconColor={Colors.info}
      rightElement={
        item.completada
          ? <StatusBadge status="completado" label="Completada" />
          : <StatusBadge status="pendiente" label="Pendiente" />
      }
    >
      <View style={styles.cardBody}>
        <View style={styles.infoRow}>
          <Ionicons name="time-outline" size={16} color={Colors.textSecondary} />
          <Text style={styles.infoText}>{formatDate(item.fechaactividad || item.fecharegistro)}</Text>
        </View>
        {item.usuario && (
          <View style={styles.infoRow}>
            <Ionicons name="person-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.usuario.nombre} {item.usuario.apellido}</Text>
          </View>
        )}
        {item.tipo_actividad?.nombre && (
          <View style={styles.infoRow}>
            <Ionicons name="pricetag-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.tipo_actividad.nombre}</Text>
          </View>
        )}
      </View>
      <View style={styles.actions}>
        <TouchableOpacity
          style={styles.editBtn}
          onPress={() => navigation.navigate('ActividadForm', { id: item.actividadid })}
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

  if (loading) return <LoadingSpinner fullScreen message="Cargando actividades..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.actividadid)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />
        }
        ListEmptyComponent={<EmptyState icon="calendar-outline" message="No hay actividades registradas" />}
      />
      <TouchableOpacity style={styles.fab} onPress={() => navigation.navigate('ActividadForm')}>
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
