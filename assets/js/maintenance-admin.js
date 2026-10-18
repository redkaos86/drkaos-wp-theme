/**
 * DR.KAOS WP THEME - pagina di personalizzazione della manutenzione:
 * anteprima dal vivo, selettore immagini, cursori sincronizzati.
 */
(function () {
	'use strict';

	var cfg = window.drkaosMaintAdmin;
	var form = document.querySelector('.dkma form');
	var root = document.querySelector('#dkma-preview .dkm');
	if (!cfg || !form || !root) { return; }

	var loadedFonts = {};
	var q = function (sel) { return root.querySelector(sel); };

	function field(name) { return form.querySelector('[data-dkm="' + name + '"]'); }
	function val(name) {
		var el = field(name);
		if (!el) { return ''; }
		return el.type === 'checkbox' ? el.checked : el.value;
	}

	function loadFont(key) {
		var f = cfg.fonts[key];
		if (!f || !f.url || loadedFonts[f.url]) { return; }
		var l = document.createElement('link');
		l.rel = 'stylesheet';
		l.href = f.url;
		document.head.appendChild(l);
		loadedFonts[f.url] = true;
	}

	function stack(key) { return (cfg.fonts[key] || cfg.fonts.system).stack; }
	function tokens(t) { return String(t).replace(/\[anno\]/g, cfg.year).replace(/\[sito\]/g, cfg.site); }
	function paragraphs(t) {
		t = tokens(t).trim();
		if (!t) { return ''; }
		return t.split(/\n{2,}/).map(function (p) { return '<p>' + p.replace(/\n/g, '<br>') + '</p>'; }).join('');
	}

	function apply() {
		['title_font', 'text_font', 'bar_font'].forEach(function (n) { loadFont(val(n)); });

		var set = function (k, v) { root.style.setProperty(k, v); };
		set('--dkm-bg', 'url("' + (val('bg_image') || cfg.defaultBg) + '")');
		set('--dkm-overlay', (val('overlay') || 0) / 100);
		set('--dkm-logo-n', val('logo_width'));
		set('--dkm-title-font', stack(val('title_font')));
		set('--dkm-title-n', val('title_size'));
		set('--dkm-title-weight', val('title_weight'));
		set('--dkm-title-color', val('title_color'));
		set('--dkm-text-font', stack(val('text_font')));
		set('--dkm-text-n', val('text_size'));
		set('--dkm-text-weight', val('text_weight'));
		set('--dkm-text-color', val('text_color'));
		set('--dkm-bar-font', stack(val('bar_font')));
		set('--dkm-bar-n', val('bar_size'));
		set('--dkm-bar-color', val('bar_color'));
		set('--dkm-bar-op', (val('bar_opacity') || 0) / 100);

		q('.dkm__logo').src = val('logo') || cfg.defaultLogo;
		q('.dkm__title').textContent = val('title');
		q('.dkm__text').innerHTML = paragraphs(val('text'));

		var bar = q('.dkm__bar');
		if (bar) {
			bar.hidden = !val('bar_enabled');
			bar.innerHTML = tokens(val('bar_text'));
		}

		// Miniature dei selettori immagine
		['bg_image', 'logo'].forEach(function (n) {
			var thumb = form.querySelector('.dkma-image[data-target="' + n + '"] .dkma-image__thumb');
			if (thumb) { thumb.style.backgroundImage = 'url("' + (val(n) || (n === 'logo' ? cfg.defaultLogo : cfg.defaultBg)) + '")'; }
		});
	}

	// Cursori <-> campi numerici
	form.querySelectorAll('input[data-dkm-range]').forEach(function (range) {
		var num = field(range.getAttribute('data-dkm-range'));
		range.addEventListener('input', function () { num.value = range.value; apply(); });
		num.addEventListener('input', function () { range.value = num.value; });
	});

	form.addEventListener('input', apply);
	form.addEventListener('change', apply);

	// Selettore immagini dalla libreria media di WordPress
	form.querySelectorAll('[data-dkm-pick]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var name = btn.getAttribute('data-dkm-pick');
			var frame = wp.media({ title: cfg.pick, button: { text: cfg.use }, library: { type: 'image' }, multiple: false });
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				field(name).value = att.url;
				apply();
			});
			frame.open();
		});
	});
	form.querySelectorAll('[data-dkm-clear]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			field(btn.getAttribute('data-dkm-clear')).value = '';
			apply();
		});
	});

	// Anteprima in scala
	var box = document.getElementById('dkma-preview');
	var stage = document.getElementById('dkma-stage');
	function fit() { stage.style.transform = 'scale(' + (box.clientWidth / 1280) + ')'; }
	fit();
	if (window.ResizeObserver) { new ResizeObserver(fit).observe(box); } else { window.addEventListener('resize', fit); }

	apply();
})();
