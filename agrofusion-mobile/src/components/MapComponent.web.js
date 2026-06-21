import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { Colors } from '../constants/colors';

export const MapView = ({ children, style, initialRegion, region, onPress, showsUserLocation }) => (
  <View style={[styles.placeholder, style]}>
    <Ionicons name="map-outline" size={48} color={Colors.textMuted} />
    <Text style={styles.text}>El mapa está disponible en el dispositivo móvil</Text>
  </View>
);

export const Marker = ({ coordinate, children }) => null;

export const Circle = (props) => null;

const styles = StyleSheet.create({
  placeholder: {
    backgroundColor: Colors.surface,
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  text: { fontSize: 14, color: Colors.textSecondary, marginTop: 12, textAlign: 'center' },
});
