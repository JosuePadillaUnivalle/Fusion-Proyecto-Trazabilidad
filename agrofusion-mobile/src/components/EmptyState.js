import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { Colors } from '../constants/colors';

const EmptyState = ({ icon = 'folder-open-outline', message = 'No hay datos disponibles', action }) => {
  return (
    <View style={styles.container}>
      <Ionicons name={icon} size={64} color={Colors.disabled} />
      <Text style={styles.message}>{message}</Text>
      {action}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 32,
  },
  message: {
    fontSize: 16,
    color: Colors.textSecondary,
    marginTop: 16,
    textAlign: 'center',
  },
});

export default EmptyState;
