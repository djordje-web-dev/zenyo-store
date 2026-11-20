// Slika sve vazne stranice u zadatoj sirini ekrana (telefon, tablet, racunar) i javlja ako neka
// stranica "bezi" u stranu (vodoravno klizanje). Ne pravi porudzbinu; korpu puni samo u svom pregledacu.
//
//   node tools/tour.mjs <folder> [--width 390] [--height 844] [--lang sr|en] [--only cart,checkout]
//
// Primeri:
//   node tools/tour.mjs snimci/telefon --width 390
//   node tools/tour.mjs snimci/tablet --width 820 --height 1180
//   node tools/tour.mjs snimci/en --width 390 --lang en

import { spawn } from 'node:child_process';
import { writeFileSync, mkdtempSync, rmSync, existsSync, mkdirSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const args = process.argv.slice(2);
const opt = (name, fallback) => { const i = args.indexOf('--' + name); return i >= 0 ? args[i + 1] : fallback; };
const outDir = args[0] && !args[0].startsWith('--') ? args[0] : join(tmpdir(), 'zenyo-tour');
const width = parseInt(opt('width', '390'), 10);
const height = parseInt(opt('height', '844'), 10);
const lang = opt('lang', 'sr');
const only = opt('only', '').split(',').filter(Boolean);
const BASE = (process.env.ZENYO_URL || 'http://localhost:8090') + (lang === 'en' ? '/en' : '');
mkdirSync(outDir, { recursive: true });

// naziv snimka, adresa, sta uraditi pre snimanja (opciono)
const ADD_GI = `document.querySelector('[data-zy-option="white"]').click(); document.querySelector('[data-zy-option="170"]').click(); document.querySelector('[data-zy-add]').click(); await new Promise(r=>setTimeout(r,2500));`;
const ADD_BELT = `const a=document.querySelectorAll('[data-zy-product]')[0]; a.querySelector('[data-zy-option="blue"]').click(); a.querySelector('[data-zy-option="280"]').click(); a.querySelector('[data-zy-add]').click(); await new Promise(r=>setTimeout(r,2500)); a.querySelector('[data-zy-add]').click(); await new Promise(r=>setTimeout(r,2500));`;
const PAGES = [
  ['01-home', '/'],
  ['02-shop', '/shop/'],
  ['03-list', '/shop/?view=all'],
  ['04-tiles', '/product-category/zenyo/'],
  ['05-product', '/product/training-judo-gi/', ADD_GI],
  ['06-belts', '/product-category/belts/', ADD_BELT],
  ['07-gallery', '/product-category/zenyo/art/', `document.querySelectorAll('[data-zy-item]')[1].click(); await new Promise(r=>setTimeout(r,600));`],
  ['08-cart', '/cart/'],
  ['09-checkout', '/checkout/'],
  ['10-checkout-errors', '/checkout/', `document.querySelector('#place_order').click(); await new Promise(r=>setTimeout(r,4500));`],
  ['11-faq', '/faq/'],
  ['12-terms', '/terms/'],
  ['13-contact', '/contact/'],
  ['14-size-guide', '/product/training-judo-gi/', `document.querySelector('.zy-textlink[data-zy-open]').click(); await new Promise(r=>setTimeout(r,700));`, 'viewport'],
  ['15-phone-panel', '/shop/', `document.querySelector('[data-zy-toggle="zy-phone-panel"]').click(); await new Promise(r=>setTimeout(r,400));`, 'viewport'],
  ['16-cart-empty', '/cart/', `for (const a of document.querySelectorAll('a.remove')) { await fetch(a.href, { credentials: 'same-origin' }); }`, 'reload'],
];

const chromePath = [
  process.env.CHROME_PATH,
  'C:/Program Files/Google/Chrome/Application/chrome.exe',
  'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
].find((p) => p && existsSync(p));

const port = 9300 + Math.floor(Math.random() * 500);
const profile = mkdtempSync(join(tmpdir(), 'zy-tour-'));
const chrome = spawn(chromePath, [
  '--headless=new', '--disable-gpu', '--hide-scrollbars', '--no-first-run',
  `--remote-debugging-port=${port}`, `--user-data-dir=${profile}`, `--window-size=${width},${height}`, 'about:blank',
], { stdio: 'ignore' });
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
let problems = 0;

try {
  for (let i = 0; i < 60; i++) {
    try { if ((await fetch(`http://127.0.0.1:${port}/json/version`)).ok) break; } catch {}
    await sleep(250);
  }
  const target = await (await fetch(`http://127.0.0.1:${port}/json/new?about:blank`, { method: 'PUT' })).json();
  const ws = new WebSocket(target.webSocketDebuggerUrl);
  await new Promise((resolve, reject) => { ws.onopen = resolve; ws.onerror = reject; });
  let nextId = 1;
  const pending = new Map();
  let onLoad = null;
  ws.onmessage = (event) => {
    const msg = JSON.parse(event.data);
    if (msg.id && pending.has(msg.id)) {
      const { resolve, reject } = pending.get(msg.id);
      pending.delete(msg.id);
      msg.error ? reject(new Error(msg.error.message)) : resolve(msg.result);
    } else if (msg.method === 'Page.loadEventFired' && onLoad) { onLoad(); onLoad = null; }
  };
  const send = (method, params = {}) => new Promise((resolve, reject) => {
    const id = nextId++;
    pending.set(id, { resolve, reject });
    ws.send(JSON.stringify({ id, method, params }));
  });
  const run = async (js) => {
    const res = await send('Runtime.evaluate', { expression: `(async()=>{${js}})()`, awaitPromise: true, returnByValue: true });
    if (res.exceptionDetails) return 'GRESKA: ' + (res.exceptionDetails.exception?.description || res.exceptionDetails.text).split('\n')[0];
    return res.result.value;
  };

  await send('Page.enable');
  const mobile = width < 1000;
  await send('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile });
  if (mobile) await send('Emulation.setTouchEmulationEnabled', { enabled: true });

  for (const [name, path, before, mode] of PAGES) {
    if (only.length && !only.some((o) => name.includes(o))) {
      // Stranice koje pune korpu moraju da se otvore i kad se ne slikaju.
      if (!(before === ADD_GI || before === ADD_BELT)) continue;
    }
    const loaded = Promise.race([new Promise((r) => { onLoad = r; }), sleep(20000)]);
    await send('Page.navigate', { url: BASE + path });
    await loaded;
    await sleep(900);
    let note = '';
    if (before) { const r = await run(before); if (typeof r === 'string' && r.startsWith('GRESKA')) note = ' ' + r; await sleep(400); }
    if (mode === 'reload') {
      const again = Promise.race([new Promise((r) => { onLoad = r; }), sleep(20000)]);
      await send('Page.navigate', { url: BASE + path });
      await again;
      await sleep(900);
    }
    if (only.length && !only.some((o) => name.includes(o))) continue;

    // Sta viri preko desne ivice ekrana?
    const overflow = await run(`
      const w = document.documentElement.clientWidth;
      const bad = [];
      for (const el of document.querySelectorAll('body *')) {
        const r = el.getBoundingClientRect();
        if (r.width > 0 && r.right > w + 1 && getComputedStyle(el).position !== 'fixed') {
          let hidden = false;
          for (let p = el.parentElement; p && p !== document.body; p = p.parentElement) {
            const o = getComputedStyle(p); if (o.overflowX !== 'visible' || o.overflow !== 'visible') { hidden = true; break; }
          }
          if (!hidden) bad.push((el.className && typeof el.className === 'string' ? '.' + el.className.split(' ')[0] : el.tagName.toLowerCase()) + ' +' + Math.round(r.right - w));
        }
      }
      return { scroll: document.documentElement.scrollWidth - w, bad: [...new Set(bad)].slice(0, 6) };
    `);
    const { cssContentSize } = await send('Page.getLayoutMetrics');
    const clip = mode === 'viewport'
      ? { x: 0, y: 0, width, height, scale: 1 }
      : { x: 0, y: 0, width, height: Math.min(cssContentSize.height, 9000), scale: 1 };
    const { data } = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: mode !== 'viewport', clip });
    writeFileSync(join(outDir, name + '.png'), Buffer.from(data, 'base64'));
    const wide = overflow && (overflow.scroll > 1 || overflow.bad.length);
    if (wide || note) problems++;
    console.log(`${wide || note ? 'PAZI' : 'ok  '} ${name}  visina ${Math.round(cssContentSize.height)}${wide ? `  viri: ${overflow.scroll}px  ${overflow.bad.join(', ')}` : ''}${note}`);
  }
  ws.close();
} catch (e) {
  console.error('PREKID:', e.message);
  problems++;
} finally {
  chrome.kill();
  await sleep(300);
  try { rmSync(profile, { recursive: true, force: true }); } catch {}
  console.log(`\nSnimci: ${outDir}   sirina ${width}px, jezik ${lang}` + (problems ? `   (${problems} za proveru)` : ''));
  process.exit(0);
}
