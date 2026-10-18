/**
 * DR.KAOS WP THEME - interruttore Manutenzione nella barra admin.
 * Senza JavaScript il link con nonce funziona comunque (ricarica la pagina).
 */
(function () {
	'use strict';

	var cfg = window.drkaosMaintBar;
	if (!cfg) { return; }

	function setState(node, on) {
		node.classList.toggle('is-on', on);
		node.classList.toggle('is-off', !on);
		var state = node.querySelector('.dkm-ab__state');
		var sw = node.querySelector('.dkm-ab__switch');
		if (state) { state.textContent = on ? 'ON' : 'OFF'; }
		if (sw) { sw.setAttribute('aria-checked', on ? 'true' : 'false'); }
	}

	document.addEventListener('click', function (e) {
		var link = e.target.closest && e.target.closest('#wp-admin-bar-drkaos-maint > .ab-item');
		if (!link || !window.fetch) { return; }

		e.preventDefault();
		var node = document.getElementById('wp-admin-bar-drkaos-maint');
		if (node.classList.contains('is-busy')) { return; }
		node.classList.add('is-busy');

		fetch(cfg.ajax, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: new URLSearchParams({ action: 'drkaos_maint_toggle', nonce: cfg.nonce })
		})
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (res && res.success) {
					setState(node, !!res.data.on);
					// Nella pagina di personalizzazione tiene allineato anche l'interruttore del modulo.
					var box = document.querySelector('.dkma input[name$="[enabled]"]');
					if (box) { box.checked = !!res.data.on; }
				} else {
					window.location.href = link.href; // ripiega sul link con ricarica
				}
			})
			.catch(function () { window.location.href = link.href; })
			.then(function () { node.classList.remove('is-busy'); });
	});
})();
