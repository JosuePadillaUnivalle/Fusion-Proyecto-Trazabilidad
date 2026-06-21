import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { getStatusColor, getStatusLabel } from '../utils/helpers';

const StatusBadge = ({ status, label }) => {
  const color = getStatusColor(status);
  const displayLabel = label || getStatusLabel(status);

  return (
    <View style={[styles.badge, { backgroundColor: color + '20', borderColor: color }]}>
      <View style={[styles.dot, { backgroundColor: color }]} />
      <Text style={[styles.text, { color }]}>{displayLabel}</Text>
    </View>
  );
};

const styles = StyleSheet.create({
  badge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
    borderWidth: 1,
  },
  dot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    marginRight: 6,
  },
  text: {
    fontSize: 12,
    fontWeight: '600',
  },
});

export default StatusBadge;
