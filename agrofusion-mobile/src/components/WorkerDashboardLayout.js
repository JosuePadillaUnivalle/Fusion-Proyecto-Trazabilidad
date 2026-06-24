import React from 'react';
import {
  View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { Colors } from '../constants/colors';
import { ROLE_LABELS } from '../constants/roles';

export default function WorkerDashboardLayout({
  userName,
  userRole,
  roleIcon = 'person-outline',
  statsRows = [],
  menuItems = [],
  refreshing = false,
  onRefresh,
}) {
  return (
    <ScrollView
      style={styles.container}
      refreshControl={
        onRefresh
          ? <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[Colors.primary]} />
          : undefined
      }
    >
      <View style={styles.header}>
        <View style={styles.headerTop}>
          <View>
            <Text style={styles.greeting}>Bienvenido</Text>
            <Text style={styles.userName}>{userName}</Text>
          </View>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>{(userName[0] || 'U').toUpperCase()}</Text>
          </View>
        </View>
        <View style={styles.roleBadge}>
          <Ionicons name={roleIcon} size={12} color={Colors.primaryDark} />
          <Text style={styles.roleBadgeText}>{ROLE_LABELS[userRole] || userRole}</Text>
        </View>
      </View>

      {statsRows.map((row, idx) => (
        <View key={idx} style={styles.statsSection}>
          {row.map((stat) => (
            <StatCard key={stat.label} {...stat} />
          ))}
        </View>
      ))}

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Mi trabajo</Text>
        <View style={styles.grid}>
          {menuItems.map((item) => (
            <TouchableOpacity
              key={item.title}
              style={styles.menuCard}
              onPress={item.onPress}
              activeOpacity={0.7}
            >
              <View style={[styles.iconCircle, { backgroundColor: (item.color || Colors.primary) + '15' }]}>
                <Ionicons name={item.icon} size={22} color={item.color || Colors.primary} />
              </View>
              <Text style={styles.menuTitle}>{item.title}</Text>
              <Text style={styles.menuSubtitle} numberOfLines={1}>{item.subtitle}</Text>
            </TouchableOpacity>
          ))}
        </View>
      </View>
    </ScrollView>
  );
}

function StatCard({ icon, label, value, color, onPress }) {
  return (
    <TouchableOpacity style={styles.statCard} onPress={onPress} activeOpacity={onPress ? 0.8 : 1}>
      <View style={styles.statHeader}>
        <View style={[styles.statIcon, { backgroundColor: color + '15' }]}>
          <Ionicons name={icon} size={22} color={color} />
        </View>
        <Text style={styles.statLabel}>{label}</Text>
      </View>
      <Text style={styles.statNumber}>{value}</Text>
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  header: {
    backgroundColor: Colors.surface,
    margin: 16,
    marginBottom: 12,
    borderRadius: 16,
    padding: 20,
    borderWidth: 1,
    borderColor: Colors.border,
  },
  headerTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  greeting: { fontSize: 13, color: Colors.textMuted, textTransform: 'uppercase', letterSpacing: 1 },
  userName: { fontSize: 22, fontWeight: '700', color: Colors.text, marginTop: 4 },
  avatar: {
    width: 48, height: 48, borderRadius: 14,
    backgroundColor: Colors.divider,
    justifyContent: 'center', alignItems: 'center',
    borderWidth: 1, borderColor: Colors.border,
  },
  avatarText: { fontSize: 18, fontWeight: '700', color: Colors.textSecondary },
  roleBadge: {
    flexDirection: 'row', alignItems: 'center', gap: 6,
    backgroundColor: Colors.divider,
    paddingHorizontal: 12, paddingVertical: 6,
    borderRadius: 8, marginTop: 16, alignSelf: 'flex-start',
    borderWidth: 1, borderColor: Colors.border,
  },
  roleBadgeText: { color: Colors.textSecondary, fontSize: 12, fontWeight: '600' },
  statsSection: { flexDirection: 'row', paddingHorizontal: 16, gap: 12, marginBottom: 8 },
  statCard: {
    flex: 1,
    backgroundColor: Colors.surface,
    borderRadius: 16, padding: 16,
    borderWidth: 1, borderColor: Colors.border,
  },
  statHeader: { flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 10 },
  statIcon: {
    width: 40, height: 40, borderRadius: 10,
    justifyContent: 'center', alignItems: 'center',
  },
  statLabel: { fontSize: 13, color: Colors.textSecondary, fontWeight: '600', flex: 1 },
  statNumber: { fontSize: 28, fontWeight: '700', color: Colors.text },
  section: { padding: 16, paddingTop: 8 },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.text, marginBottom: 12 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  menuCard: {
    width: '47.4%',
    backgroundColor: Colors.surface,
    borderRadius: 16, padding: 16,
    borderWidth: 1, borderColor: Colors.border,
  },
  iconCircle: {
    width: 44, height: 44, borderRadius: 12,
    justifyContent: 'center', alignItems: 'center',
    marginBottom: 14,
  },
  menuTitle: { fontSize: 15, fontWeight: '600', color: Colors.text },
  menuSubtitle: { fontSize: 12, color: Colors.textMuted, marginTop: 3 },
});
