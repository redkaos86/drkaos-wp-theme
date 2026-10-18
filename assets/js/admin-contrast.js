/**
 * DR.KAOS WP THEME - controllo automatico della leggibilità nella dashboard.
 *
 * Per ogni elemento con testo confronta il colore del testo con lo sfondo reale
 * (risalendo gli antenati). Se il contrasto è troppo basso usa un testo scuro su
 * sfondo chiaro o un testo chiaro su sfondo scuro. Vale per qualsiasi pagina,
 * anche dei plugin di terze parti. Dopo il primo passaggio ricontrolla solo le
 * parti della pagina che cambiano.
 */
(function () {
	'use strict';

	var MIN_RATIO = 4;
	var DARK_TEXT = [29, 43, 51];
	var LIGHT_TEXT = [230, 242, 239];
	var DARK_LINK = [0, 107, 87];
	var LIGHT_LINK = [0, 224, 181];
	var PAGE_BG = [10, 26, 32];
	var SKIP_TAGS = { SCRIPT: 1, STYLE: 1, NOSCRIPT: 1, IFRAME: 1, SVG: 1, svg: 1, CANVAS: 1, IMG: 1, VIDEO: 1, TEMPLATE: 1, OPTION: 1, HEAD: 1 };
	var SKIP_SELECTOR = '#adminmenuwrap, #adminmenuback, #wpadminbar, .screen-reader-text, .mce-container, .wp-editor-container iframe, .CodeMirror, .button-primary, .dkm';

	function parse(c) {
		var m = c && c.match(/^rgba?\(([^)]+)\)/);
		if (!m) { return null; }
		var p = m[1].split(/[\s,\/]+/).filter(Boolean).map(parseFloat);
		if (p.length < 3) { return null; }
		return { r: p[0], g: p[1], b: p[2], a: p.length > 3 ? p[3] : 1 };
	}

	function lum(c) {
		var f = function (v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); };
		return 0.2126 * f(c[0]) + 0.7152 * f(c[1]) + 0.0722 * f(c[2]);
	}

	function ratio(a, b) {
		var l1 = lum(a), l2 = lum(b);
		return (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
	}

	function compose(own, bg) {
		return [
			own.r * own.a + bg[0] * (1 - own.a),
			own.g * own.a + bg[1] * (1 - own.a),
			own.b * own.a + bg[2] * (1 - own.a)
		];
	}

	function hasOwnText(el) {
		var n = el.childNodes;
		for (var i = 0; i < n.length; i++) {
			if (n[i].nodeType === 3 && n[i].nodeValue.trim() !== '') { return true; }
		}
		return false;
	}

	// Sfondo effettivo dell'elemento dato, risalendo fino al body.
	function effectiveBg(el) {
		var chain = [];
		for (var n = el; n && n.nodeType === 1; n = n.parentElement) { chain.push(n); }
		var bg = PAGE_BG;
		for (var i = chain.length - 1; i >= 0; i--) {
			var own = parse(window.getComputedStyle(chain[i]).backgroundColor);
			if (own && own.a > 0) { bg = compose(own, bg); }
		}
		return bg;
	}

	function walk(el, bg) {
		if (el.nodeType !== 1 || SKIP_TAGS[el.tagName] || (el.matches && el.matches(SKIP_SELECTOR))) { return; }

		var cs = window.getComputedStyle(el);
		if (cs.display === 'none') { return; }

		var own = parse(cs.backgroundColor);
		var eff = (own && own.a > 0) ? compose(own, bg) : bg;

		// Sfondi con immagine/gradiente non valutabili: si mantiene lo sfondo ereditato.
		var skipCheck = cs.backgroundImage !== 'none' && own && own.a === 0;

		// I campi (input, select, textarea) sono gestiti dal CSS: qui solo testo normale.
		if (hasOwnText(el) && !skipCheck && cs.visibility !== 'hidden') {
			var col = parse(cs.color);
			if (col && col.a > 0.3) {
				var fg = [col.r, col.g, col.b];
				if (ratio(fg, eff) < MIN_RATIO) {
					var isLink = el.tagName === 'A' || (el.closest && el.closest('a'));
					var dark = isLink ? DARK_LINK : DARK_TEXT;
					var light = isLink ? LIGHT_LINK : LIGHT_TEXT;
					var best = ratio(dark, eff) >= ratio(light, eff) ? dark : light;
					el.style.setProperty('color', 'rgb(' + best.join(',') + ')', 'important');
				}
			}
		}

		for (var c = el.firstElementChild; c; c = c.nextElementSibling) {
			walk(c, eff);
		}
	}

	function fullPass() {
		walk(document.body, PAGE_BG);
	}

	// Controlla solo i rami cambiati.
	var pending = new Set();
	var timer = null;

	function flush() {
		timer = null;
		if (document.hidden) { return; }
		var nodes = Array.from(pending);
		pending.clear();
		if (nodes.length > 150) { fullPass(); return; }
		nodes.forEach(function (node) {
			if (!node.isConnected) { return; }
			var parent = node.parentElement;
			walk(node, parent ? effectiveBg(parent) : PAGE_BG);
		});
	}

	function queue(node) {
		if (node && node.nodeType === 1) { pending.add(node); }
		if (!timer) { timer = setTimeout(flush, 250); }
	}

	function init() {
		if (document.body.classList.contains('block-editor-page')) { return; }
		fullPass();
		window.addEventListener('load', function () { queue(document.body); });
		if (window.MutationObserver) {
			new MutationObserver(function (records) {
				records.forEach(function (r) {
					if (r.type === 'childList') {
						r.addedNodes.forEach(queue);
					} else {
						queue(r.target);
					}
				});
			}).observe(document.body, {
				childList: true,
				subtree: true,
				attributes: true,
				attributeFilter: ['class', 'hidden', 'aria-expanded']
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
