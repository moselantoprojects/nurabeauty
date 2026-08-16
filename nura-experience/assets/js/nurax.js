/* NURA Experience - front-end for AI Wig Finder + Virtual Try-On. */
(function () {
	'use strict';
	var d = document;
	function ready(fn){ if(d.readyState!=='loading'){fn();}else{d.addEventListener('DOMContentLoaded',fn);} }

	ready(function () {
		initFinder();
		initTryOn();
	});

	/* ---------- AI Wig Finder ---------- */
	function initFinder() {
		var root = d.querySelector('[data-nurax-finder]');
		if (!root) { return; }
		var form = root.querySelector('.nurax-quiz');
		var results = root.querySelector('[data-nurax-results]');

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var fd = new FormData(form);
			var payload = {
				face: fd.get('face'), tone: fd.get('tone'),
				life: fd.get('life'), budget: fd.get('budget')
			};
			results.hidden = false;
			results.innerHTML = '<p>Finding your perfect match…</p>';

			fetch((window.NURAX && NURAX.rest ? NURAX.rest : '/wp-json/nurax/v1/') + 'wig-finder', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(payload)
			}).then(function (r) { return r.json(); }).then(function (data) {
				if (!data || !data.products || !data.products.length) {
					results.innerHTML = '<p>No exact match right now — <a href="/book-appointment/">book a free consultation</a> and we will find your crown.</p>';
					return;
				}
				var html = '<p class="nurax-results__note">' + (data.note || 'Your matches') + '</p><div class="nurax-reco">';
				data.products.forEach(function (p) {
					html += '<a href="' + p.url + '"><img src="' + p.img + '" alt="' + p.name + '" loading="lazy"><span>' + p.name + '<br><b>' + p.price + '</b></span></a>';
				});
				html += '</div>';
				results.innerHTML = html;
				results.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
			}).catch(function () {
				results.innerHTML = '<p>Something went wrong. Please try again or <a href="/book-appointment/">book a consultation</a>.</p>';
			});
		});
	}

	/* ---------- Virtual Try-On (client-side canvas overlay MVP) ---------- */
	function initTryOn() {
		var root = d.querySelector('[data-nurax-tryon]');
		if (!root) { return; }
		var canvas = root.querySelector('[data-nurax-canvas]');
		var ctx = canvas.getContext('2d');
		var hint = root.querySelector('.nurax-tryon__hint');
		var photoInput = root.querySelector('[data-nurax-photo]');
		var scaleInput = root.querySelector('[data-nurax-scale]');
		var opacityInput = root.querySelector('[data-nurax-opacity]');
		var resetBtn = root.querySelector('[data-nurax-reset]');

		// Overlay image from query param product or data-overlay.
		var overlayUrl = root.getAttribute('data-overlay');
		var params = new URLSearchParams(window.location.search);
		if (params.get('overlay')) { overlayUrl = params.get('overlay'); }

		var photo = null, wig = null;
		var state = { x: canvas.width / 2, y: canvas.height * 0.32, scale: 1, opacity: 1, dragging: false, ox: 0, oy: 0 };

		if (overlayUrl) {
			wig = new Image(); wig.crossOrigin = 'anonymous';
			wig.onload = draw; wig.src = overlayUrl;
		}

		function draw() {
			ctx.clearRect(0, 0, canvas.width, canvas.height);
			if (photo) {
				var r = Math.max(canvas.width / photo.width, canvas.height / photo.height);
				var w = photo.width * r, h = photo.height * r;
				ctx.drawImage(photo, (canvas.width - w) / 2, (canvas.height - h) / 2, w, h);
				if (hint) { hint.style.display = 'none'; }
			}
			if (wig && photo) {
				ctx.globalAlpha = state.opacity;
				var ww = wig.width * state.scale * 0.5, wh = wig.height * state.scale * 0.5;
				ctx.drawImage(wig, state.x - ww / 2, state.y - wh / 2, ww, wh);
				ctx.globalAlpha = 1;
			}
		}

		if (photoInput) {
			photoInput.addEventListener('change', function (e) {
				var file = e.target.files[0]; if (!file) { return; }
				var reader = new FileReader();
				reader.onload = function (ev) { photo = new Image(); photo.onload = draw; photo.src = ev.target.result; };
				reader.readAsDataURL(file);
			});
		}
		if (scaleInput) { scaleInput.addEventListener('input', function () { state.scale = this.value / 100; draw(); }); }
		if (opacityInput) { opacityInput.addEventListener('input', function () { state.opacity = this.value / 100; draw(); }); }
		if (resetBtn) { resetBtn.addEventListener('click', function () { state.x = canvas.width / 2; state.y = canvas.height * 0.32; state.scale = 1; if (scaleInput) { scaleInput.value = 100; } draw(); }); }

		// Drag to position the wig.
		function pos(e) { var r = canvas.getBoundingClientRect(); var t = e.touches ? e.touches[0] : e; return { x: (t.clientX - r.left) * (canvas.width / r.width), y: (t.clientY - r.top) * (canvas.height / r.height) }; }
		function down(e) { state.dragging = true; var p = pos(e); state.ox = p.x - state.x; state.oy = p.y - state.y; }
		function move(e) { if (!state.dragging) { return; } var p = pos(e); state.x = p.x - state.ox; state.y = p.y - state.oy; draw(); e.preventDefault(); }
		function up() { state.dragging = false; }
		canvas.addEventListener('mousedown', down); canvas.addEventListener('mousemove', move); window.addEventListener('mouseup', up);
		canvas.addEventListener('touchstart', down, { passive: true }); canvas.addEventListener('touchmove', move, { passive: false }); window.addEventListener('touchend', up);

		// Hook point for a real face-tracking provider (see plugin settings/readme).
		if (window.nuraxTryonProvider && typeof window.nuraxTryonProvider === 'function') {
			window.nuraxTryonProvider({ canvas: canvas, setState: function (s) { Object.assign(state, s); draw(); } });
		}
	}
})();
