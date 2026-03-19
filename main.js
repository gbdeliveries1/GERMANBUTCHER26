(function () {
  'use strict';

  function safeStyle(id, fn) {
    var el = document.getElementById(id);
    if (!el) { return; }
    fn(el.style);
  }

  // Example usage: replace any direct document.getElementById('foo').style... with safeStyle:
  // safeStyle('foo', s => { s.display = 'none'; });

  document.addEventListener('DOMContentLoaded', function () {
    // Add any DOM-dependent code here, always using safeStyle for element styling.
  });

  window.safeStyle = safeStyle; // optional exposure
})();