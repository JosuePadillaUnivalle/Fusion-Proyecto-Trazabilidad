import React from 'react';
import { View, Text, TextInput, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { Colors } from '../constants/colors';

const FormInput = ({ label, icon, error, containerStyle, ...props }) => {
  return (
    <View style={[styles.container, containerStyle]}>
      {label && <Text style={styles.label}>{label}</Text>}
      <View style={[styles.inputWrapper, error && styles.inputError]}>
        {icon && <Ionicons name={icon} size={20} color={Colors.textSecondary} style={styles.icon} />}
        <TextInput
          style={[styles.input, icon ? { paddingLeft: 8 } : null]}
          placeholderTextColor={Colors.disabled}
          {...props}
        />
      </View>
      {error && <Text style={styles.errorText}>{error}</Text>}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    marginBottom: 16,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: Colors.text,
    marginBottom: 6,
  },
  inputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.background,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: Colors.border,
    paddingHorizontal: 12,
  },
  inputError: {
    borderColor: Colors.error,
  },
  icon: {
    marginRight: 4,
  },
  input: {
    flex: 1,
    height: 48,
    fontSize: 15,
    color: Colors.text,
  },
  errorText: {
    fontSize: 12,
    color: Colors.error,
    marginTop: 4,
  },
});

export default FormInput;
