/**
 * Otvaranje i zatvaranje malih panela (za sada: broj telefona u zaglavlju).
 * Dugme sa data-zy-toggle="ID" pokazuje ili sakriva element sa tim ID-jem.
 * Panel se zatvara i klikom van njega ili tasterom Esc.
 */
(function () {
  'use strict';

  var open = null; // { button, panel }

  function close() {
    if (!open) return;
    open.panel.hidden = true;
    open.button.setAttribute('aria-expanded', 'false');
    open = null;
  }

  document.addEventListener('click', function (event) {
    var button = event.target.closest('[data-zy-toggle]');
    if (button) {
      var panel = document.getElementById(button.getAttribute('data-zy-toggle'));
      if (!panel) return;
      var wasOpen = open && open.panel === panel;
      close();
      if (!wasOpen) {
        panel.hidden = false;
        button.setAttribute('aria-expanded', 'true');
        open = { button: button, panel: panel };
      }
      return;
    }
    if (open && !open.panel.contains(event.target)) close();
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && open) {
      var button = open.button;
      close();
      button.focus();
    }
  });
})();
