import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl, TouchableOpacity, Alert } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../context/AuthContext';
import { isTransportista, isMayorista } from '../../constants/roles';
import { USE_MOCK_DATA } from '../../constants/designMode';
import { getMockDocumentos } from '../../data/mockWorkersData';
import Card from '../../components/Card';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function DocumentosScreen() {
  const { user } = useAuth();
  const esTrabajador = isTransportista(user) || isMayorista(user);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      if (USE_MOCK_DATA && esTrabajador) {
        setData(getMockDocumentos(user?.usuarioid));
      } else {
        setData([]);
      }
    } catch (e) {
      if (USE_MOCK_DATA && esTrabajador) setData(getMockDocumentos(user?.usuarioid));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useFocusEffect(useCallback(() => { loadData(); }, []));

  const showDetalle = (item) => {
    Alert.alert(
      item.titulo || item.nombre || `Documento #${item.id}`,
      [
        item.tipo && `Tipo: ${item.tipo}`,
        item.referencia && `Referencia: ${item.referencia}`,
        item.estado && `Estado: ${item.estado}`,
        item.fecha || item.fecharegistro ? `Fecha: ${formatDate(item.fecha || item.fecharegistro)}` : null,
        USE_MOCK_DATA ? '\n(Vista previa — sin descarga real)' : null,
      ].filter(Boolean).join('\n'),
      [{ text: 'Cerrar' }],
    );
  };

  const renderItem = ({ item }) => (
    <TouchableOpacity activeOpacity={0.7} onPress={() => showDetalle(item)}>
      <Card
        title={item.titulo || item.nombre || `Documento #${item.id}`}
        subtitle={item.tipo || 'Comprobante'}
        icon="document-text-outline"
        iconColor={Colors.primary}
      >
        <View style={styles.cardBody}>
          <View style={styles.infoRow}>
            <Ionicons name="calendar-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{formatDate(item.fecha || item.fecharegistro)}</Text>
          </View>
          {item.referencia && (
            <View style={styles.infoRow}>
              <Ionicons name="link-outline" size={16} color={Colors.textSecondary} />
              <Text style={styles.infoText}>{item.referencia}</Text>
            </View>
          )}
          {item.estado && (
            <View style={styles.infoRow}>
              <Ionicons name="checkmark-circle-outline" size={16} color={Colors.textSecondary} />
              <Text style={styles.infoText}>{item.estado}</Text>
            </View>
          )}
        </View>
      </Card>
    </TouchableOpacity>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando documentos..." />;

  return (
    <View style={styles.container}>
      <View style={styles.pageHeader}>
        <Text style={styles.pageTitle}>Documentos</Text>
        <Text style={styles.pageSubtitle}>{data.length} registros · toca para ver detalle</Text>
      </View>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.id || item.documentoid)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="document-text-outline" message="No hay documentos registrados" />}
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
