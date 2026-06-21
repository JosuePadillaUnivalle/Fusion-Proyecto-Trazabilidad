import React from 'react';
import { View, ActivityIndicator, Text, StyleSheet } from 'react-native';
import { Colors } from '../constants/colors';

const LoadingSpinner = ({ message = 'Cargando...', size = 'large', fullScreen = false }) => {
  const content = (
    <>
      <ActivityIndicator size={size} color={Colors.primary} />
      {message && <Text style={styles.text}>{message}</Text>}
    </>
  );

  if (fullScreen) {
    return <View style={styles.fullScreen}>{content}</View>;
  }

  return <View style={styles.container}>{content}</View>;
};

const styles = StyleSheet.create({
  fullScreen: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: Colors.background,
  },
  container: {
    padding: 24,
    justifyContent: 'center',
    alignItems: 'center',
  },
  text: {
    marginTop: 12,
    fontSize: 14,
    color: Colors.textSecondary,
  },
});

export default LoadingSpinner;
