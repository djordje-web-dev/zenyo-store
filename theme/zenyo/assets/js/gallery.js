/**
 * Stranica sa galerijom i jednim delom za kupovinu (Art, Jewellery, Accessories).
 *
 * Klik na sliku bira proizvod u padajucoj listi. Lista velicina, opis i cena prate izabrani proizvod,
 * a cena se mnozi kolicinom. Podaci stizu sa servera u <script id="zy-gallery-data">:
 *   products: [{ id, name, text, material, details: [[naziv, vrednost]], price, priceText,
 *                sizeLabel, variations: [{ id, value, name, price, inStock }] }]
 */
(function () {
  'use strict';

  var root = document.querySelector('[data-zy-gallery]');
  var dataEl = document.getElementById('zy-gallery-data');
  if (!root || !dataEl) return;

  var data = JSON.parse(dataEl.textContent);
  var t = data.i18n;
  var byId = {};
  data.products.forEach(function (p) { byId[p.id] = p; });

  var productSelect = root.querySelector('[data-zy-pick]');
  var sizeWrap = root.querySelector('[data-zy-size-wrap]');
  var sizeLabel = root.querySelector('[data-zy-size-label]');
  var sizeSelect = root.querySelector('[data-zy-size]');
  var qtyEl = root.querySelector('[data-zy-qty]');
  var decBtn = root.querySelector('[data-zy-dec]');
  var incBtn = root.querySelector('[data-zy-inc]');
  var priceEl = root.querySelector('[data-zy-price]');
  var titleEl = root.querySelector('[data-zy-info-title]');
  var textEl = root.querySelector('[data-zy-info-text]');
  var materialEl = root.querySelector('[data-zy-info-material]');
  var detailsEl = root.querySelector('[data-zy-info-details]');
  var ruleEl = root.querySelector('[data-zy-info-rule]');
  var addBtn = root.querySelector('[data-zy-add]');
  var statusEl = root.querySelector('[data-zy-status]');

  var qty = 1;
  var sizeValue = ''; // pamti izabranu velicinu i kad se promeni proizvod

  /** 5000 -> "5.000 RSD", po podesavanjima prodavnice. */
  function money(amount) {
    var f = data.format;
    var parts = Number(amount).toFixed(f.decimals).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, f.thousand);
    var number = parts.join(f.decimal);
    return f.pattern.replace('%v', number).replace('%s', f.symbol);
  }

  function current() { return byId[productSelect.value]; }

  /** Varijacija koja odgovara izabranoj velicini (ili null ako proizvod nema velicine). */
  function currentVariation(p) {
    if (!p.variations.length) return null;
    return p.variations.filter(function (v) { return v.value === sizeSelect.value; })[0] || null;
  }

  function setStatus(text) { statusEl.textContent = text || ''; }

  function renderSizes(p) {
    if (!p.variations.length) {
      sizeWrap.hidden = true;
      return;
    }
    sizeWrap.hidden = false;
    sizeLabel.textContent = p.sizeLabel;
    sizeSelect.innerHTML = '';
    p.variations.forEach(function (v) {
      var option = document.createElement('option');
      option.value = v.value;
      option.textContent = v.name + (v.inStock ? '' : ' — ' + t.soldOut);
      option.disabled = !v.inStock;
      sizeSelect.appendChild(option);
    });
    // Zadrzi velicinu od prethodnog proizvoda ako je i ovaj ima; inace prva dostupna.
    var keep = p.variations.filter(function (v) { return v.value === sizeValue && v.inStock; })[0];
    var first = p.variations.filter(function (v) { return v.inStock; })[0];
    var pick = keep || first;
    if (pick) sizeSelect.value = pick.value;
    sizeValue = sizeSelect.value;
  }

  function renderPrice(p) {
    var variation = currentVariation(p);
    var unit = variation ? variation.price : p.price;
    var canBuy = variation ? variation.inStock : p.canBuy;
    if (p.variations.length && !variation) canBuy = false;

    if (unit === null || unit === undefined) priceEl.textContent = '';
    else priceEl.textContent = money(unit * qty);

    addBtn.disabled = !canBuy;
    addBtn.textContent = canBuy ? t.add : t.soldOut;
    decBtn.disabled = qty <= 1;
    qtyEl.textContent = String(qty);
  }

  function renderInfo(p) {
    titleEl.textContent = p.name;
    textEl.textContent = p.text;
    textEl.hidden = !p.text;
    materialEl.textContent = p.material;
    materialEl.hidden = !p.material;

    detailsEl.innerHTML = '';
    p.details.forEach(function (row) {
      var wrap = document.createElement('div');
      var dt = document.createElement('dt');
      var dd = document.createElement('dd');
      dt.textContent = row[0];
      dd.textContent = row[1];
      wrap.appendChild(dt);
      wrap.appendChild(dd);
      detailsEl.appendChild(wrap);
    });
    detailsEl.hidden = !p.details.length;
    ruleEl.hidden = !p.details.length;

    // Oznaci izabranu sliku u galeriji.
    Array.prototype.slice.call(root.querySelectorAll('[data-zy-item]')).forEach(function (item) {
      item.setAttribute('aria-pressed', item.getAttribute('data-zy-item') === String(p.id) ? 'true' : 'false');
    });
  }

  function render() {
    var p = current();
    if (!p) return;
    renderSizes(p);
    renderInfo(p);
    renderPrice(p);
  }

  productSelect.addEventListener('change', function () { setStatus(''); render(); });

  sizeSelect.addEventListener('change', function () {
    sizeValue = sizeSelect.value;
    setStatus('');
    renderPrice(current());
  });

  decBtn.addEventListener('click', function () { qty = Math.max(1, qty - 1); setStatus(''); renderPrice(current()); });
  incBtn.addEventListener('click', function () { qty += 1; setStatus(''); renderPrice(current()); });

  // Klik na sliku bira taj proizvod i dovodi deo za kupovinu u vidno polje.
  root.addEventListener('click', function (event) {
    var item = event.target.closest('[data-zy-item]');
    if (!item) return;
    productSelect.value = item.getAttribute('data-zy-item');
    setStatus('');
    render();
    var buy = root.querySelector('[data-zy-buy]');
    if (buy && buy.scrollIntoView) buy.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  });

  addBtn.addEventListener('click', function () {
    var p = current();
    var variation = currentVariation(p);
    var id = variation ? variation.id : p.id;
    var unit = variation ? variation.price : p.price;
    var summary = [p.name];
    if (variation) summary.push(variation.name);
    summary.push(t.qty.replace('%d', String(qty)));
    summary.push(money(unit * qty));

    addBtn.disabled = true;
    setStatus(t.adding);
    window.zenyoCart.add(data.ajaxUrl, id, qty)
      .then(function () { setStatus(t.added.replace('%s', summary.join(', '))); })
      .catch(function () { setStatus(t.error); })
      .then(function () { renderPrice(current()); });
  });

  render();
})();
