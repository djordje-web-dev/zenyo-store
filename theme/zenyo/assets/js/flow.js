/**
 * Korpa i naplata: sitnice preko WooCommerce-ovih skripti.
 *
 * Tezak posao (osvezavanje korpe i naplate bez ponovnog ucitavanja, slanje porudzbine) radi
 * WooCommerce. Ova skripta dodaje samo:
 *   1. dugmad + i - za kolicinu u korpi,
 *   2. dugme "Add to cart" kod preporuka u korpi,
 *   3. ispis greske ispod polja na naplati, umesto jednog spiska na vrhu.
 * WooCommerce-ove skripte su pisane u jQuery-ju, pa i ova koristi njega da bi mogla da im se javi.
 */
jQuery(function ($) {
  'use strict';

  var $body = $(document.body);

  /* ---------- 1. Kolicina u korpi ---------- */

  var updateTimer = null;
  function updateCart() {
    clearTimeout(updateTimer);
    // Mala pauza: ako kupac klikne + tri puta zaredom, korpa se osvezi jednom.
    updateTimer = setTimeout(function () {
      $('.woocommerce-cart-form [name="update_cart"]').prop('disabled', false).trigger('click');
    }, 450);
  }

  $body.on('click', '.zy-cartx [data-zy-step]', function () {
    var $input = $(this).siblings('input.qty');
    var max = parseInt($input.attr('max'), 10);
    var next = Math.max(1, (parseInt($input.val(), 10) || 1) + parseInt($(this).attr('data-zy-step'), 10));
    if (max > 0) next = Math.min(next, max);
    $input.val(next);
    $(this).siblings('[data-zy-step="-1"]').addBack('[data-zy-step="-1"]').prop('disabled', next <= 1);
    updateCart();
  });

  $body.on('change', '.zy-cartx input.qty', function () {
    if ((parseInt($(this).val(), 10) || 0) < 1) $(this).val(1);
    updateCart();
  });

  /* ---------- 2. Preporuke u korpi ---------- */

  $body.on('click', '[data-zy-rec-add]', function () {
    var $btn = $(this);
    $btn.prop('disabled', true);
    window.zenyoCart.add($btn.attr('data-zy-url'), $btn.attr('data-zy-rec-add'), 1)
      // Nova stavka menja i spisak i preporuke, pa je najjednostavnije ucitati korpu ponovo.
      .then(function () { window.location.reload(); })
      .catch(function () { $btn.prop('disabled', false); });
  });

  /* ---------- 3. Greske ispod polja na naplati ---------- */

  var $form = $('form.checkout');
  if (!$form.length) return;

  function clearFieldErrors() {
    $form.find('.zy-field-error').remove();
    $form.find('.zy-has-error').removeClass('zy-has-error');
  }

  // WooCommerce javlja "checkout_error" kad server odbije porudzbinu i na vrh obrasca stavi spisak gresaka.
  // Svaka greska nosi oznaku polja (data-id), pa je selimo ispod tog polja.
  $body.on('checkout_error', function () {
    clearFieldErrors();
    var $group = $form.find('.woocommerce-NoticeGroup-checkout');
    var $first = null;

    $group.find('li[data-id]').each(function () {
      var $item = $(this);
      var $row = $('#' + $item.attr('data-id') + '_field');
      if (!$row.length) return; // greska bez svog polja ostaje u spisku na vrhu
      if (!$row.find('.zy-field-error').length) {
        $('<span class="zy-field-error" role="alert"></span>').text($.trim($item.text())).appendTo($row);
        $row.addClass('zy-has-error');
        if (!$first) $first = $row;
      }
      $item.remove();
    });

    if (!$group.find('li').length) $group.remove();
    if ($first) {
      $('html, body').stop(true);
      $first.find('input, textarea, select').first().trigger('focus');
    }
  });

  // Cim kupac krene da ispravlja polje, njegova greska nestaje.
  $form.on('input change', '.zy-has-error :input', function () {
    var $row = $(this).closest('.form-row');
    $row.removeClass('zy-has-error').find('.zy-field-error').remove();
  });
});
