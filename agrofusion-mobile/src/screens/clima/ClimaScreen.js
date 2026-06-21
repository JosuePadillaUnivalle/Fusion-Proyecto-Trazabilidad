import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { climasApi } from '../../api/client';
import Card from '../../components/Card';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';
import { Colors } from '../../constants/colors';
import { formatDate } from '../../utils/helpers';

export default function ClimaScreen() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const res = await climasApi.list();
      setData(res.data?.data || res.data || []);
    } catch (e) {} finally { setLoading(false); setRefreshing(false); }
  };

  useEffect(() => { loadData(); }, []);

  const renderItem = ({ item }) => (
    <Card
      title={item.lote?.nombre || 'Registro climático'}
      subtitle={formatDate(item.fecharegistro)}
      icon="cloud-outline"
      iconColor="#0288D1"
    >
      <View style={styles.weatherGrid}>
        {item.temperatura != null && (
          <View style={styles.weatherItem}>
            <Ionicons name="thermometer-outline" size={24} color={Colors.error} />
            <Text style={styles.weatherValue}>{item.temperatura}°C</Text>
            <Text style={styles.weatherLabel}>Temperatura</Text>
          </View>
        )}
        {item.humedad != null && (
          <View style={styles.weatherItem}>
            <Ionicons name="water-outline" size={24} color={Colors.info} />
            <Text style={styles.weatherValue}>{item.humedad}%</Text>
            <Text style={styles.weatherLabel}>Humedad</Text>
          </View>
        )}
        {item.precipitacion != null && (
          <View style={styles.weatherItem}>
            <Ionicons name="rainy-outline" size={24} color={Colors.accent} />
            <Text style={styles.weatherValue}>{item.precipitacion}mm</Text>
            <Text style={styles.weatherLabel}>Precipitación</Text>
          </View>
        )}
        {item.velocidad_viento != null && (
          <View style={styles.weatherItem}>
            <Ionicons name="flag-outline" size={24} color={Colors.textSecondary} />
            <Text style={styles.weatherValue}>{item.velocidad_viento}km/h</Text>
            <Text style={styles.weatherLabel}>Viento</Text>
          </View>
        )}
      </View>
    </Card>
  );

  if (loading) return <LoadingSpinner fullScreen message="Cargando datos climáticos..." />;

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item) => String(item.climaid || item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[Colors.primary]} />}
        ListEmptyComponent={<EmptyState icon="cloud-outline" message="No hay datos climáticos registrados" />}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  list: { padding: 16 },
  weatherGrid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-around', marginTop: 12, gap: 12 },
  weatherItem: { alignItems: 'center', minWidth: 70 },
  weatherValue: { fontSize: 18, fontWeight: '700', color: Colors.text, marginTop: 4 },
  weatherLabel: { fontSize: 11, color: Colors.textSecondary, marginTop: 2 },
});
