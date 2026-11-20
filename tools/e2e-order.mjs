// Proba cele kupovine u pravom pregledacu (Chrome), od dodavanja u korpu do stranice "Hvala" i mejlova.
// Pokretanje:  node tools/e2e-order.mjs [folder-za-snimke]            (srpski sajt)
//              ZENYO_LANG=en node tools/e2e-order.mjs [folder]         (engleski sajt, adrese sa /en/)
//              ZENYO_WIDTH=390 node tools/e2e-order.mjs [folder]       (u sirini telefona)
// Pravi PRAVU probnu porudzbinu (pouzecem) na lokalnom sajtu. Treba Node 22+ i pokrenut Docker.

import { spawn } from 'node:child_process';
import { writeFileSync, mkdtempSync, rmSync, existsSync, mkdirSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const BASE = process.env.ZENYO_URL || 'http://localhost:8090';
const MAIL = process.env.ZENYO_MAIL || 'http://localhost:8091';
const LANG = process.env.ZENYO_LANG === 'en' ? 'en' : 'sr';
const PREFIX = LANG === 'en' ? '/en' : '';
// Tekstovi koje proba ocekuje na stranici, po jeziku.
const T = {
  sr: { htmlLang: 'sr-Latn-RS', cart: 'Vaša korpa', cod: /pouzećem/i, items: /4 artikla/, thanks: 'Hvala', confirmed: /potvrđena/, delivery: /Standardna dostava/, payStatus: /Plaćanje pouzećem/, orderWord: /porudžbin/i, newOrder: /novu porudžbinu|new order/i, mailBody: /porudžbin/i },
  en: { htmlLang: 'en', cart: 'Your cart', cod: /cash on delivery/i, items: /4 items/, thanks: 'Thank you', confirmed: /confirmed/, delivery: /Standard delivery/, payStatus: /Payment on delivery/, orderWord: /order/i, newOrder: /novu porudžbinu|new order/i, mailBody: /order/i },
}[LANG];
// ZENYO_WIDTH=390 proba istu kupovinu u sirini telefona.
const WIDTH = parseInt(process.env.ZENYO_WIDTH || '1440', 10);
const HEIGHT = WIDTH < 1000 ? 844 : 900;
const outDir = process.argv[2] || join(tmpdir(), 'zenyo-e2e');
mkdirSync(outDir, { recursive: true });

const chromePath = [
  process.env.CHROME_PATH,
  'C:/Program Files/Google/Chrome/Application/chrome.exe',
  'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
].find((p) => p && existsSync(p));

const port = 9300 + Math.floor(Math.random() * 500);
const profile = mkdtempSync(join(tmpdir(), 'zy-e2e-'));
const chrome = spawn(chromePath, [
  '--headless=new', '--disable-gpu', '--hide-scrollbars', '--no-first-run',
  `--remote-debugging-port=${port}`, `--user-data-dir=${profile}`, `--window-size=${WIDTH},${HEIGHT}`, 'about:blank',
], { stdio: 'ignore' });

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const results = [];
let failed = 0;
function check(name, ok, detail = '') {
  results.push(`${ok ? 'OK  ' : 'FAIL'} ${name}${detail ? '  -> ' + detail : ''}`);
  if (!ok) failed++;
}

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
  const waitLoad = () => Promise.race([new Promise((r) => { onLoad = r; }), sleep(20000)]);

  await send('Page.enable');
  await send('Emulation.setDeviceMetricsOverride', { width: WIDTH, height: HEIGHT, deviceScaleFactor: 1, mobile: WIDTH < 1000 });

  const goto = async (path) => { const l = waitLoad(); await send('Page.navigate', { url: BASE + PREFIX + path }); await l; await sleep(900); };
  const run = async (js) => {
    const res = await send('Runtime.evaluate', { expression: `(async()=>{${js}})()`, awaitPromise: true, returnByValue: true });
    if (res.exceptionDetails) throw new Error(res.exceptionDetails.exception?.description || res.exceptionDetails.text);
    return res.result.value;
  };
  const shot = async (name) => {
    const { cssContentSize } = await send('Page.getLayoutMetrics');
    const { data } = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, clip: { x: 0, y: 0, width: cssContentSize.width, height: cssContentSize.height, scale: 1 } });
    writeFileSync(join(outDir, name + '.png'), Buffer.from(data, 'base64'));
  };
  const H = `const q=(s,r=document)=>r.querySelector(s); const qa=(s,r=document)=>[...r.querySelectorAll(s)]; const wait=ms=>new Promise(r=>setTimeout(r,ms)); const txt=e=>e?e.textContent.replace(/\\s+/g,' ').trim():'';`;

  // --- 1. Punjenje korpe sa tri razlicite vrste stranica ---
  await goto('/product-category/zenyo/jewellery/');
  await run(`${H} qa('[data-zy-item]')[1].click(); q('[data-zy-add]').click(); await wait(2500);`);
  await goto('/product/training-judo-gi/');
  await run(`${H} q('[data-zy-option="white"]').click(); q('[data-zy-option="170"]').click(); q('[data-zy-add]').click(); await wait(2500);`);
  await goto('/product-category/belts/');
  const count = await run(`${H} const a=qa('[data-zy-product]')[0]; q('[data-zy-option="blue"]',a).click(); q('[data-zy-option="280"]',a).click(); q('[data-zy-add]',a).click(); await wait(2500); q('[data-zy-add]',a).click(); await wait(2500); return txt(q('.zy-cart__count'));`);
  check('brojac korpe posle 4 dodavanja', count === '4', count);

  // --- 2. Korpa ---
  await goto('/cart/');
  const cart1 = await run(`${H} return { title: txt(q('.zy-flow__head h1')), lines: qa('.zy-cartx__row').map(r=>[txt(q('.zy-cartx__name',r)), txt(q('.zy-cartx__variant',r)), q('input.qty',r).value, txt(q('.zy-cartx__total',r))].join(' / ')), sub: txt(q('.zy-summary__rows div:first-child')), ship: txt(q('.zy-summary__rows div:nth-child(2) dd')), total: txt(q('.zy-summary__amount')), recs: qa('.zy-rec h3').map(txt) };`);
  const htmlLang = await run(`return document.documentElement.lang;`);
  check(`jezik stranice je ${LANG}`, htmlLang === T.htmlLang, htmlLang);
  check('korpa: naslov', cart1.title === T.cart, cart1.title);
  check('korpa: tri stavke', cart1.lines.length === 3, cart1.lines.join(' || '));
  check('korpa: ukupno 8.490 + 2x1.100 + 1.290 + 390 = 12.370', /12\.370/.test(cart1.total), `${cart1.sub} | dostava ${cart1.ship} | ${cart1.total}`);
  check('korpa: dve preporuke kojih nema u korpi', cart1.recs.length === 2, cart1.recs.join(' | '));
  await shot('1-cart');

  const cart2 = await run(`${H} const row=qa('.zy-cartx__row').find(r=>/Torii/i.test(r.textContent)); q('[data-zy-step="1"]',row).click(); await wait(4500); const r2=qa('.zy-cartx__row').find(r=>/Torii/i.test(r.textContent)); return { qty:q('input.qty',r2).value, line:txt(q('.zy-cartx__total',r2)), total:txt(q('.zy-summary__amount')), header:txt(q('.zy-cart__count')) };`);
  check('korpa: + povecava kolicinu bez ucitavanja stranice', cart2.qty === '2' && /2\.580/.test(cart2.line) && /13\.660/.test(cart2.total), JSON.stringify(cart2));
  check('korpa: brojac u zaglavlju prati promenu', cart2.header === '5', cart2.header);
  const cart3 = await run(`${H} const row=qa('.zy-cartx__row').find(r=>/Torii/i.test(r.textContent)); q('[data-zy-step="-1"]',row).click(); await wait(4500); return txt(q('.zy-summary__amount'));`);
  check('korpa: - vraca kolicinu', /12\.370/.test(cart3), cart3);

  // --- 3. Naplata: provere polja ---
  await goto('/checkout/');
  const co1 = await run(`${H} return { title: txt(q('.zy-flow__head h1')), steps: qa('.zy-step__title').map(txt), fields: qa('.zy-step__fields .form-row').filter(r=>getComputedStyle(r).display!=='none').map(r=>txt(q('label',r))), ship: qa('.zy-ship-methods .zy-option').map(txt), pay: qa('#payment .zy-option').map(txt), summary: txt(q('.zy-summary__title')), total: txt(q('.zy-summary__amount')) };`);
  check('naplata: cetiri odeljka', co1.steps.length === 4, co1.steps.join(' | '));
  check('naplata: polja iz dizajna', co1.fields.length === 8, co1.fields.join(' | '));
  check('naplata: dva nacina dostave', co1.ship.length === 2, co1.ship.join(' | '));
  check('naplata: pouzece ponudjeno', co1.pay.some((p) => T.cod.test(p)), co1.pay.join(' | '));
  check('naplata: pregled i ukupno', T.items.test(co1.summary) && /12\.370/.test(co1.total), `${co1.summary} | ${co1.total}`);
  await shot('2-checkout');

  const err1 = await run(`${H} q('#place_order').click(); await wait(5000); return { errors: qa('.zy-field-error').map(e=>e.closest('.form-row').id.replace('_field','')+': '+txt(e)), top: txt(q('.woocommerce-NoticeGroup-checkout')), url: location.pathname };`);
  check('naplata: prazna polja daju gresku ispod svakog polja', err1.errors.length === 7 && err1.url.includes('checkout'), err1.errors.join(' | ') + (err1.top ? ' || na vrhu: ' + err1.top : ''));
  await shot('3-checkout-errors');

  const fill = `const set=(id,v)=>{const e=q('#'+id); e.value=v; e.dispatchEvent(new Event('input',{bubbles:true})); e.dispatchEvent(new Event('change',{bubbles:true}));};`;
  const err2 = await run(`${H} ${fill} set('billing_first_name','Test'); set('billing_last_name','Kupac'); set('billing_email','kupac@example.com'); set('billing_phone','12345'); set('billing_address_1','Probna ulica 1'); set('billing_city','Novi Sad'); set('billing_postcode','21'); set('order_comments','Probna porudzbina - interfon 5'); await wait(600); q('#place_order').click(); await wait(5000); return qa('.zy-field-error').map(e=>e.closest('.form-row').id.replace('_field','')+': '+txt(e));`);
  check('naplata: los telefon i postanski broj se odbijaju', err2.length === 2 && err2.some((e) => /phone/i.test(e)) && err2.some((e) => /postcode/i.test(e)), err2.join(' | '));

  // --- 4. Naplata: dostava menja ukupan iznos, pa porudzbina ---
  const ship = await run(`${H} ${fill} set('billing_phone','+381 64 123 4567'); set('billing_postcode','21000'); await wait(3500); const radios=qa('.zy-ship-methods input'); radios[1].click(); await wait(4500); const express=txt(q('.zy-summary__amount')); qa('.zy-ship-methods input')[0].click(); await wait(4500); return { express, standard: txt(q('.zy-summary__amount')), kept: q('#billing_first_name').value };`);
  check('naplata: ekspres dostava menja ukupno na 12.570, standardna vraca na 12.370', /12\.570/.test(ship.express) && /12\.370/.test(ship.standard), JSON.stringify(ship));

  const l = waitLoad();
  const terms = await run(`${H} const t=q('#terms'); if(!t) return 'nema'; q('#place_order').click(); await wait(5000); const blocked=location.pathname.includes('checkout') && !location.pathname.includes('order-received'); t.checked=true; t.dispatchEvent(new Event('change',{bubbles:true})); return blocked ? 'trazi' : 'ne trazi';`);
  check('naplata: bez prihvatanja uslova kupovine porudzbina ne prolazi', terms === 'trazi', terms);
  await run(`${H} q('#payment_method_cod').checked=true; q('#place_order').click();`);
  await l; await sleep(2500);
  const ty = await run(`${H} return { url: location.pathname, title: txt(q('.zy-flow__head h1')), headline: txt(q('.zy-thanks__headline')), number: txt(q('.zy-thanks__number')), sections: qa('.zy-thanks__section').map(s=>txt(s)), lines: qa('.zy-summary__line').map(txt), total: txt(q('.zy-summary__amount')), cart: txt(q('.zy-cart__count')), dupTable: !!q('.woocommerce-order-details') };`);
  check('porudzbina: stize se na stranicu "Thank you"', /order-received/.test(ty.url) && ty.title === T.thanks, `${ty.url} | ${ty.title}`);
  check('porudzbina: broj i potvrda', /^#\d+$/.test(ty.number) && T.confirmed.test(ty.headline), `${ty.number} | ${ty.headline}`);
  check('porudzbina: dostava, adresa i napomena', T.delivery.test(ty.sections[0]) && /Probna ulica 1/.test(ty.sections[0]) && /interfon 5/.test(ty.sections[0]), ty.sections[0]);
  check('porudzbina: placanje pouzecem', T.cod.test(ty.sections[1]) && T.payStatus.test(ty.sections[1]), ty.sections[1]);
  check('porudzbina: stavke i ukupno', ty.lines.length === 3 && /12\.370/.test(ty.total), ty.lines.join(' || ') + ' | ' + ty.total);
  check('porudzbina: korpa je ispraznjena', ty.cart === '0', ty.cart);
  check('porudzbina: nema duple WooCommerce tabele', !ty.dupTable);
  await shot('4-thankyou');

  // --- 5. Mejlovi ---
  await sleep(1500);
  let mails = [];
  try {
    const m = await (await fetch(`${MAIL}/api/v1/messages?limit=10`)).json();
    const list = (m.messages || []).slice(0, 6).filter((x) => x.Subject.includes(ty.number.replace('#', '')) || T.orderWord.test(x.Subject)).slice(0, 2);
    mails = list.map((x) => `${x.To.map((t) => t.Address).join(',')}: ${x.Subject}`);
    const toCustomer = list.find((x) => x.To.some((t) => t.Address === 'kupac@example.com'));
    if (toCustomer) {
      const body = await (await fetch(`${MAIL}/api/v1/message/${toCustomer.ID}`)).json();
      const text = (body.Text || '') + ' ' + toCustomer.Subject;
      const cyrillic = /[Ѐ-ӿ]/.test(text);
      check(`mejlovi: mejl kupcu je na jeziku kupovine (${LANG}), bez cirilice`, T.mailBody.test(text) && !cyrillic && (LANG === 'en' ? !/porudžbin/i.test(text) : true), toCustomer.Subject);
    }
  } catch (e) { mails = ['Mailpit nedostupan: ' + e.message]; }
  check('mejlovi: stigao mejl kupcu', mails.some((m) => m.startsWith('kupac@example.com')), mails.join(' || '));
  check('mejlovi: stiglo obavestenje prodavcu', mails.some((m) => !m.startsWith('kupac@example.com') && T.newOrder.test(m)), mails.join(' || '));

  console.log(results.join('\n'));
  console.log(`\nBroj porudzbine: ${ty.number}   Snimci: ${outDir}`);
  console.log(failed ? `\n${failed} PROVERA NIJE PROSLO` : '\nSVE PROVERE SU PROSLE');
  ws.close();
} catch (e) {
  console.log(results.join('\n'));
  console.error('\nPREKID:', e.message);
  failed++;
} finally {
  chrome.kill();
  await sleep(300);
  try { rmSync(profile, { recursive: true, force: true }); } catch {}
  process.exit(failed ? 1 : 0);
}
