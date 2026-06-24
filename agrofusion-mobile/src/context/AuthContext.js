import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { authApi } from '../api/client';
import { USE_MOCK_DATA } from '../constants/designMode';
import { DEMO_USERS } from '../data/mockWorkersData';
import { ROLES } from '../constants/roles';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadStoredAuth();
  }, []);

  const loadStoredAuth = async () => {
    try {
      const storedToken = await AsyncStorage.getItem('auth_token');
      const storedUser = await AsyncStorage.getItem('user_data');
      if (storedToken && storedUser) {
        setToken(storedToken);
        setUser(JSON.parse(storedUser));
      }
    } catch (e) {
      console.error('Error loading auth:', e);
    } finally {
      setLoading(false);
    }
  };

  const login = async (email, password) => {
    const response = await authApi.login(email, password);
    const { token: newToken, user: userData } = response.data;
    await AsyncStorage.setItem('auth_token', newToken);
    await AsyncStorage.setItem('user_data', JSON.stringify(userData));
    setToken(newToken);
    setUser(userData);
    return userData;
  };

  const register = async (data) => {
    const response = await authApi.register(data);
    return response.data;
  };

  const logout = useCallback(async () => {
    try {
      const stored = await AsyncStorage.getItem('auth_token');
      if (stored && !stored.startsWith('demo-')) {
        await authApi.logout();
      }
    } catch (e) {}
    await AsyncStorage.multiRemove(['auth_token', 'user_data']);
    setToken(null);
    setUser(null);
  }, []);

  const refreshUser = async () => {
    try {
      const response = await authApi.me();
      const userData = response.data;
      await AsyncStorage.setItem('user_data', JSON.stringify(userData));
      setUser(userData);
    } catch (e) {
      console.error('Error refreshing user:', e);
    }
  };

  const loginDemo = useCallback(async (roleKey) => {
    const demo = DEMO_USERS[roleKey];
    if (!demo) return null;
    const demoUser = {
      usuarioid: demo.usuarioid,
      nombre: demo.nombre,
      apellido: demo.apellido,
      email: demo.email,
      roles: [{ name: demo.role }],
    };
    const demoToken = `demo-${roleKey}-token`;
    await AsyncStorage.setItem('auth_token', demoToken);
    await AsyncStorage.setItem('user_data', JSON.stringify(demoUser));
    setToken(demoToken);
    setUser(demoUser);
    return demoUser;
  }, []);

  const showDemoLogin = USE_MOCK_DATA;

  return (
    <AuthContext.Provider value={{
      user, token, loading, login, register, logout, refreshUser,
      loginDemo, showDemoLogin,
    }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
