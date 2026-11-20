// Snimak ekrana stranice pomocu Chrome-a, bez dodatnih paketa (treba Node 22+).
//
//   node tools/shot.mjs <adresa> <izlaz.png> [--width 1440] [--height 900] [--full] [--eval "js kod"]
//
//   --full   snima celu stranicu, ne samo prvi ekran
//   --eval   izvrsi JS na stranici pre snimanja (npr. klik na dugme)
//
// Primer:
//   node tools/shot.mjs http://localhost:8090/cart/ cart.png --full

import { spawn } from 'node:child_process';
import { writeFileSync, mkdtempSync, rmSync, existsSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const args = process.argv.slice(2);
const [url, out] = args;
const opt = (name, fallback) => {
  const i = args.indexOf('--' + name);
  return i === -1 ? fallback : args[i + 1];
};
const width = Number(opt('width', 1440));
const height = Number(opt('height', 900));
const full = args.includes('--full');
const evalJs = opt('eval', '');

if (!url || !out) {
  console.error('Upotreba: node tools/shot.mjs <adresa> <izlaz.png> [--width N] [--height N] [--full] [--eval "js"]');
  process.exit(1);
}

const chromePath = [
  process.env.CHROME_PATH,
  'C:/Program Files/Google/Chrome/Application/chrome.exe',
  'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
].find((p) => p && existsSync(p));
if (!chromePath) {
  console.error('Nije nadjen Chrome ni Edge. Postavi promenljivu CHROME_PATH.');
  process.exit(1);
}

const port = 9300 + Math.floor(Math.random() * 500);
const profile = mkdtempSync(join(tmpdir(), 'zy-shot-'));
const chrome = spawn(chromePath, [
  '--headless=new', '--disable-gpu', '--hide-scrollbars', '--no-first-run',
  `--remote-debugging-port=${port}`, `--user-data-dir=${profile}`,
  `--window-size=${width},${height}`, 'about:blank',
], { stdio: 'ignore' });

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function waitForChrome() {
  for (let i = 0; i < 60; i++) {
    try {
      const res = await fetch(`http://127.0.0.1:${port}/json/version`);
      if (res.ok) return;
    } catch {}
    await sleep(250);
  }
  throw new Error('Chrome se nije pokrenuo.');
}

try {
  await waitForChrome();
  const target = await (await fetch(`http://127.0.0.1:${port}/json/new?about:blank`, { method: 'PUT' })).json();
  const ws = new WebSocket(target.webSocketDebuggerUrl);
  await new Promise((resolve, reject) => { ws.onopen = resolve; ws.onerror = reject; });

  let nextId = 1;
  const pending = new Map();
  const waiters = new Map();
  ws.onmessage = (event) => {
    const msg = JSON.parse(event.data);
    if (msg.id && pending.has(msg.id)) {
      const { resolve, reject } = pending.get(msg.id);
      pending.delete(msg.id);
      msg.error ? reject(new Error(msg.error.message)) : resolve(msg.result);
    } else if (msg.method && waiters.has(msg.method)) {
      waiters.get(msg.method)();
      waiters.delete(msg.method);
    }
  };
  const send = (method, params = {}) => new Promise((resolve, reject) => {
    const id = nextId++;
    pending.set(id, { resolve, reject });
    ws.send(JSON.stringify({ id, method, params }));
  });
  const once = (method) => new Promise((resolve) => waiters.set(method, resolve));

  await send('Page.enable');
  await send('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile: width < 700 });
  const loaded = once('Page.loadEventFired');
  await send('Page.navigate', { url });
  await Promise.race([loaded, sleep(20000)]);
  await sleep(1200); // fontovi i slike

  if (evalJs) {
    const res = await send('Runtime.evaluate', { expression: evalJs, awaitPromise: true, returnByValue: true });
    if (res.exceptionDetails) console.error('Greska u --eval:', res.exceptionDetails.text);
    else if (res.result && res.result.value !== undefined) console.log('eval:', JSON.stringify(res.result.value));
    await sleep(500);
  }

  const params = { format: 'png' };
  if (full) {
    const { cssContentSize } = await send('Page.getLayoutMetrics');
    params.captureBeyondViewport = true;
    params.clip = { x: 0, y: 0, width: cssContentSize.width, height: cssContentSize.height, scale: 1 };
  }
  const { data } = await send('Page.captureScreenshot', params);
  writeFileSync(out, Buffer.from(data, 'base64'));
  console.log('Sacuvano:', out);
  ws.close();
} finally {
  chrome.kill();
  await sleep(300);
  try { rmSync(profile, { recursive: true, force: true }); } catch {}
}
