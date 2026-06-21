import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { lotesApi } from '../../api/client';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate, truncate } from '../../utils/helpers';

export default function LotesScreen({ navigation }) {
  const [lotes, setLotes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await lotesApi.list();
      setLotes(res.data?.data || res.data || []);
    } catch (e) { console.error(e); }
    finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, []);
  const onRefresh = () => { setRefreshing(true); loadData(); };

  const handleDelete = (item) => {
    Alert.alert('Eliminar Lote', `¿Estás seguro de eliminar "${item.nombre}"?`, [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Eliminar', style: 'destructive', onPress: async () => {
        try {
          await lotesApi.delete(item.loteid);
          loadData();
        } catch (e) {
          Alert.alert('Error', 'No se pudo eliminar');
        }
      }},
    ]);
  };

  const renderItem = ({ item }) => (
    <Card
      title={item.nombre || `Lote #${item.loteid}`}
      subtitle={item.ubicacion ? truncate(item.ubicacion, 40) : 'Sin ubicación'}
      icon="map-outline"
      iconColor={Colors.primary}
      onPress={() => navigation.navigate('LoteDetail', { id: item.loteid })}
      rightElement={
        <StatusBadge
          status={item.estadoTipo?.slug || item.estadoTipo?.nombre || 'planificado'}
          label={item.estadoTipo?.nombre || 'Planificado'}
        />
      }
    >
      <View style={styles.cardBody}>
        <View style={styles.infoRow}>
          <Ionicons name="person-outline" size={16} color={Colors.textSecondary} />
          <Text style={styles.infoText}>
            Encargado: {item.usuario ? `${item.usuario.nombre} ${item.usuario.apellido}` : 'Sin asignar'}
          </Text>
        </View>
        <View style={styles.infoRow}>
          <Ionicons name="leaf-outline" size={16} color={Colors.textSecondary} />
          <Text style={styles.infoText}>
            {item.cultivo_etiqueta || item.cultivo?.nombre || 'Sin cultivo'}
          </Text>
        </View>
        <View style={styles.infoRow}>
          <Ionicons name="expand-outline" size={16} color={Colors.textSecondary} />
          <Text style={styles.infoText}>
            {item.superficie ? `${item.superficie} ha` : 'Sin superficie'}
          </Text>
        </View>
        {item.codigo_trazabilidad && (
          <View style={styles.infoRow}>
            <Ionicons name="barcode-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{item.codigo_trazabilidad}</Text>
          </View>
        )}
        <View style={styles.actions}>
          <TouchableOpacity
            style={styles.viewBtn}
            onPress={() => navigation.navigate('LoteDetail', { id: item.loteid })}
          >
            <Ionicons name="eye-outline" size={16} color={Colors.primary} />
            <Text style={[styles.viewBtnText, { color: Colors.primary }]}>Ver</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={styles.editBtn}
            onPress={() => navigation.navigate('LoteForm', { id: item.loteid })}
          >
            <Ionicons name="create-outline" size={16} color="#FFF" />
            <Text style={styles.btnText}>Editar</Text>
          </TouchableOpacity>
          <TouchableOpacity style={styles.deleteBtn} onPress={() => handleDelete(item)}>
            <Ionicons name="trash-outline" size={16} color="#FFF" />
            <Text style={styles.btnText}>Eliminar</Text>
          </TouchableOpacity>
        </View>
      </View>
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando lotes..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={lotes}
        keyExtractor={(item) => String(item.loteid)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="map-outline" message="No hay lotes registrados" />}
      />
      <TouchableOpacity style={styles.fab} onPress={() => navigation.navigate('LoteForm')}>
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
  actions: { flexDirection: 'row', gap: 8, marginTop: 10, justifyContent: 'flex-end' },
  viewBtn: {
    flexDirection: 'row', alignItems: 'center', paddingHorizontal: 10, paddingVertical: 6,
    borderRadius: 8, borderWidth: 1, borderColor: Colors.primary, gap: 4,
  },
  viewBtnText: { fontWeight: '600', fontSize: 12 },
  editBtn: {
    flexDirection: 'row', alignItems: 'center', backgroundColor: Colors.info,
    paddingHorizontal: 10, paddingVertical: 6, borderRadius: 8, gap: 4,
  },
  deleteBtn: {
    flexDirection: 'row', alignItems: 'center', backgroundColor: Colors.error,
    paddingHorizontal: 10, paddingVertical: 6, borderRadius: 8, gap: 4,
  },
  btnText: { color: '#FFF', fontWeight: '600', fontSize: 12 },
  fab: {
    position: 'absolute', bottom: 24, right: 24, width: 56, height: 56, borderRadius: 28,
    backgroundColor: Colors.primary, justifyContent: 'center', alignItems: 'center', elevation: 6,
  },
});
