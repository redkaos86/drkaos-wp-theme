/**
 * DR.KAOS WP THEME - cursore personalizzato.
 * Configurazione in window.drkaosCursor (impostata da inc/cursor.php).
 */
(function () {
	'use strict';

	var cfg = window.drkaosCursor;
	if (!cfg || !window.matchMedia) { return; }
	// Solo dispositivi con mouse/trackpad.
	if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) { return; }

	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var root = document.documentElement;
	var parts = [];
	var tx = -100, ty = -100, started = false, running = false, growSel = '';

	function makePart(kind, w, h, lerp) {
		var el = document.createElement('div');
		el.className = 'dk-cursor dk-cursor--' + kind + (cfg.invert ? ' dk-cursor--invert' : '') + (cfg.anchor === 'topleft' && (kind === 'icon' || kind === 'image') ? ' is-topleft' : '');
		el.setAttribute('aria-hidden', 'true');
		el.style.setProperty('--dk-color', cfg.color);
		el.style.setProperty('--dk-w', w + 'px');
		el.style.setProperty('--dk-h', h + 'px');
		var inner = document.createElement('span');
		inner.className = 'dk-cursor__inner';
		if (kind === 'icon' || kind === 'image') { inner.innerHTML = cfg.markup; }
		el.appendChild(inner);
		document.body.appendChild(el);
		var p = { el: el, l: reduce ? 1 : lerp, x: -100, y: -100, kind: kind };
		parts.push(p);
		return p;
	}

	function build() {
		var s = cfg.size, l = cfg.smooth;
		switch (cfg.shape) {
			case 'ring':
				makePart('ring', cfg.ringSize, cfg.ringSize, l);
				break;
			case 'dot_ring':
				makePart('ring', cfg.ringSize, cfg.ringSize, Math.max(0.04, l * 0.55));
				makePart('dot', s, s, Math.min(1, l * 2.2));
				break;
			case 'icon':
			case 'image':
				makePart(cfg.shape, s, s, l);
				break;
			default:
				makePart('dot', s, s, l);
		}
	}

	function tick() {
		var moving = false;
		for (var i = 0; i < parts.length; i++) {
			var p = parts[i];
			var dx = tx - p.x, dy = ty - p.y;
			if (Math.abs(dx) > 0.1 || Math.abs(dy) > 0.1) {
				p.x += dx * p.l;
				p.y += dy * p.l;
				moving = true;
			} else {
				p.x = tx; p.y = ty;
			}
			p.el.style.transform = 'translate3d(' + p.x.toFixed(2) + 'px,' + p.y.toFixed(2) + 'px,0)';
		}
		if (moving) { requestAnimationFrame(tick); } else { running = false; }
	}

	function kick() {
		if (!running) { running = true; requestAnimationFrame(tick); }
	}

	function setHover(on) {
		for (var i = 0; i < parts.length; i++) {
			var p = parts[i], scale = 1;
			if (on) {
				// Con anello + pallina: la pallina si ingrandisce, l'anello solo un po'.
				scale = p.kind === 'ring' && parts.length > 1 ? 1 + (cfg.hoverScale - 1) * 0.35 : cfg.hoverScale;
			}
			p.el.firstChild.style.setProperty('--dk-s', scale);
		}
	}

	function setHidden(hidden) {
		for (var i = 0; i < parts.length; i++) { parts[i].el.classList.toggle('is-hidden', hidden); }
	}

	function onMove(e) {
		tx = e.clientX; ty = e.clientY;
		if (!started) {
			started = true;
			for (var i = 0; i < parts.length; i++) { parts[i].x = tx; parts[i].y = ty; parts[i].el.classList.add('is-ready'); }
		}
		kick();
	}

	function onOver(e) {
		var t = e.target;
		if (!t || !t.closest) { return; }
		var field = t.closest('input:not([type=button]):not([type=submit]):not([type=checkbox]):not([type=radio]):not([type=range]), textarea, select, [contenteditable="true"]');
		setHidden(!!field);
		var grow = false;
		if (growSel) {
			try { grow = !!t.closest(growSel); } catch (err) { growSel = ''; }
		}
		setHover(grow && cfg.hoverScale > 1);
	}

	function init() {
		if (!document.body) { return; }
		growSel = cfg.growSelector || '';
		build();
		root.classList.add('dk-cursor-on');
		if (cfg.hideNative) { root.classList.add('dk-hide-native'); }

		document.addEventListener('mousemove', onMove, { passive: true });
		document.addEventListener('mouseover', onOver, { passive: true });
		document.addEventListener('mousedown', function () { parts.forEach(function (p) { p.el.classList.add('is-down'); }); });
		document.addEventListener('mouseup', function () { parts.forEach(function (p) { p.el.classList.remove('is-down'); }); });
		document.documentElement.addEventListener('mouseleave', function () { setHidden(true); });
		document.documentElement.addEventListener('mouseenter', function () { setHidden(false); });
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
