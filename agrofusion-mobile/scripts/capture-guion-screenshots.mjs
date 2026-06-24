/**
 * Captura automática de pantallas para el guion AgroFusion (Expo Web + Playwright).
 * Uso: node scripts/capture-guion-screenshots.mjs
 */
import { chromium } from 'playwright';
import { mkdir } from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUT = path.join(__dirname, '..', 'capturas-guion');
const BASE = process.env.EXPO_WEB_URL || 'http://localhost:8081';
const VIEWPORT = { width: 390, height: 844 }; // iPhone 14 Pro

async function shot(page, name) {
  const file = path.join(OUT, `${name}.png`);
  await page.screenshot({ path: file, fullPage: false, timeout: 60000, animations: 'disabled' });
  console.log('OK', file);
}

async function clickText(page, text, opts = {}) {
  const loc = page.getByText(text, { exact: opts.exact ?? false }).first();
  await loc.waitFor({ state: 'visible', timeout: 30000 });
  await loc.click({ timeout: 15000 });
  await page.waitForTimeout(opts.wait ?? 1200);
}

async function waitApp(page) {
  await page.goto(BASE, { waitUntil: 'load', timeout: 90000 });
  await page.waitForFunction(
    () => document.body?.innerText?.includes('AgroFusion') || document.body?.innerText?.includes('Bienvenido'),
    { timeout: 90000 },
  );
  await page.waitForTimeout(2500);
}

async function main() {
  await mkdir(OUT, { recursive: true });

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: VIEWPORT,
    deviceScaleFactor: 2,
    locale: 'es-BO',
  });
  const page = await context.newPage();

  try {
    console.log('Abriendo', BASE);
    await waitApp(page);
    await shot(page, '01-login');

    // Presentación — slides clave del guion
    await clickText(page, 'Conoce la plataforma');
    await page.waitForTimeout(1200);
    await shot(page, '02-presentacion-slide-01-intro');

    for (let i = 2; i <= 13; i++) {
      const btn = page.getByText(i === 13 ? 'Comenzar en AgroFusion' : 'Continuar').first();
      if (await btn.isVisible().catch(() => false)) {
        if (i === 13) {
          await shot(page, '13-presentacion-slide-13-logo');
          break;
        }
        await btn.click();
        await page.waitForTimeout(900);
        const keySlides = { 3: '03-presentacion-agrofusion', 4: '04-presentacion-actores', 7: '07-presentacion-app-movil', 8: '08-presentacion-evidencia', 9: '09-presentacion-timeline', 13: '13-presentacion-logo' };
        if (keySlides[i]) await shot(page, keySlides[i]);
      }
    }

    // Volver al login si salimos de presentación
    await waitApp(page);

    // Demo agricultor
    await clickText(page, 'Agricultor de campo');
    await page.waitForTimeout(1500);
    await shot(page, '10-dashboard-agricultor');

    await clickText(page, 'Mis Actividades', { wait: 1500 });
    await shot(page, '11-mis-actividades');

    await clickText(page, 'Siembra de lechuga romana', { wait: 1500 });
    await shot(page, '12-completar-siembra-comprobante');

    // Nueva sesión agricultor → Mis Lotes
    await page.evaluate(() => localStorage.clear());
    await waitApp(page);
    await clickText(page, 'Agricultor de campo', { wait: 2000 });
    const misLotes = page.getByText('Mis Lotes', { exact: true }).first();
    await misLotes.waitFor({ state: 'attached', timeout: 15000 });
    await misLotes.click({ force: true });
    await page.waitForTimeout(1500);
    await shot(page, '13-mis-lotes');

    await clickText(page, 'Lote Sur B', { wait: 1500 });
    await shot(page, '14-detalle-lote-timeline');

    // Transportista — limpiar sesión demo
    await page.evaluate(() => {
      Object.keys(localStorage).forEach((k) => {
        if (k.includes('auth') || k.includes('user') || k.includes('token')) localStorage.removeItem(k);
      });
    });
    await waitApp(page);

    await clickText(page, 'Transportista', { wait: 1500 });
    await shot(page, '15-dashboard-transportista');

    await clickText(page, 'Mis Rutas', { wait: 1500 });
    await shot(page, '16-rutas-lista');

    await clickText(page, 'Ruta Mercado Campesino', { wait: 1500 });
    await shot(page, '17-ruta-detalle-vehiculo');

    // Slide final presentación
    await page.evaluate(() => {
      Object.keys(localStorage).forEach((k) => localStorage.clear());
    });
    await waitApp(page);
    await clickText(page, 'Conoce la plataforma');
    for (let i = 0; i < 12; i++) {
      await page.getByText('Continuar').first().click();
      await page.waitForTimeout(700);
    }
    await shot(page, '18-presentacion-slide-13-logo');

    console.log('\nCapturas guardadas en:', OUT);
  } catch (err) {
    console.error('Error capturando:', err.message);
    await shot(page, 'ERROR-estado-final');
    process.exitCode = 1;
  } finally {
    await browser.close();
  }
}

main();
