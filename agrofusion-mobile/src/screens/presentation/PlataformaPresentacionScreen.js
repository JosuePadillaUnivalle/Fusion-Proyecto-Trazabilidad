import React, { useRef, useState } from 'react';
import {
  View, Text, StyleSheet, FlatList, ImageBackground, TouchableOpacity,
  Dimensions, SafeAreaView, StatusBar,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import {
  PRESENTATION_SLIDES,
  PRESENTATION_ACTORS,
  PRESENTATION_TIMELINE,
} from '../../constants/presentationSlides';
import { Colors } from '../../constants/colors';

const { width: SCREEN_W, height: SCREEN_H } = Dimensions.get('window');

function SlideBackground({ image, children, dark = true }) {
  if (!image) {
    return (
      <View style={[styles.slide, styles.slideSolid]}>
        {children}
      </View>
    );
  }
  return (
    <ImageBackground source={{ uri: image }} style={styles.slide} resizeMode="cover">
      <View style={[styles.overlay, dark && styles.overlayDark]}>{children}</View>
    </ImageBackground>
  );
}

function ActorsGrid() {
  return (
    <View style={styles.actorsGrid}>
      {PRESENTATION_ACTORS.map((a) => (
        <View key={a.key} style={styles.actorChip}>
          <Ionicons name={a.icon} size={16} color={Colors.primaryLight} />
          <Text style={styles.actorLabel}>{a.label}</Text>
        </View>
      ))}
    </View>
  );
}

function WebMockPanel() {
  return (
    <View style={styles.mockPanel}>
      <View style={styles.mockHeader}>
        <View style={styles.mockDot} />
        <View style={[styles.mockDot, { opacity: 0.6 }]} />
        <View style={[styles.mockDot, { opacity: 0.35 }]} />
        <Text style={styles.mockHeaderText}>Panel web · AgroFusion</Text>
      </View>
      <View style={styles.mockRow}>
        <View style={styles.mockStat}>
          <Text style={styles.mockStatVal}>24</Text>
          <Text style={styles.mockStatLbl}>Lotes activos</Text>
        </View>
        <View style={styles.mockStat}>
          <Text style={styles.mockStatVal}>8</Text>
          <Text style={styles.mockStatLbl}>En cosecha</Text>
        </View>
      </View>
      <View style={styles.mockListItem}>
        <Ionicons name="map-outline" size={14} color={Colors.primary} />
        <Text style={styles.mockListText}>Lote Norte A · Tomate cherry</Text>
        <View style={styles.mockBadge}><Text style={styles.mockBadgeText}>En curso</Text></View>
      </View>
      <View style={styles.mockListItem}>
        <Ionicons name="person-outline" size={14} color={Colors.primary} />
        <Text style={styles.mockListText}>Carlos M. · Responsable asignado</Text>
      </View>
    </View>
  );
}

function MobileMockPanel() {
  return (
    <View style={styles.phoneFrame}>
      <View style={styles.phoneNotch} />
      <View style={styles.phoneContent}>
        <Text style={styles.phoneTitle}>Mis Actividades</Text>
        <View style={styles.phoneCard}>
          <Ionicons name="leaf-outline" size={18} color={Colors.success} />
          <View style={{ flex: 1 }}>
            <Text style={styles.phoneCardTitle}>Siembra lechuga romana</Text>
            <Text style={styles.phoneCardSub}>Lote Sur B · Pendiente</Text>
          </View>
        </View>
        <View style={[styles.phoneCard, { opacity: 0.85 }]}>
          <Ionicons name="water-outline" size={18} color={Colors.warning} />
          <View style={{ flex: 1 }}>
            <Text style={styles.phoneCardTitle}>Riego programado</Text>
            <Text style={styles.phoneCardSub}>Lote Norte A</Text>
          </View>
        </View>
        <View style={styles.phoneCameraBtn}>
          <Ionicons name="camera-outline" size={16} color="#FFF" />
          <Text style={styles.phoneCameraText}>Registrar evidencia</Text>
        </View>
      </View>
    </View>
  );
}

function EvidenceMock() {
  return (
    <View style={styles.evidenceBox}>
      <View style={styles.evidencePhoto}>
        <Ionicons name="image-outline" size={40} color={Colors.textMuted} />
        <Text style={styles.evidencePhotoLbl}>Comprobante de siembra</Text>
      </View>
      <View style={styles.evidenceMeta}>
        <MetaRow icon="map-outline" text="Lote Sur B" />
        <MetaRow icon="person-outline" text="Carlos Mendoza" />
        <MetaRow icon="time-outline" text="23 jun 2026 · 07:42" />
      </View>
    </View>
  );
}

function MetaRow({ icon, text }) {
  return (
    <View style={styles.metaRow}>
      <Ionicons name={icon} size={14} color={Colors.primary} />
      <Text style={styles.metaText}>{text}</Text>
    </View>
  );
}

function TimelinePanel() {
  return (
    <View style={styles.timelineBox}>
      {PRESENTATION_TIMELINE.map((t, i) => (
        <View key={t.etapa} style={styles.timelineItem}>
          <View style={styles.timelineLeft}>
            <View style={[styles.timelineDot, i === PRESENTATION_TIMELINE.length - 1 && styles.timelineDotLast]}>
              <Ionicons name={t.icon} size={12} color="#FFF" />
            </View>
            {i < PRESENTATION_TIMELINE.length - 1 && <View style={styles.timelineLine} />}
          </View>
          <Text style={styles.timelineLabel}>{t.etapa}</Text>
        </View>
      ))}
    </View>
  );
}

function ChainPanel() {
  const chain = ['Agricultor', 'Transportista', 'Planta', 'Mayorista', 'Minorista', 'Consumidor'];
  return (
    <View style={styles.chainRow}>
      {chain.map((c, i) => (
        <React.Fragment key={c}>
          <View style={styles.chainNode}>
            <Text style={styles.chainNodeText}>{c}</Text>
          </View>
          {i < chain.length - 1 && (
            <Ionicons name="arrow-forward" size={12} color="rgba(255,255,255,0.6)" />
          )}
        </React.Fragment>
      ))}
    </View>
  );
}

function renderSlideContent(slide) {
  switch (slide.type) {
    case 'actors':
      return <ActorsGrid />;
    case 'web-mock':
      return <WebMockPanel />;
    case 'mobile-mock':
      return <MobileMockPanel />;
    case 'evidence':
      return <EvidenceMock />;
    case 'timeline':
      return <TimelinePanel />;
    case 'chain':
      return <ChainPanel />;
    case 'logo':
      return (
        <View style={styles.logoFinal}>
          <View style={styles.logoBox}>
            <Ionicons name="leaf" size={48} color="#FFF" />
          </View>
        </View>
      );
    case 'closing':
      return (
        <View style={styles.closingIcons}>
          {['analytics-outline', 'shield-checkmark-outline', 'people-outline'].map((icon) => (
            <View key={icon} style={styles.closingIconWrap}>
              <Ionicons name={icon} size={22} color={Colors.primaryLight} />
            </View>
          ))}
        </View>
      );
    default:
      return null;
  }
}

export default function PlataformaPresentacionScreen({ navigation }) {
  const listRef = useRef(null);
  const [index, setIndex] = useState(0);
  const total = PRESENTATION_SLIDES.length;
  const isLast = index === total - 1;

  const goTo = (i) => {
    listRef.current?.scrollToIndex({ index: i, animated: true });
    setIndex(i);
  };

  const onNext = () => {
    if (isLast) {
      navigation.goBack();
      return;
    }
    goTo(index + 1);
  };

  const renderSlide = ({ item }) => (
    <SlideBackground image={item.image} dark={item.type !== 'logo'}>
      <View style={styles.slideInner}>
        {item.highlight && (
          <View style={styles.brandPill}>
            <Ionicons name="leaf" size={14} color="#FFF" />
            <Text style={styles.brandPillText}>AgroFusion</Text>
          </View>
        )}
        <Text style={[styles.slideTitle, item.type === 'logo' && styles.slideTitleLogo]}>
          {item.title}
        </Text>
        <Text style={[styles.slideBody, item.type === 'logo' && styles.slideBodyLogo]}>
          {item.body}
        </Text>
        {renderSlideContent(item)}
      </View>
    </SlideBackground>
  );

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" />
      <SafeAreaView style={styles.safeTop}>
        <View style={styles.topBar}>
          <TouchableOpacity style={styles.skipBtn} onPress={() => navigation.goBack()}>
            <Text style={styles.skipText}>{isLast ? 'Cerrar' : 'Omitir'}</Text>
          </TouchableOpacity>
          <Text style={styles.counter}>{index + 1} / {total}</Text>
        </View>
      </SafeAreaView>

      <FlatList
        ref={listRef}
        data={PRESENTATION_SLIDES}
        keyExtractor={(item) => item.id}
        renderItem={renderSlide}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        onMomentumScrollEnd={(e) => {
          const i = Math.round(e.nativeEvent.contentOffset.x / SCREEN_W);
          setIndex(i);
        }}
        getItemLayout={(_, i) => ({ length: SCREEN_W, offset: SCREEN_W * i, index: i })}
      />

      <SafeAreaView style={styles.safeBottom}>
        <View style={styles.dotsRow}>
          {PRESENTATION_SLIDES.map((s, i) => (
            <TouchableOpacity key={s.id} onPress={() => goTo(i)}>
              <View style={[styles.dot, i === index && styles.dotActive]} />
            </TouchableOpacity>
          ))}
        </View>
        <TouchableOpacity style={styles.nextBtn} onPress={onNext} activeOpacity={0.85}>
          <Text style={styles.nextBtnText}>
            {isLast ? 'Comenzar en AgroFusion' : 'Continuar'}
          </Text>
          <Ionicons name={isLast ? 'checkmark' : 'arrow-forward'} size={20} color="#FFF" />
        </TouchableOpacity>
      </SafeAreaView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.primaryDark },
  safeTop: { position: 'absolute', top: 0, left: 0, right: 0, zIndex: 10 },
  safeBottom: { position: 'absolute', bottom: 0, left: 0, right: 0, zIndex: 10 },
  topBar: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    paddingHorizontal: 20, paddingTop: 8,
  },
  skipBtn: { padding: 8 },
  skipText: { color: 'rgba(255,255,255,0.85)', fontSize: 14, fontWeight: '600' },
  counter: { color: 'rgba(255,255,255,0.65)', fontSize: 13, fontWeight: '500' },
  slide: { width: SCREEN_W, height: SCREEN_H },
  slideSolid: { backgroundColor: Colors.primaryDark },
  overlay: { flex: 1, justifyContent: 'flex-end' },
  overlayDark: { backgroundColor: 'rgba(15, 23, 42, 0.72)' },
  slideInner: { padding: 28, paddingBottom: 140 },
  brandPill: {
    flexDirection: 'row', alignItems: 'center', alignSelf: 'flex-start', gap: 6,
    backgroundColor: Colors.primary, paddingHorizontal: 12, paddingVertical: 6,
    borderRadius: 8, marginBottom: 16, borderWidth: 1, borderColor: 'rgba(255,255,255,0.2)',
  },
  brandPillText: { color: '#FFF', fontSize: 13, fontWeight: '700' },
  slideTitle: {
    fontSize: 26, fontWeight: '700', color: '#FFF', lineHeight: 34, marginBottom: 12,
  },
  slideTitleLogo: { fontSize: 36, textAlign: 'center', marginTop: 24 },
  slideBody: { fontSize: 15, color: 'rgba(255,255,255,0.9)', lineHeight: 24, marginBottom: 20 },
  slideBodyLogo: { textAlign: 'center', fontSize: 16, color: 'rgba(255,255,255,0.75)' },
  actorsGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  actorChip: {
    flexDirection: 'row', alignItems: 'center', gap: 6,
    backgroundColor: 'rgba(255,255,255,0.12)', borderWidth: 1, borderColor: 'rgba(255,255,255,0.2)',
    paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10,
  },
  actorLabel: { color: '#FFF', fontSize: 12, fontWeight: '600' },
  mockPanel: {
    backgroundColor: Colors.surface, borderRadius: 12, padding: 14,
    borderWidth: 1, borderColor: Colors.border,
  },
  mockHeader: { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: 12 },
  mockDot: { width: 8, height: 8, borderRadius: 4, backgroundColor: Colors.primary },
  mockHeaderText: { fontSize: 11, color: Colors.textMuted, marginLeft: 4, fontWeight: '600' },
  mockRow: { flexDirection: 'row', gap: 10, marginBottom: 10 },
  mockStat: {
    flex: 1, backgroundColor: Colors.background, borderRadius: 10, padding: 10,
    borderWidth: 1, borderColor: Colors.border,
  },
  mockStatVal: { fontSize: 20, fontWeight: '700', color: Colors.primary },
  mockStatLbl: { fontSize: 10, color: Colors.textMuted, marginTop: 2 },
  mockListItem: {
    flexDirection: 'row', alignItems: 'center', gap: 8, paddingVertical: 8,
    borderTopWidth: 1, borderTopColor: Colors.border,
  },
  mockListText: { flex: 1, fontSize: 12, color: Colors.text },
  mockBadge: { backgroundColor: Colors.primaryLight, paddingHorizontal: 8, paddingVertical: 3, borderRadius: 6 },
  mockBadgeText: { fontSize: 10, color: Colors.primary, fontWeight: '600' },
  phoneFrame: {
    alignSelf: 'center', width: 220, backgroundColor: Colors.surface,
    borderRadius: 20, borderWidth: 2, borderColor: Colors.border, overflow: 'hidden',
  },
  phoneNotch: { height: 24, backgroundColor: Colors.divider },
  phoneContent: { padding: 14 },
  phoneTitle: { fontSize: 14, fontWeight: '700', color: Colors.text, marginBottom: 10 },
  phoneCard: {
    flexDirection: 'row', alignItems: 'center', gap: 10,
    backgroundColor: Colors.background, borderRadius: 10, padding: 10, marginBottom: 8,
    borderWidth: 1, borderColor: Colors.border,
  },
  phoneCardTitle: { fontSize: 12, fontWeight: '600', color: Colors.text },
  phoneCardSub: { fontSize: 10, color: Colors.textMuted },
  phoneCameraBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6,
    backgroundColor: Colors.primary, borderRadius: 10, paddingVertical: 10, marginTop: 4,
  },
  phoneCameraText: { color: '#FFF', fontSize: 12, fontWeight: '600' },
  evidenceBox: {
    backgroundColor: Colors.surface, borderRadius: 12, overflow: 'hidden',
    borderWidth: 1, borderColor: Colors.border,
  },
  evidencePhoto: {
    height: 120, backgroundColor: Colors.divider, alignItems: 'center', justifyContent: 'center',
  },
  evidencePhotoLbl: { fontSize: 11, color: Colors.textMuted, marginTop: 6 },
  evidenceMeta: { padding: 12, gap: 6 },
  metaRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  metaText: { fontSize: 12, color: Colors.text, fontWeight: '500' },
  timelineBox: { gap: 0 },
  timelineItem: { flexDirection: 'row', alignItems: 'flex-start', minHeight: 36 },
  timelineLeft: { width: 28, alignItems: 'center' },
  timelineDot: {
    width: 22, height: 22, borderRadius: 11, backgroundColor: Colors.primary,
    alignItems: 'center', justifyContent: 'center', borderWidth: 2, borderColor: 'rgba(255,255,255,0.3)',
  },
  timelineDotLast: { backgroundColor: Colors.success },
  timelineLine: { width: 2, flex: 1, minHeight: 14, backgroundColor: 'rgba(255,255,255,0.25)' },
  timelineLabel: { color: '#FFF', fontSize: 13, fontWeight: '600', paddingTop: 2, flex: 1 },
  chainRow: {
    flexDirection: 'row', flexWrap: 'wrap', alignItems: 'center', gap: 4, justifyContent: 'center',
  },
  chainNode: {
    backgroundColor: 'rgba(255,255,255,0.12)', borderWidth: 1, borderColor: 'rgba(255,255,255,0.25)',
    paddingHorizontal: 8, paddingVertical: 6, borderRadius: 8,
  },
  chainNodeText: { color: '#FFF', fontSize: 10, fontWeight: '600' },
  logoFinal: { alignItems: 'center', marginBottom: 8 },
  logoBox: {
    width: 96, height: 96, borderRadius: 20, backgroundColor: Colors.primary,
    alignItems: 'center', justifyContent: 'center', borderWidth: 2, borderColor: 'rgba(255,255,255,0.25)',
  },
  closingIcons: { flexDirection: 'row', justifyContent: 'center', gap: 16 },
  closingIconWrap: {
    width: 48, height: 48, borderRadius: 12, backgroundColor: 'rgba(255,255,255,0.1)',
    alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.2)',
  },
  dotsRow: { flexDirection: 'row', justifyContent: 'center', gap: 6, marginBottom: 16 },
  dot: { width: 8, height: 8, borderRadius: 4, backgroundColor: 'rgba(255,255,255,0.35)' },
  dotActive: { backgroundColor: '#FFF', width: 22 },
  nextBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8,
    backgroundColor: Colors.primary, marginHorizontal: 24, marginBottom: 16,
    paddingVertical: 16, borderRadius: 12, borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)',
  },
  nextBtnText: { color: '#FFF', fontSize: 16, fontWeight: '700' },
});
