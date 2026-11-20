/**
 * Kupovina proizvoda: galerija, izbor (boja, velicina...), cena, dodavanje u korpu, paneli.
 *
 * Radi za svaki element sa atributom data-zy-product, pa ista skripta pokriva stranicu proizvoda
 * (jedan takav element) i stranice gde se kupuje vise proizvoda odjednom, npr. Belts (vise njih).
 *
 * Podaci za svaki proizvod stizu sa servera u <script data-zy-product-data> unutar tog elementa:
 *   variations: [{ id, attributes: { pa_colour: 'blue', pa_size: '180' }, price, priceText, inStock, image }]
 * Varijacija = jedna konkretna kombinacija koja se moze kupiti.
 */
(function () {
  'use strict';

  var slice = function (list) { return Array.prototype.slice.call(list); };

  /* ---------- Paneli (uvecana slika, tabela velicina): zajednicki za celu stranicu ---------- */

  document.addEventListener('click', function (event) {
    var opener = event.target.closest('[data-zy-open]');
    if (opener) {
      var dialog = document.getElementById(opener.getAttribute('data-zy-open'));
      if (!dialog || typeof dialog.showModal !== 'function') return;
      if (dialog.id === 'zy-zoom') {
        var scope = opener.closest('[data-zy-product]');
        var source = scope && scope.querySelector('[data-zy-main]');
        var zoomImg = dialog.querySelector('[data-zy-zoom-img]');
        if (source && zoomImg) { zoomImg.src = source.src; zoomImg.alt = source.alt; }
      }
      dialog.showModal();
      return;
    }
    var closer = event.target.closest('[data-zy-close]');
    if (closer && closer.closest('dialog')) {
      closer.closest('dialog').close();
      return;
    }
    // Klik na zatamnjenu pozadinu oko panela ga zatvara.
    if (event.target.tagName === 'DIALOG') event.target.close();
  });

  /* ---------- Jedan proizvod ---------- */

  function initProduct(root) {
    var dataEl = root.querySelector('[data-zy-product-data]');
    if (!dataEl) return;

    var data = JSON.parse(dataEl.textContent);
    var t = data.i18n;
    var selected = {}; // npr. { pa_colour: 'blue', pa_size: '180' }

    var priceEl = root.querySelector('[data-zy-price]');
    var statusEl = root.querySelector('[data-zy-status]');
    var addBtn = root.querySelector('[data-zy-add]');
    var mainImg = root.querySelector('[data-zy-main]');
    var groups = slice(root.querySelectorAll('[data-zy-choice]'));
    var thumbs = slice(root.querySelectorAll('[data-zy-thumb]'));

    function showImage(src, alt, thumb) {
      if (!mainImg || !src) return;
      mainImg.src = src;
      if (alt) mainImg.alt = alt;
      thumbs.forEach(function (b) { b.setAttribute('aria-pressed', b === thumb ? 'true' : 'false'); });
    }

    thumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        showImage(thumb.getAttribute('data-full'), thumb.getAttribute('data-alt'), thumb);
      });
    });

    function setStatus(text, isError) {
      if (!statusEl) return;
      statusEl.textContent = text || '';
      statusEl.classList.toggle('is-error', !!isError);
    }

    /** Varijacije na stanju koje se slazu sa datim izborom (izbor moze biti i delimican). */
    function matching(choice) {
      return data.variations.filter(function (v) {
        if (!v.inStock) return false;
        return Object.keys(choice).every(function (key) {
          // Prazna vrednost na varijaciji znaci "bilo koja".
          return !v.attributes[key] || v.attributes[key] === choice[key];
        });
      });
    }

    function exactVariation() {
      var keys = Object.keys(data.labels);
      if (!keys.every(function (k) { return selected[k]; })) return null;
      return matching(selected)[0] || null;
    }

    function cheapest(list) {
      return list.reduce(function (min, v) { return !min || v.price < min.price ? v : min; }, null);
    }

    function render() {
      groups.forEach(function (group, groupIndex) {
        var key = group.getAttribute('data-zy-choice');
        // Dostupnost zavisi samo od izbora IZNAD ove grupe: boja se uvek moze promeniti,
        // a duzine se prilagodjavaju boji (ne obrnuto). Tako kupac nikad ne ostane "zakljucan".
        var others = {};
        groups.slice(0, groupIndex).forEach(function (earlier) {
          var k = earlier.getAttribute('data-zy-choice');
          if (selected[k]) others[k] = selected[k];
        });

        slice(group.querySelectorAll('[data-zy-option]')).forEach(function (btn) {
          var value = btn.getAttribute('data-zy-option');
          var trial = Object.assign({}, others);
          trial[key] = value;
          var available = matching(trial).length > 0;
          // Ako je izabrana vrednost postala nedostupna (npr. promenjena boja), skidamo je.
          if (!available && selected[key] === value) delete selected[key];
          btn.disabled = !available;
          btn.setAttribute('aria-pressed', selected[key] === value ? 'true' : 'false');
        });

        var valueEl = group.querySelector('[data-zy-value]');
        var chosen = group.querySelector('[data-zy-option][aria-pressed="true"]');
        if (valueEl) valueEl.textContent = chosen ? chosen.getAttribute('data-name') : '';
      });

      if (!priceEl || !data.isVariable) return;
      var exact = exactVariation();
      if (exact) {
        priceEl.textContent = exact.priceText;
        return;
      }
      var pool = matching(selected);
      var low = cheapest(pool);
      if (!low) {
        priceEl.textContent = t.soldOut;
        return;
      }
      var allSame = pool.every(function (v) { return v.price === low.price; });
      priceEl.textContent = allSame ? low.priceText : t.from.replace('%s', low.priceText);
    }

    groups.forEach(function (group) {
      var key = group.getAttribute('data-zy-choice');
      group.addEventListener('click', function (event) {
        var btn = event.target.closest('[data-zy-option]');
        if (!btn || btn.disabled) return;
        selected[key] = btn.getAttribute('data-zy-option');
        setStatus('');
        render();
        render(); // drugi prolaz: ako je prvi skinuo neku vrednost, da se ostale grupe usklade

        // Ako izabrana kombinacija (ili boja) ima svoju sliku, prikazi je.
        var withImage = matching(selected).filter(function (v) { return v.image; })[0];
        if (withImage) showImage(withImage.image, '', null);
      });
    });

    function missingLabels() {
      return Object.keys(data.labels)
        .filter(function (k) { return !selected[k]; })
        // "Length (cm)" -> "length": u poruci ne treba jedinica mere.
        .map(function (k) { return data.labels[k].replace(/\s*\(.*\)\s*$/, '').toLowerCase(); });
    }

    function chosenNames() {
      return groups.map(function (group) {
        var chosen = group.querySelector('[data-zy-option][aria-pressed="true"]');
        return chosen ? chosen.getAttribute('data-name') : '';
      }).filter(Boolean);
    }

    if (addBtn) {
      addBtn.addEventListener('click', function () {
        var id = data.productId;
        var summary = [];

        if (data.isVariable) {
          var missing = missingLabels();
          if (missing.length) {
            setStatus(t.select.replace('%s', missing.join(' ' + t.and + ' ')), true);
            return;
          }
          var variation = exactVariation();
          if (!variation) {
            setStatus(t.soldOut, true);
            return;
          }
          id = variation.id;
          summary = chosenNames().concat([variation.priceText]);
        } else {
          summary = [data.priceText];
        }

        addBtn.disabled = true;
        setStatus(t.adding);

        window.zenyoCart.add(data.ajaxUrl, id, 1)
          .then(function () { setStatus(t.added.replace('%s', summary.join(', '))); })
          .catch(function () { setStatus(t.error, true); })
          .then(function () { addBtn.disabled = false; });
      });
    }

    render();
  }

  slice(document.querySelectorAll('[data-zy-product]')).forEach(initProduct);
})();
