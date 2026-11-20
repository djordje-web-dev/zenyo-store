/**
 * Shop Index: prelazak misem (ili Tab) preko naziva kategorije menja veliku sliku.
 *
 * Svaki red i svaka slika imaju isti broj u data-zy-index. Kad red postane aktivan:
 *   - red dobija klasu is-active (tamna slova, crvena crtica),
 *   - njegova slika dobija is-active i is-in (is-in pokrece ulazak u tri trake),
 *   - prethodna slika dobija is-prev i ostaje ispod dok je nova ne prekrije,
 *   - velika slika pocinje da vodi na adresu tog reda.
 */
(function () {
  'use strict';

  var panel = document.querySelector('.zy-shopx__panel');
  var rows = Array.prototype.slice.call(document.querySelectorAll('.zy-shopx__row'));
  if (!panel || !rows.length) return;

  var layers = Array.prototype.slice.call(panel.querySelectorAll('.zy-shopx__layer'));
  var active = 0;

  function pick(index) {
    if (index === active || !layers[index]) return;
    var previous = active;
    active = index;

    rows.forEach(function (row, i) {
      row.classList.toggle('is-active', i === index);
      if (i === index) row.setAttribute('aria-current', 'true');
      else row.removeAttribute('aria-current');
    });

    layers.forEach(function (layer, i) {
      layer.classList.remove('is-in');
      layer.classList.toggle('is-prev', i === previous);
      layer.classList.toggle('is-active', i === index);
    });
    // Citanje sirine tera pregledac da primeni skidanje klase, pa animacija krece ispocetka
    // i kad se brzo predje preko vise kategorija.
    void layers[index].offsetWidth;
    layers[index].classList.add('is-in');

    panel.href = rows[index].href;
    panel.setAttribute('aria-label', layers[index].getAttribute('data-zy-label') || '');
  }

  rows.forEach(function (row, i) {
    row.addEventListener('mouseenter', function () { pick(i); });
    row.addEventListener('focus', function () { pick(i); });
  });

  // Telefon i tablet nemaju prelazak misem, pa se slike smenjuju same, na svake cetiri sekunde.
  // Ne radi kod onih koji su u sistemu iskljucili animacije, ni dok je kartica u pozadini.
  var noHover = window.matchMedia && window.matchMedia('(hover: none)').matches;
  var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (noHover && !calm && layers.length > 1) {
    setInterval(function () {
      if (!document.hidden) pick((active + 1) % layers.length);
    }, 4000);
  }
})();
