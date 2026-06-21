import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { usuariosApi } from '../../api/client';
import Card from '../../components/Card';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { ROLE_LABELS } from '../../constants/roles';

export default function UsuariosScreen({ navigation }) {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await usuariosApi.list();
      setData(res.data?.data || res.data || []);
    } catch (e) {} finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, []);

  const renderItem = ({ item }) => {
    const roleName = item.roles?.[0]?.name || '';
    return (
      <Card
        title={`${item.nombre} ${item.apellido}`}
        subtitle={item.email}
        icon="person-outline"
        iconColor="#1B5E20"
        onPress={() => navigation.navigate('UsuarioDetail', { id: item.usuarioid })}
        rightElement={
          <View style={styles.roleContainer}>
            <StatusBadge status={item.estado_cuenta === 'APROBADO' ? 'aprobado' : 'pendiente'} label={item.estado_cuenta || 'APROBADO'} />
          </View>
        }
      >
        <View style={styles.cardBody}>
          <View style={styles.infoRow}>
            <Ionicons name="shield-outline" size={16} color={Colors.textSecondary} />
            <Text style={styles.infoText}>{ROLE_LABELS[roleName] || roleName || 'Sin rol'}</Text>
          </View>
          {item.telefono && (
            <View style={styles.infoRow}>
              <Ionicons name="call-outline" size={16} color={Colors.textSecondary} />
              <Text style={styles.infoText}>{item.telefono}</Text>
            </View>
          )}
        </View>
      </Card>
    );
  };

  if (loading) return <LoadingSpinner fullScreen message="Cargando usuarios..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.usuarioid)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="people-outline" message="No hay usuarios registrados" />}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  list: { padding: 16 },
  cardBody: { marginTop: 12, gap: 6 },
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  infoText: { fontSize: 13, color: Colors.textSecondary },
  roleContainer: { alignItems: 'flex-end' },
});
