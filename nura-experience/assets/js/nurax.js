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
			wig = new Image();
			wig.onload = draw;
			wig.onerror = function () { if (hint) { hint.textContent = 'Could not load the wig image — open a product and tap Try it on.'; } };
			wig.src = overlayUrl;
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


/* ===== NURA AI Stylist chat (v1.1.0) ===== */
(function(){var d=document;function r(f){if(d.readyState!=="loading"){f();}else{d.addEventListener("DOMContentLoaded",f);}}
r(function(){
	var root=d.querySelector("[data-nurax-stylist]");if(!root){return;}
	var panel=root.querySelector("[data-stylist-panel]");
	var log=root.querySelector("[data-stylist-log]");
	var form=root.querySelector("[data-stylist-form]");
	var input=form?form.querySelector('input[name="msg"]'):null;
	var wa=root.getAttribute("data-wa")||"";
	var history=[];var busy=false;
	function openPanel(){if(panel){panel.hidden=false;root.classList.add("is-open");if(input){input.focus();}}}
	function closePanel(){if(panel){panel.hidden=true;root.classList.remove("is-open");}}
	var toggle=root.querySelector("[data-stylist-toggle]");
	if(toggle){toggle.addEventListener("click",function(){if(panel.hidden){openPanel();}else{closePanel();}});}
	var x=root.querySelector("[data-stylist-close]");if(x){x.addEventListener("click",closePanel);}
	function esc(s){var e=d.createElement("div");e.textContent=(s==null)?"":String(s);return e.innerHTML;}
	function addMsg(role,text){var el=d.createElement("div");el.className="nurax-msg nurax-msg--"+(role==="user"?"user":"bot");el.innerHTML=esc(text);log.appendChild(el);log.scrollTop=log.scrollHeight;return el;}
	function addProducts(items){if(!items||!items.length){return;}var wrap=d.createElement("div");wrap.className="nurax-msg nurax-msg--bot nurax-msg--cards";items.forEach(function(pr){var a=d.createElement("a");a.className="nurax-chip";a.href=pr.url||"#";a.target="_blank";a.rel="noopener";a.innerHTML='<img src="'+esc(pr.img)+'" alt="" loading="lazy"><span>'+esc(pr.name)+'<b>'+esc(pr.price)+'</b></span>';wrap.appendChild(a);});log.appendChild(wrap);log.scrollTop=log.scrollHeight;}
	function typing(on){var t=log.querySelector(".nurax-typing");if(on){if(t){return;}var el=d.createElement("div");el.className="nurax-msg nurax-msg--bot nurax-typing";el.innerHTML="<span></span><span></span><span></span>";log.appendChild(el);log.scrollTop=log.scrollHeight;}else if(t){t.parentNode.removeChild(t);}}
	function send(text){
		if(busy||!text){return;}
		busy=true;
		addMsg("user",text);history.push({role:"user",content:text});
		if(input){input.value="";}
		typing(true);
		var rest=(window.NURAX&&NURAX.rest)?NURAX.rest:"/wp-json/nurax/v1/";
		fetch(rest+"stylist",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({messages:history.slice(-10)})})
			.then(function(res){return res.json();})
			.then(function(data){typing(false);var reply=(data&&data.reply)?data.reply:"Sorry, I could not respond just now. Please try WhatsApp.";addMsg("bot",reply);history.push({role:"assistant",content:reply});if(data&&data.products){addProducts(data.products);}busy=false;})
			.catch(function(){typing(false);var el=addMsg("bot","I am having trouble connecting right now. ");if(wa){var a=d.createElement("a");a.href=wa;a.target="_blank";a.rel="noopener";a.textContent="Chat on WhatsApp";a.className="nurax-msg-link";el.appendChild(a);}busy=false;});
	}
	if(form){form.addEventListener("submit",function(e){e.preventDefault();send(input?input.value.trim():"");});}
	[].slice.call(root.querySelectorAll("[data-q]")).forEach(function(qb){qb.addEventListener("click",function(){send(qb.getAttribute("data-q"));});});
});})();
