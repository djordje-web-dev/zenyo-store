/**
 * Zajednicka funkcija za dodavanje u korpu bez ponovnog ucitavanja stranice.
 * Koriste je product.js (stranica proizvoda, Belts) i gallery.js (Art, Jewellery, Accessories).
 *
 *   zenyoCart.add(url, id, kolicina).then(...)   // id = proizvod ili varijacija
 *
 * WooCommerce uz odgovor salje nove komade stranice (npr. brojac korpe u zaglavlju),
 * i ova funkcija ih odmah zamenjuje.
 */
window.zenyoCart = (function () {
  'use strict';

  function applyFragments(fragments, cartHash) {
    if (!fragments) return;
    Object.keys(fragments).forEach(function (selector) {
      Array.prototype.slice.call(document.querySelectorAll(selector)).forEach(function (el) {
        el.outerHTML = fragments[selector];
      });
    });
    // Javljamo i WooCommerce-ovoj skripti, da zapamti novo stanje za ostale stranice.
    if (window.jQuery) window.jQuery(document.body).trigger('added_to_cart', [fragments, cartHash]);
  }

  function add(url, id, quantity) {
    var body = new URLSearchParams();
    body.set('product_id', String(id));
    body.set('quantity', String(quantity || 1));
    return fetch(url, { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!json || json.error) throw new Error('add failed');
        applyFragments(json.fragments, json.cart_hash);
        return json;
      });
  }

  return { add: add };
})();
