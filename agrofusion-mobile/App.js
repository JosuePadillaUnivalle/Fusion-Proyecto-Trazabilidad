import React from 'react';
import { StatusBar } from 'expo-status-bar';
import { NavigationContainer } from '@react-navigation/native';
import { View, StyleSheet } from 'react-native';
import { AuthProvider, useAuth } from './src/context/AuthContext';
import AuthNavigator from './src/navigation/AuthNavigator';
import MainNavigator from './src/navigation/MainNavigator';
import LoadingSpinner from './src/components/LoadingSpinner';

function AppContent() {
  const { user, loading } = useAuth();

  if (loading) {
    return <LoadingSpinner fullScreen message="Cargando AgroFusion..." />;
  }

  return user ? <MainNavigator /> : <AuthNavigator />;
}

export default function App() {
  return (
    <View style={styles.container}>
      <AuthProvider>
        <NavigationContainer>
          <AppContent />
          <StatusBar style="light" />
        </NavigationContainer>
      </AuthProvider>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
});
